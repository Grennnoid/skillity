<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StudentChatbotController extends Controller
{
    private const SETTINGS_CACHE_KEY = 'settings:chatbot';
    private const SETTINGS_CACHE_TTL = 300;
    private const HISTORY_LIMIT = 40;
    private const MODEL_HISTORY_LIMIT = 14;
    private const CONTEXT_CACHE_TTL = 90;

    public function history(): JsonResponse
    {
        return response()->json([
            'messages' => $this->formattedMessages(),
            'config' => $this->chatbotFrontendConfig(),
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
            'page_title' => ['nullable', 'string', 'max:180'],
            'page_path' => ['nullable', 'string', 'max:255'],
            'page_snapshot' => ['nullable', 'string', 'max:15000'],
            'local_progress' => ['nullable', 'string', 'max:5000'],
            'local_notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $apiKey = (string) config('ai.deepseek_api_key', '');
        if ($apiKey === '') {
            return response()->json([
                'message' => 'AI chatbot belum aktif. Admin perlu mengisi DEEPSEEK_API_KEY di file .env.',
            ], 503);
        }

        $settings = $this->chatbotSettings();
        $message = trim($validated['message']);
        $pageTitle = trim((string) ($validated['page_title'] ?? ''));
        $pagePath = trim((string) ($validated['page_path'] ?? ''));
        $websiteContext = $this->websiteContext($pagePath, $pageTitle, [
            'page_snapshot' => trim((string) ($validated['page_snapshot'] ?? '')),
            'local_progress' => trim((string) ($validated['local_progress'] ?? '')),
            'local_notes' => trim((string) ($validated['local_notes'] ?? '')),
        ]);

        if ($this->isIdentityQuestion($message)) {
            $assistantText = $this->identityReply($settings);

            DB::transaction(function () use ($message, $assistantText, $pageTitle, $pagePath) {
                DB::table('ai_chat_messages')->insert([
                    [
                        'user_id' => auth()->id(),
                        'role' => 'user',
                        'content' => $message,
                        'model' => null,
                        'token_count' => null,
                        'page_title' => $pageTitle !== '' ? $pageTitle : null,
                        'page_path' => $pagePath !== '' ? $pagePath : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'user_id' => auth()->id(),
                        'role' => 'assistant',
                        'content' => $assistantText,
                        'model' => 'system-profile',
                        'token_count' => null,
                        'page_title' => $pageTitle !== '' ? $pageTitle : null,
                        'page_path' => $pagePath !== '' ? $pagePath : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            });

            return response()->json([
                'message' => [
                    'role' => 'assistant',
                    'content' => $assistantText,
                    'created_at' => now()->toIso8601String(),
                ],
            ]);
        }

        $history = DB::table('ai_chat_messages')
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->limit(self::MODEL_HISTORY_LIMIT)
            ->get(['role', 'content'])
            ->reverse()
            ->values();

        $input = $history
            ->map(fn ($item) => [
                'role' => $item->role,
                'content' => $item->content,
            ])
            ->all();

        $userPrompt = $message;
        if ($pageTitle !== '' || $pagePath !== '') {
            $contextLines = array_filter([
                $pageTitle !== '' ? 'Current page title: '.$pageTitle : null,
                $pagePath !== '' ? 'Current page path: '.$pagePath : null,
            ]);
            $userPrompt = implode("\n", $contextLines)."\n\nUser message:\n".$message;
        }

        $input[] = [
            'role' => 'user',
            'content' => $userPrompt,
        ];

        $model = $this->chatbotModel();
        $apiResponse = Http::timeout(60)
            ->withToken($apiKey)
            ->acceptJson()
            ->post('https://api.deepseek.com/chat/completions', [
                'model' => $model,
                'messages' => array_merge([
                    [
                        'role' => 'system',
                        'content' => $this->chatbotInstructions($settings),
                    ],
                    [
                        'role' => 'system',
                        'content' => $websiteContext,
                    ],
                ], $input),
            ]);

        if ($apiResponse->failed()) {
            $errorMessage = (string) data_get($apiResponse->json(), 'error.message', '');
            return response()->json([
                'message' => $errorMessage !== ''
                    ? 'AI chatbot belum bisa membalas sekarang: '.$errorMessage
                    : 'AI chatbot belum bisa membalas sekarang. Coba lagi sebentar lagi.',
                'debug' => app()->isLocal() ? $apiResponse->json() : null,
            ], 502);
        }

        $responsePayload = $apiResponse->json();
        $assistantText = $this->extractAssistantText($responsePayload);
        if ($assistantText === '') {
            $assistantText = 'Aku belum punya balasan yang pas untuk itu. Coba tanya lagi dengan kalimat yang lebih spesifik.';
        }

        $model = (string) data_get($responsePayload, 'model', $model);
        $totalTokens = (int) data_get($responsePayload, 'usage.total_tokens', 0);

        DB::transaction(function () use ($message, $assistantText, $model, $totalTokens, $pageTitle, $pagePath) {
            DB::table('ai_chat_messages')->insert([
                [
                    'user_id' => auth()->id(),
                    'role' => 'user',
                    'content' => $message,
                    'model' => null,
                    'token_count' => null,
                    'page_title' => $pageTitle !== '' ? $pageTitle : null,
                    'page_path' => $pagePath !== '' ? $pagePath : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => auth()->id(),
                    'role' => 'assistant',
                    'content' => $assistantText,
                    'model' => $model,
                    'token_count' => $totalTokens > 0 ? $totalTokens : null,
                    'page_title' => $pageTitle !== '' ? $pageTitle : null,
                    'page_path' => $pagePath !== '' ? $pagePath : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::table('ai_usage_logs')->insert([
                'provider' => 'deepseek',
                'model' => $model,
                'token_count' => $totalTokens,
                'cost_usd' => 0,
                'logged_at' => now(),
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ai_feedback_logs')->insert([
                'user_id' => auth()->id(),
                'prompt_summary' => Str::limit($message, 180),
                'detected_topic' => $pageTitle !== '' ? Str::limit($pageTitle, 80, '') : 'Student Chatbot',
                'sentiment' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Cache::forget('dashboard:admin:data');
        Cache::forget('dashboard:dosen:'.auth()->id().':data');

        return response()->json([
            'message' => [
                'role' => 'assistant',
                'content' => $assistantText,
                'created_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function clear(): JsonResponse
    {
        DB::table('ai_chat_messages')
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json([
            'message' => 'Riwayat chat dibersihkan.',
        ]);
    }

    private function formattedMessages()
    {
        return DB::table('ai_chat_messages')
            ->where('user_id', auth()->id())
            ->orderBy('id')
            ->limit(self::HISTORY_LIMIT)
            ->get(['role', 'content', 'created_at'])
            ->map(fn ($message) => [
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at ? Carbon::parse($message->created_at)->toIso8601String() : null,
            ]);
    }

    private function chatbotFrontendConfig(): array
    {
        $settings = $this->chatbotSettings();

        return [
            'name' => $settings['chatbot_name'],
            'welcome' => $settings['chatbot_welcome'],
            'placeholder' => $settings['chatbot_placeholder'],
            'configured' => (string) config('ai.deepseek_api_key', '') !== '',
        ];
    }

    private function chatbotSettings(): array
    {
        $stored = Cache::remember(self::SETTINGS_CACHE_KEY, self::SETTINGS_CACHE_TTL, function () {
            return DB::table('system_settings')
                ->whereIn('key', [
                    'chatbot_name',
                    'chatbot_personality',
                    'chatbot_welcome',
                    'chatbot_placeholder',
                ])
                ->pluck('value', 'key')
                ->all();
        });

        return [
            'chatbot_name' => $stored['chatbot_name'] ?? 'Skillify AI',
            'chatbot_personality' => $stored['chatbot_personality'] ?? "You are Skillify AI, a warm and capable learning assistant inside a digital skills platform. Help students understand lessons, stay motivated, break down concepts clearly, and suggest practical next steps. Keep answers supportive, concise, and easy to follow. Do not claim to have accessed grades or hidden platform data unless the user explicitly provides it in the chat.",
            'chatbot_welcome' => $stored['chatbot_welcome'] ?? 'Hi, I am here to help with your courses, roadmap, and study questions.',
            'chatbot_placeholder' => $stored['chatbot_placeholder'] ?? 'Ask about this course, chapter, or your study plan...',
        ];
    }

    private function chatbotInstructions(array $settings): string
    {
        return "Your name is ".trim($settings['chatbot_name']).". Always introduce yourself using that exact name when asked who you are or what your name is.\n\n"
            .trim($settings['chatbot_personality'])
            ."\n\nPlatform context: Skillify is a digital skills learning platform with courses, roadmaps, chapters, mentors, progress tracking, reviews, Q&A, and attendance/consistent mode."
            ."\nOnly claim website facts that appear in the provided website context for this request. If something is not in the context, say you are not sure yet rather than inventing it.";
    }

    private function chatbotModel(): string
    {
        $rawModel = strtolower(trim((string) config('ai.deepseek_model', 'deepseek-chat')));
        $supportedModels = [
            'deepseek-v4-flash',
            'deepseek-v4-pro',
            'deepseek-chat',
            'deepseek-reasoner',
        ];

        return in_array($rawModel, $supportedModels, true) ? $rawModel : 'deepseek-chat';
    }

    private function extractAssistantText(array $responsePayload): string
    {
        return trim((string) data_get($responsePayload, 'choices.0.message.content', ''));
    }

    private function isIdentityQuestion(string $message): bool
    {
        $normalized = Str::lower(trim($message));

        $patterns = [
            'who are you',
            'what is your name',
            'what’s your name',
            'whats your name',
            'who r u',
            'siapa kamu',
            'kamu siapa',
            'nama kamu siapa',
            'siapa namamu',
            'siapa anda',
            'namamu siapa',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function identityReply(array $settings): string
    {
        $name = trim((string) ($settings['chatbot_name'] ?? 'Skillify AI'));

        return "Hi, I'm {$name}. I'm your learning copilot here on Skillify, ready to help with your courses, roadmaps, chapters, and study questions.";
    }

    private function websiteContext(string $pagePath, string $pageTitle, array $clientContext = []): string
    {
        $cacheKey = 'chatbot:context:user:'.auth()->id().':'.md5($pagePath.'|'.$pageTitle);

        $baseContext = Cache::remember($cacheKey, self::CONTEXT_CACHE_TTL, function () use ($pagePath, $pageTitle) {
            $segments = [
                'Website context for current request:',
                'Site name: Skillify.',
                'Current page title: '.($pageTitle !== '' ? $pageTitle : 'Unknown'),
                'Current page path: '.($pagePath !== '' ? $pagePath : '/'),
                $this->studentProfileContext(),
                $this->pageSpecificContext($pagePath),
            ];

            return implode("\n\n", array_filter($segments));
        });

        $clientSegments = [];
        if (!empty($clientContext['local_progress'])) {
            $clientSegments[] = 'Browser-local student progress context:'."\n".$clientContext['local_progress'];
        }
        if (!empty($clientContext['local_notes'])) {
            $clientSegments[] = 'Browser-local student notes for this page:'."\n".Str::limit($clientContext['local_notes'], 1800);
        }
        if (!empty($clientContext['page_snapshot'])) {
            $clientSegments[] = 'Current page content snapshot from the browser:'."\n".Str::limit($clientContext['page_snapshot'], 7000);
        }

        return $baseContext.(count($clientSegments) ? "\n\n".implode("\n\n", $clientSegments) : '');
    }

    private function studentProfileContext(): string
    {
        $user = auth()->user();

        $states = DB::table('user_course_states')
            ->where('user_id', $user->id)
            ->orderBy('course_title')
            ->get([
                'course_slug',
                'course_title',
                'is_enrolled',
                'is_favorite',
                'consistent_mode_enabled',
                'consistent_mode_target',
            ]);

        $enrolled = $states->where('is_enrolled', true)->pluck('course_title')->take(8)->values()->all();
        $favorites = $states->where('is_favorite', true)->pluck('course_title')->take(8)->values()->all();
        $consistent = $states
            ->where('consistent_mode_enabled', true)
            ->map(fn ($item) => $item->course_title.' (target '.$item->consistent_mode_target.' chapter/day)')
            ->take(6)
            ->values()
            ->all();

        return implode("\n", array_filter([
            'Current user: '.$user->name.' (role: '.$user->role.', account status: '.$user->account_status.').',
            $user->bio ? 'User bio: '.Str::limit($user->bio, 220) : null,
            'Enrolled courses: '.($enrolled ? implode(', ', $enrolled) : 'none yet'),
            'Favorite courses: '.($favorites ? implode(', ', $favorites) : 'none yet'),
            'Consistent mode courses: '.($consistent ? implode(', ', $consistent) : 'none active'),
        ]));
    }

    private function pageSpecificContext(string $pagePath): string
    {
        if ($pagePath === '/student/dashboard') {
            return $this->dashboardPageContext();
        }

        if ($pagePath === '/student/courses') {
            return $this->coursesDirectoryContext();
        }

        if ($pagePath === '/student/mentors') {
            return $this->mentorsDirectoryContext();
        }

        if (preg_match('#^/mentors/(\d+)$#', $pagePath, $matches) === 1) {
            return $this->mentorPageContext((int) $matches[1]);
        }

        if ($pagePath === '/courses/frontend-craft' || $pagePath === '/courses/frontend-craft/info') {
            return $this->frontendCraftInfoContext();
        }

        if ($pagePath === '/courses/frontend-craft/roadmap') {
            return $this->frontendCraftRoadmapContext();
        }

        if (preg_match('#^/courses/frontend-craft/chapters/(\d+)$#', $pagePath, $matches) === 1) {
            return $this->frontendCraftChapterContext((int) $matches[1]);
        }

        if (preg_match('#^/courses/quiz/(\d+)/info$#', $pagePath, $matches) === 1 || preg_match('#^/courses/quiz/(\d+)$#', $pagePath, $matches) === 1) {
            return $this->quizInfoContext((int) $matches[1]);
        }

        if (preg_match('#^/courses/quiz/(\d+)/roadmap$#', $pagePath, $matches) === 1) {
            return $this->quizRoadmapContext((int) $matches[1]);
        }

        if (preg_match('#^/courses/quiz/(\d+)/chapters/(\d+)$#', $pagePath, $matches) === 1) {
            return $this->quizChapterContext((int) $matches[1], (int) $matches[2]);
        }

        return 'No extra page-specific website data was found for this page.';
    }

    private function dashboardPageContext(): string
    {
        $states = DB::table('user_course_states')
            ->where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get(['course_title', 'is_enrolled', 'is_favorite']);

        $mentors = DB::table('users as u')
            ->leftJoin('quizzes as q', 'q.created_by', '=', 'u.id')
            ->where('u.role', 'dosen')
            ->groupBy('u.id', 'u.name')
            ->orderByRaw('COUNT(q.id) DESC')
            ->limit(6)
            ->get(['u.name', DB::raw('COUNT(q.id) as courses_count')]);

        return implode("\n", [
            'Page type: student dashboard.',
            'Recent student courses: '.($states->count() ? $states->map(fn ($row) => $row->course_title.($row->is_enrolled ? ' [enrolled]' : '').($row->is_favorite ? ' [favorite]' : ''))->implode(', ') : 'none'),
            'Top mentors shown on dashboard: '.($mentors->count() ? $mentors->map(fn ($row) => $row->name.' ('.$row->courses_count.' courses)')->implode(', ') : 'none'),
        ]);
    }

    private function coursesDirectoryContext(): string
    {
        $courses = DB::table('quizzes as q')
            ->leftJoin('users as u', 'u.id', '=', 'q.created_by')
            ->orderByDesc('q.created_at')
            ->limit(10)
            ->get(['q.id', 'q.title', 'q.category', 'q.difficulty', 'u.name as mentor_name']);

        $items = collect([
            'Frontend Craft (built-in course, category: Web Development, difficulty: beginner)',
        ]);

        foreach ($courses as $course) {
            $items->push($course->title.' (category: '.($course->category ?? 'General').', difficulty: '.($course->difficulty ?? 'unknown').', mentor: '.($course->mentor_name ?? 'Unknown').')');
        }

        return "Page type: student courses directory.\nVisible course catalog includes: ".$items->take(10)->implode(', ');
    }

    private function mentorsDirectoryContext(): string
    {
        $mentors = DB::table('users as u')
            ->leftJoin('quizzes as q', 'q.created_by', '=', 'u.id')
            ->where('u.role', 'dosen')
            ->groupBy('u.id', 'u.name', 'u.bio')
            ->orderByRaw('COUNT(q.id) DESC')
            ->limit(8)
            ->get(['u.name', 'u.bio', DB::raw('COUNT(q.id) as courses_count')]);

        return "Page type: student mentors directory.\nVisible mentors: ".($mentors->count()
            ? $mentors->map(fn ($mentor) => $mentor->name.' ('.$mentor->courses_count.' courses, bio: '.Str::limit((string) ($mentor->bio ?? 'No bio'), 80).')')->implode(', ')
            : 'none');
    }

    private function mentorPageContext(int $mentorId): string
    {
        $mentor = DB::table('users')
            ->where('id', $mentorId)
            ->first(['id', 'name', 'bio', 'role']);

        if (!$mentor) {
            return 'Mentor profile was requested, but the mentor record was not found.';
        }

        $courses = DB::table('quizzes')
            ->where('created_by', $mentorId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['title', 'category', 'difficulty']);

        return implode("\n", [
            'Page type: mentor profile.',
            'Mentor name: '.$mentor->name,
            'Mentor role: '.$mentor->role,
            'Mentor bio: '.Str::limit((string) ($mentor->bio ?? 'No bio provided.'), 220),
            'Mentor courses: '.($courses->count()
                ? $courses->map(fn ($course) => $course->title.' ('.$course->category.', '.$course->difficulty.')')->implode(', ')
                : 'none'),
        ]);
    }

    private function frontendCraftInfoContext(): string
    {
        $content = DB::table('course_page_contents')
            ->where('course_slug', 'frontend-craft')
            ->first();

        $lessons = DB::table('course_lessons')
            ->where('course_slug', 'frontend-craft')
            ->orderBy('chapter_number')
            ->limit(8)
            ->get(['chapter_number', 'title']);

        $reviewSummary = DB::table('course_reviews')
            ->where('course_slug', 'frontend-craft')
            ->selectRaw('COUNT(*) as total_reviews, ROUND(AVG(rating), 1) as avg_rating')
            ->first();

        return implode("\n", [
            'Page type: built-in course info page.',
            'Course title: '.($content?->hero_title ?: 'Frontend Craft'),
            'Tagline: '.($content?->tagline ?: 'Not set'),
            'About: '.Str::limit((string) ($content?->about ?: 'Not set'), 240),
            'Target audience: '.($content?->target_audience ?: 'Not set'),
            'Duration text: '.($content?->duration_text ?: 'Not set'),
            'Instructor: '.($content?->instructor_name ?: 'Digital Skill Team'),
            'Chapters available: '.($lessons->count() ? $lessons->map(fn ($lesson) => $lesson->chapter_number.'. '.($lesson->title ?: 'Chapter '.$lesson->chapter_number))->implode(', ') : 'none'),
            'Review summary: '.(($reviewSummary && $reviewSummary->total_reviews) ? $reviewSummary->avg_rating.'/5 from '.$reviewSummary->total_reviews.' reviews' : 'no reviews yet'),
        ]);
    }

    private function frontendCraftRoadmapContext(): string
    {
        $state = DB::table('user_course_states')
            ->where('user_id', auth()->id())
            ->where('course_slug', 'frontend-craft')
            ->first();

        $attendance = DB::table('course_attendance_logs')
            ->where('user_id', auth()->id())
            ->where('course_slug', 'frontend-craft')
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        $lessons = DB::table('course_lessons')
            ->where('course_slug', 'frontend-craft')
            ->orderBy('chapter_number')
            ->limit(10)
            ->get(['chapter_number', 'title', 'description']);

        return implode("\n", [
            'Page type: built-in course roadmap.',
            'Current course: Frontend Craft.',
            'Student enrolled: '.(!empty($state?->is_enrolled) ? 'yes' : 'no'),
            'Student favorite: '.(!empty($state?->is_favorite) ? 'yes' : 'no'),
            'Consistent mode: '.(!empty($state?->consistent_mode_enabled) ? 'enabled with target '.$state->consistent_mode_target.' chapter/day' : 'disabled'),
            'Today attendance progress: '.($attendance ? $attendance->chapters_completed.'/'.$attendance->target_chapters.' chapters, attended='.(($attendance->is_attended) ? 'yes' : 'no') : 'no attendance log today'),
            'Roadmap chapters: '.($lessons->count() ? $lessons->map(fn ($lesson) => $lesson->chapter_number.'. '.($lesson->title ?: 'Chapter '.$lesson->chapter_number).' - '.Str::limit((string) ($lesson->description ?? 'No description'), 70))->implode(' | ') : 'none'),
        ]);
    }

    private function frontendCraftChapterContext(int $chapter): string
    {
        $lesson = DB::table('course_lessons')
            ->where('course_slug', 'frontend-craft')
            ->where('chapter_number', $chapter)
            ->first(['chapter_number', 'title', 'description', 'video_url', 'video_path']);

        $qaCount = DB::table('course_questions')
            ->where('course_slug', 'frontend-craft')
            ->where(function ($query) use ($chapter) {
                $query->whereNull('chapter_number')->orWhere('chapter_number', $chapter);
            })
            ->count();

        return implode("\n", [
            'Page type: built-in course chapter page.',
            'Current course: Frontend Craft.',
            'Current chapter number: '.$chapter,
            'Current chapter title: '.($lesson?->title ?: 'Chapter '.$chapter),
            'Current chapter description: '.Str::limit((string) ($lesson?->description ?: 'No description'), 240),
            'Video available: '.(($lesson && ($lesson->video_url || $lesson->video_path)) ? 'yes' : 'no'),
            'Q&A entries related to this chapter: '.$qaCount,
        ]);
    }

    private function quizInfoContext(int $quizId): string
    {
        $course = DB::table('quizzes as q')
            ->leftJoin('quiz_course_infos as qi', 'qi.quiz_id', '=', 'q.id')
            ->leftJoin('users as u', 'u.id', '=', 'q.created_by')
            ->where('q.id', $quizId)
            ->first([
                'q.id',
                'q.title',
                'q.category',
                'q.difficulty',
                'u.name as mentor_name',
                'qi.tagline',
                'qi.about',
                'qi.target_audience',
            ]);

        if (!$course) {
            return 'Quiz course page was requested, but the course was not found.';
        }

        $state = DB::table('user_course_states')
            ->where('user_id', auth()->id())
            ->where('course_slug', 'quiz-'.$quizId)
            ->first();

        $lessonCount = DB::table('course_lessons')
            ->where('course_slug', 'quiz-'.$quizId)
            ->count();

        return implode("\n", [
            'Page type: quiz course info page.',
            'Course title: '.$course->title,
            'Category: '.($course->category ?? 'General'),
            'Difficulty: '.($course->difficulty ?? 'unknown'),
            'Mentor: '.($course->mentor_name ?? 'Unknown'),
            'Tagline: '.($course->tagline ?? 'Not set'),
            'About: '.Str::limit((string) ($course->about ?? 'Not set'), 240),
            'Target audience: '.($course->target_audience ?? 'Not set'),
            'Student enrolled: '.(!empty($state?->is_enrolled) ? 'yes' : 'no'),
            'Chapter count: '.$lessonCount,
        ]);
    }

    private function quizRoadmapContext(int $quizId): string
    {
        $course = DB::table('quizzes')
            ->where('id', $quizId)
            ->first(['id', 'title']);

        if (!$course) {
            return 'Quiz roadmap page was requested, but the course was not found.';
        }

        $slug = 'quiz-'.$quizId;
        $state = DB::table('user_course_states')
            ->where('user_id', auth()->id())
            ->where('course_slug', $slug)
            ->first();
        $attendance = DB::table('course_attendance_logs')
            ->where('user_id', auth()->id())
            ->where('course_slug', $slug)
            ->whereDate('attendance_date', now()->toDateString())
            ->first();
        $lessons = DB::table('course_lessons')
            ->where('course_slug', $slug)
            ->orderBy('chapter_number')
            ->limit(10)
            ->get(['chapter_number', 'title', 'description']);

        return implode("\n", [
            'Page type: quiz course roadmap.',
            'Current course: '.$course->title,
            'Student enrolled: '.(!empty($state?->is_enrolled) ? 'yes' : 'no'),
            'Student favorite: '.(!empty($state?->is_favorite) ? 'yes' : 'no'),
            'Consistent mode: '.(!empty($state?->consistent_mode_enabled) ? 'enabled with target '.$state->consistent_mode_target.' chapter/day' : 'disabled'),
            'Today attendance progress: '.($attendance ? $attendance->chapters_completed.'/'.$attendance->target_chapters.' chapters, attended='.(($attendance->is_attended) ? 'yes' : 'no') : 'no attendance log today'),
            'Roadmap chapters: '.($lessons->count() ? $lessons->map(fn ($lesson) => $lesson->chapter_number.'. '.($lesson->title ?: 'Chapter '.$lesson->chapter_number).' - '.Str::limit((string) ($lesson->description ?? 'No description'), 70))->implode(' | ') : 'none'),
        ]);
    }

    private function quizChapterContext(int $quizId, int $chapter): string
    {
        $course = DB::table('quizzes')
            ->where('id', $quizId)
            ->first(['title']);

        $slug = 'quiz-'.$quizId;
        $lesson = DB::table('course_lessons')
            ->where('course_slug', $slug)
            ->where('chapter_number', $chapter)
            ->first(['chapter_number', 'title', 'description', 'video_url', 'video_path']);
        $qaCount = DB::table('course_questions')
            ->where('course_slug', $slug)
            ->where(function ($query) use ($chapter) {
                $query->whereNull('chapter_number')->orWhere('chapter_number', $chapter);
            })
            ->count();

        return implode("\n", [
            'Page type: quiz course chapter page.',
            'Current course: '.($course?->title ?? 'Unknown course'),
            'Current chapter number: '.$chapter,
            'Current chapter title: '.($lesson?->title ?: 'Chapter '.$chapter),
            'Current chapter description: '.Str::limit((string) ($lesson?->description ?: 'No description'), 240),
            'Video available: '.(($lesson && ($lesson->video_url || $lesson->video_path)) ? 'yes' : 'no'),
            'Q&A entries related to this chapter: '.$qaCount,
        ]);
    }
}
