<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCourseState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentCourseController extends Controller
{
    private const CACHE_TTL_SECONDS = 300;

    private function cacheKey(string $prefix, string $value): string
    {
        return 'student_course:'.$prefix.':'.$value;
    }

    private function frontendCraftContent(): ?object
    {
        return Cache::remember(
            $this->cacheKey('course_page_content', 'frontend-craft'),
            self::CACHE_TTL_SECONDS,
            fn () => DB::table('course_page_contents')
                ->where('course_slug', 'frontend-craft')
                ->first()
        );
    }

    private function quizRecord(int $quizId): ?object
    {
        return Cache::remember(
            $this->cacheKey('quiz_record', (string) $quizId),
            self::CACHE_TTL_SECONDS,
            fn () => DB::table('quizzes')
                ->where('id', $quizId)
                ->first(['id', 'title', 'category', 'difficulty', 'created_by', 'created_at', 'updated_at'])
        );
    }

    private function quizCourseInfo(int $quizId): ?object
    {
        return Cache::remember(
            $this->cacheKey('quiz_course_info', (string) $quizId),
            self::CACHE_TTL_SECONDS,
            fn () => DB::table('quiz_course_infos')
                ->where('quiz_id', $quizId)
                ->first()
        );
    }

    private function userNameById(?int $userId): ?string
    {
        if (empty($userId)) {
            return null;
        }

        return Cache::remember(
            $this->cacheKey('user_name', (string) $userId),
            self::CACHE_TTL_SECONDS,
            fn () => DB::table('users')->where('id', $userId)->value('name')
        );
    }

    private function lessonsByCourseSlug(string $slug)
    {
        return Cache::remember(
            $this->cacheKey('course_lessons', $slug),
            self::CACHE_TTL_SECONDS,
            fn () => DB::table('course_lessons')
                ->where('course_slug', $slug)
                ->get()
                ->keyBy('chapter_number')
        );
    }

    private function chapterCountForLessons($lessons): int
    {
        return max(8, (int) ($lessons->keys()->max() ?? 0));
    }

    private function chapterDefaults(): array
    {
        return [
            1 => ['title' => 'Kickoff Setup', 'description' => 'Atur tools dan workspace agar siap belajar tanpa hambatan.'],
            2 => ['title' => 'Semantic HTML', 'description' => 'Bangun struktur halaman yang bersih, rapi, dan accessible.'],
            3 => ['title' => 'CSS Foundation', 'description' => 'Kuasai color, spacing, typography, dan komposisi visual modern.'],
            4 => ['title' => 'Responsive Layout', 'description' => 'Pelajari Flexbox dan Grid supaya desain adaptif di semua layar.'],
            5 => ['title' => 'JavaScript Core', 'description' => 'Pahami dasar logika, function, dan alur interaksi UI.'],
            6 => ['title' => 'DOM Interaction', 'description' => 'Hubungkan script dan tampilan dengan event serta validasi form.'],
            7 => ['title' => 'Mini Project', 'description' => 'Bangun produk nyata dari nol agar skill langsung terpakai.'],
            8 => ['title' => 'Deploy Polish', 'description' => 'Optimasi final dan rilis project ke publik sebagai portfolio.'],
        ];
    }

    private function quizCourseSlug(int $quizId): string
    {
        return 'quiz-'.$quizId;
    }

    private function quizIdFromSlug(string $slug): ?int
    {
        if (preg_match('/^quiz-(\d+)$/', $slug, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function chapterCountForSlug(string $slug): int
    {
        return $this->chapterCountForLessons($this->lessonsByCourseSlug($slug));
    }

    private function popQuizDefinitionsBySlug(string $slug)
    {
        return DB::table('question_bank')
            ->where('course_slug', $slug)
            ->whereRaw('"is_pop_quiz" IS TRUE')
            ->whereNotNull('placement_after_chapter')
            ->orderBy('placement_after_chapter')
            ->orderBy('id')
            ->get([
                'id',
                'course_slug',
                'placement_after_chapter',
                'question_text',
                'question_type',
                'correct_answer',
                'options_json',
                'difficulty',
                'requires_perfect_score',
            ]);
    }

    private function popQuizQuestionGroups(string $slug): array
    {
        return $this->popQuizDefinitionsBySlug($slug)
            ->groupBy(fn ($row) => (int) $row->placement_after_chapter)
            ->map(fn ($group) => $group->values())
            ->all();
    }

    private function popQuizProgressRows(string $slug): array
    {
        return DB::table('user_pop_quiz_progress')
            ->where('user_id', auth()->id())
            ->where('course_slug', $slug)
            ->get()
            ->keyBy(fn ($row) => (int) $row->placement_after_chapter)
            ->all();
    }

    private function passedPopQuizPlacements(string $slug): array
    {
        return DB::table('user_pop_quiz_progress')
            ->where('user_id', auth()->id())
            ->where('course_slug', $slug)
            ->whereNotNull('passed_at')
            ->orderBy('placement_after_chapter')
            ->pluck('placement_after_chapter')
            ->map(fn ($placement) => (int) $placement)
            ->all();
    }

    private function popQuizRouteBySlug(string $slug, int $afterChapter): string
    {
        if ($slug === 'frontend-craft') {
            return route('courses.frontend-craft.pop-quiz', ['afterChapter' => $afterChapter]);
        }

        return route('courses.quiz.pop-quiz', [
            'quiz' => $this->quizIdFromSlug($slug),
            'afterChapter' => $afterChapter,
        ]);
    }

    private function buildPendingPopQuizPayload(string $slug, int $afterChapter, array $quizGroups, array $quizProgressRows): ?array
    {
        if (!isset($quizGroups[$afterChapter])) {
            return null;
        }

        $progressRow = $quizProgressRows[$afterChapter] ?? null;

        return [
            'placement_after_chapter' => $afterChapter,
            'question_count' => count($quizGroups[$afterChapter]),
            'take_quiz_url' => $this->popQuizRouteBySlug($slug, $afterChapter),
            'is_passed' => !empty($progressRow?->passed_at),
            'last_score_percent' => $progressRow ? (float) $progressRow->score_percent : null,
            'last_correct_answers' => $progressRow ? (int) $progressRow->correct_answers : null,
            'last_total_questions' => $progressRow ? (int) $progressRow->total_questions : null,
        ];
    }

    private function completedChapterNumbers(string $slug): array
    {
        return DB::table('user_chapter_progress')
            ->where('user_id', auth()->id())
            ->where('course_slug', $slug)
            ->whereNotNull('completed_at')
            ->orderBy('chapter_number')
            ->pluck('chapter_number')
            ->map(fn ($chapter) => (int) $chapter)
            ->all();
    }

    private function nextUnlockedChapter(array $completedChapters, int $chaptersCount): int
    {
        $completedLookup = array_fill_keys($completedChapters, true);

        for ($chapter = 1; $chapter <= $chaptersCount; $chapter++) {
            if (!isset($completedLookup[$chapter])) {
                return $chapter;
            }
        }

        return max(1, $chaptersCount);
    }

    private function chapterStateMap(string $slug, int $chaptersCount): array
    {
        $completedChapters = $this->completedChapterNumbers($slug);
        $completedLookup = array_fill_keys($completedChapters, true);
        $quizGroups = $this->popQuizQuestionGroups($slug);
        $quizProgressRows = $this->popQuizProgressRows($slug);
        $passedQuizLookup = array_fill_keys($this->passedPopQuizPlacements($slug), true);
        $states = [];
        $isNextChapterAccessible = true;
        $nextAction = null;
        $popQuizStates = [];

        for ($chapter = 1; $chapter <= $chaptersCount; $chapter++) {
            $isCompleted = isset($completedLookup[$chapter]);
            $isLocked = $chapter === 1 ? false : !$isNextChapterAccessible;

            $states[$chapter] = [
                'is_completed' => $isCompleted,
                'is_locked' => $isLocked,
                'is_unlocked' => !$isLocked,
            ];

            if (isset($quizGroups[$chapter])) {
                $popQuizStates[$chapter] = $this->buildPendingPopQuizPayload($slug, $chapter, $quizGroups, $quizProgressRows);
            }

            if ($nextAction === null) {
                if (!$isCompleted) {
                    $nextAction = [
                        'type' => 'chapter',
                        'chapter' => $chapter,
                    ];
                } elseif (isset($quizGroups[$chapter]) && !isset($passedQuizLookup[$chapter])) {
                    $nextAction = [
                        'type' => 'pop_quiz',
                        'placement_after_chapter' => $chapter,
                    ] + ($popQuizStates[$chapter] ?? []);
                }
            }

            $isNextChapterAccessible = $isCompleted && (!isset($quizGroups[$chapter]) || isset($passedQuizLookup[$chapter]));
        }

        $unlockedChapter = $nextAction && $nextAction['type'] === 'chapter'
            ? (int) $nextAction['chapter']
            : $this->nextUnlockedChapter($completedChapters, $chaptersCount);

        return [
            'completed' => $completedChapters,
            'completed_lookup' => $completedLookup,
            'unlocked_chapter' => $unlockedChapter,
            'states' => $states,
            'completed_count' => count($completedChapters),
            'next_action' => $nextAction,
            'pop_quizzes' => $popQuizStates,
            'passed_pop_quizzes' => array_keys($passedQuizLookup),
        ];
    }

    private function markChapterCompleted(string $slug, int $chapter): void
    {
        DB::table('user_chapter_progress')->updateOrInsert(
            [
                'user_id' => auth()->id(),
                'course_slug' => $slug,
                'chapter_number' => $chapter,
            ],
            [
                'completed_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function normalizeQuizAnswer(?string $answer): string
    {
        $value = mb_strtolower(trim((string) $answer));

        return preg_replace('/\s+/', ' ', $value) ?? '';
    }

    private function popQuizQuestionsForPlacement(string $slug, int $afterChapter)
    {
        return collect($this->popQuizQuestionGroups($slug)[$afterChapter] ?? []);
    }

    private function evaluatePopQuizAnswers($questions, array $answers): array
    {
        $resultRows = [];
        $correctAnswers = 0;

        foreach ($questions as $question) {
            $answer = $answers[$question->id] ?? null;
            $normalizedUser = $this->normalizeQuizAnswer(is_array($answer) ? implode(' ', $answer) : (string) $answer);
            $normalizedCorrect = $this->normalizeQuizAnswer((string) ($question->correct_answer ?? ''));
            $isCorrect = $normalizedCorrect !== '' && $normalizedUser === $normalizedCorrect;

            if ($isCorrect) {
                $correctAnswers++;
            }

            $resultRows[] = [
                'id' => (int) $question->id,
                'question_text' => (string) $question->question_text,
                'question_type' => (string) $question->question_type,
                'your_answer' => is_array($answer) ? implode(', ', $answer) : trim((string) $answer),
                'correct_answer' => (string) ($question->correct_answer ?? ''),
                'is_correct' => $isCorrect,
            ];
        }

        $totalQuestions = count($resultRows);
        $scorePercent = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

        return [
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score_percent' => $scorePercent,
            'passed' => $totalQuestions > 0 && $correctAnswers === $totalQuestions,
            'results' => $resultRows,
        ];
    }

    private function savePopQuizProgress(string $slug, int $afterChapter, array $evaluation): void
    {
        $existing = DB::table('user_pop_quiz_progress')
            ->where('user_id', auth()->id())
            ->where('course_slug', $slug)
            ->where('placement_after_chapter', $afterChapter)
            ->first();

        $passedAt = $evaluation['passed']
            ? ($existing->passed_at ?? now())
            : ($existing->passed_at ?? null);

        DB::table('user_pop_quiz_progress')->updateOrInsert(
            [
                'user_id' => auth()->id(),
                'course_slug' => $slug,
                'placement_after_chapter' => $afterChapter,
            ],
            [
                'total_questions' => $evaluation['total_questions'],
                'correct_answers' => $evaluation['correct_answers'],
                'score_percent' => $evaluation['score_percent'],
                'answers_json' => json_encode($evaluation['results']),
                'last_submitted_at' => now(),
                'passed_at' => $passedAt,
                'updated_at' => now(),
                'created_at' => $existing->created_at ?? now(),
            ]
        );
    }

    private function completionResponse(string $slug, int $chapter, int $chaptersCount, string $roadmapUrl, ?array $context = null): array
    {
        $this->markChapterCompleted($slug, $chapter);

        if ($context) {
            $this->registerChapterAttendance($context, $chapter);
        }

        $progress = $this->chapterStateMap($slug, $chaptersCount);
        $nextChapter = $chapter < $chaptersCount ? $chapter + 1 : null;
        $nextUrl = null;

        if ($nextChapter && !($progress['states'][$nextChapter]['is_locked'] ?? true)) {
            $nextUrl = $slug === 'frontend-craft'
                ? route('courses.frontend-craft.chapter', ['chapter' => $nextChapter])
                : route('courses.quiz.chapter', ['quiz' => $this->quizIdFromSlug($slug), 'chapter' => $nextChapter]);
        }

        return [
            'ok' => true,
            'completed_count' => $progress['completed_count'],
            'chapters_count' => $chaptersCount,
            'next_url' => $nextUrl,
            'roadmap_url' => $roadmapUrl,
            'unlocked_chapter' => $progress['unlocked_chapter'],
            'pending_pop_quiz' => $progress['next_action']['type'] === 'pop_quiz' ? $progress['next_action'] : null,
        ];
    }

    private function completionRedirectResponse(array $payload): RedirectResponse
    {
        if (!empty($payload['pending_pop_quiz']['placement_after_chapter'])) {
            return redirect()->to($payload['roadmap_url']);
        }

        return redirect()->to($payload['next_url'] ?? $payload['roadmap_url']);
    }

    private function chapterHasCompletableVideo(string $slug, int $chapter): bool
    {
        $lesson = $this->lessonsByCourseSlug($slug)->get($chapter);

        return (bool) ($lesson && ($lesson->video_url || $lesson->video_path));
    }

    private function forgetAttendanceDashboardCaches(?int $dosenId = null): void
    {
        Cache::forget('dashboard:admin:data');

        if ($dosenId !== null) {
            Cache::forget('dashboard:dosen:'.$dosenId.':data');
        }
    }

    private function attendanceDate(): string
    {
        return now()->toDateString();
    }

    private function ensureCourseStateRow(array $context): UserCourseState
    {
        return UserCourseState::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'course_slug' => $context['course_slug'],
            ],
            [
                'course_title' => $context['course_title'],
                'is_enrolled' => false,
                'is_favorite' => false,
                'consistent_mode_enabled' => false,
                'consistent_mode_target' => 1,
            ]
        );
    }

    private function fetchTodayAttendanceLog(string $slug): ?object
    {
        return DB::table('course_attendance_logs')
            ->where('user_id', auth()->id())
            ->where('course_slug', $slug)
            ->where('attendance_date', $this->attendanceDate())
            ->first();
    }

    private function completedChapterCountForDate(int $userId, string $slug, string $date): int
    {
        return (int) DB::table('user_chapter_progress')
            ->where('user_id', $userId)
            ->where('course_slug', $slug)
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', $date)
            ->count();
    }

    private function attendancePayload(array $context, ?UserCourseState $state = null, ?object $todayLog = null): array
    {
        $state ??= $this->ensureCourseStateRow($context);
        $todayLog ??= $this->fetchTodayAttendanceLog($context['course_slug']);

        if ((bool) $state->consistent_mode_enabled && $todayLog) {
            $todayLog = $this->refreshAttendanceProgress($context, $todayLog);
        }

        return [
            'enabled' => (bool) $state->consistent_mode_enabled,
            'target_chapters' => max(1, (int) ($state->consistent_mode_target ?? 1)),
            'today_completed' => (int) ($todayLog->chapters_completed ?? 0),
            'today_attended' => (bool) ($todayLog->is_attended ?? false),
            'roadmap_entered' => !empty($todayLog?->roadmap_entered_at),
            'attendance_date' => $this->attendanceDate(),
        ];
    }

    private function upsertTodayAttendanceLog(array $context, UserCourseState $state, bool $markRoadmapEntered = false): object
    {
        $date = $this->attendanceDate();
        $existing = DB::table('course_attendance_logs')
            ->where('user_id', auth()->id())
            ->where('course_slug', $context['course_slug'])
            ->where('attendance_date', $date)
            ->first();

        $payload = [
            'dosen_id' => $context['dosen_id'],
            'quiz_id' => $context['quiz_id'],
            'course_title' => $context['course_title'],
            'target_chapters' => max(1, (int) ($state->consistent_mode_target ?? 1)),
            'updated_at' => now(),
        ];

        if ($markRoadmapEntered && empty($existing?->roadmap_entered_at)) {
            $payload['roadmap_entered_at'] = now();
        }

        if ($existing) {
            DB::table('course_attendance_logs')
                ->where('id', $existing->id)
                ->update($payload);
        } else {
            DB::table('course_attendance_logs')->insert($payload + [
                'user_id' => auth()->id(),
                'course_slug' => $context['course_slug'],
                'attendance_date' => $date,
                'chapters_completed' => 0,
                'is_attended' => false,
                'created_at' => now(),
            ]);
        }

        $log = $this->fetchTodayAttendanceLog($context['course_slug']);

        $this->forgetAttendanceDashboardCaches($context['dosen_id']);

        return $log;
    }

    private function refreshAttendanceProgress(array $context, object $log): object
    {
        $completedCount = $this->completedChapterCountForDate(
            (int) $log->user_id,
            (string) $log->course_slug,
            (string) $log->attendance_date
        );
        $target = max(1, (int) $log->target_chapters);
        $isAttended = $completedCount >= $target;

        DB::table('course_attendance_logs')
            ->where('id', $log->id)
            ->update([
                'chapters_completed' => $completedCount,
                'is_attended' => $isAttended,
                'attended_at' => $isAttended ? ($log->attended_at ?? now()) : null,
                'updated_at' => now(),
            ]);

        $this->forgetAttendanceDashboardCaches($context['dosen_id']);

        return $this->fetchTodayAttendanceLog($context['course_slug']);
    }

    private function beginAttendanceSession(array $context): array
    {
        $state = $this->ensureCourseStateRow($context);
        if (!(bool) $state->consistent_mode_enabled) {
            return $this->attendancePayload($context, $state);
        }

        $log = $this->upsertTodayAttendanceLog($context, $state, true);

        return $this->attendancePayload($context, $state, $log);
    }

    private function registerChapterAttendance(array $context, int $chapterNumber): array
    {
        $state = $this->ensureCourseStateRow($context);
        if (!(bool) $state->consistent_mode_enabled) {
            return $this->attendancePayload($context, $state);
        }

        $log = $this->upsertTodayAttendanceLog($context, $state, true);

        DB::table('course_attendance_chapters')->insertOrIgnore([
            'attendance_log_id' => $log->id,
            'user_id' => auth()->id(),
            'course_slug' => $context['course_slug'],
            'attendance_date' => $this->attendanceDate(),
            'chapter_number' => $chapterNumber,
            'visited_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $log = $this->refreshAttendanceProgress($context, $log);

        return $this->attendancePayload($context, $state, $log);
    }

    private function catalog(): array
    {
        return [
            'frontend-craft' => [
                'title' => 'Frontend Craft',
                'route' => 'courses.frontend-craft',
                'roadmap_route' => 'courses.frontend-craft.roadmap',
            ],
        ];
    }

    private function findQuizCourse(int $quizId): ?object
    {
        $quiz = $this->quizRecord($quizId);
        if (!$quiz) {
            return null;
        }

        $info = $this->quizCourseInfo($quizId);

        return (object) [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'category' => $quiz->category,
            'difficulty' => $quiz->difficulty,
            'created_by' => $quiz->created_by,
            'created_at' => $quiz->created_at,
            'updated_at' => $quiz->updated_at,
            'hero_title' => $info->hero_title ?? null,
            'hero_background_url' => $info->hero_background_url ?? null,
            'tagline' => $info->tagline ?? null,
            'about' => $info->about ?? null,
            'target_audience' => $info->target_audience ?? null,
            'duration_text' => $info->duration_text ?? null,
            'syllabus_json' => $info->syllabus_json ?? null,
            'learning_outcomes' => $info->learning_outcomes ?? null,
            'trailer_url' => $info->trailer_url ?? null,
            'trailer_poster_url' => $info->trailer_poster_url ?? null,
            'instructor_name' => $info->instructor_name ?? null,
            'instructor_photo_url' => $info->instructor_photo_url ?? null,
            'mentor_name' => $info->instructor_name ?? null,
        ];
    }

    private function resolveCourseBySlug(string $slug): ?array
    {
        if ($slug === 'frontend-craft') {
            return [
                'title' => 'Frontend Craft',
                'route' => route('courses.frontend-craft'),
                'roadmap_route' => route('courses.frontend-craft.roadmap'),
            ];
        }

        $quizId = $this->quizIdFromSlug($slug);
        if (!$quizId) {
            return null;
        }

        $quiz = $this->quizRecord($quizId);
        if (!$quiz) {
            return null;
        }

        return [
            'title' => $quiz->title,
            'route' => route('courses.quiz.show', ['quiz' => $quizId]),
            'roadmap_route' => route('courses.quiz.roadmap', ['quiz' => $quizId]),
        ];
    }

    private function resolveCourseContextBySlug(string $slug): ?array
    {
        if ($slug === 'frontend-craft') {
            $content = $this->frontendCraftContent();

            return [
                'course_slug' => 'frontend-craft',
                'course_title' => !empty($content?->hero_title) ? $content->hero_title : 'Frontend Craft',
                'quiz_id' => null,
                'dosen_id' => $content->updated_by ?? null,
                'info_route' => route('courses.frontend-craft.info'),
            ];
        }

        $quizId = $this->quizIdFromSlug($slug);
        if (!$quizId) {
            return null;
        }

        $quiz = $this->quizRecord($quizId);
        if (!$quiz) {
            return null;
        }

        return [
            'course_slug' => $slug,
            'course_title' => $quiz->title,
            'quiz_id' => $quiz->id,
            'dosen_id' => $quiz->created_by,
            'info_route' => route('courses.quiz.info', ['quiz' => $quiz->id]),
        ];
    }

    private function buildCarouselCourses(?string $search = null)
    {
        $search = trim((string) $search);

        if ($search === '') {
            return Cache::remember(
                $this->cacheKey('carousel_courses', 'all'),
                self::CACHE_TTL_SECONDS,
                fn () => $this->buildCarouselCoursesResult('')
            );
        }

        return $this->buildCarouselCoursesResult($search);
    }

    private function buildCarouselCoursesResult(string $search)
    {
        $frontendCraftContent = $this->frontendCraftContent();

        $quizCoursesQuery = DB::table('quizzes as q')
            ->leftJoin('quiz_course_infos as qi', 'qi.quiz_id', '=', 'q.id')
            ->leftJoin('users as u', 'u.id', '=', 'q.created_by')
            ->select(
                'q.id',
                'q.title',
                'q.category',
                'q.difficulty',
                'u.name as mentor_name',
                'qi.hero_background_url'
            )
            ->orderByDesc('q.created_at')
            ->limit(36);

        if ($search !== '') {
            $quizCoursesQuery->where(function ($query) use ($search) {
                $query->where('q.title', 'like', '%'.$search.'%')
                    ->orWhere('q.category', 'like', '%'.$search.'%')
                    ->orWhere('u.name', 'like', '%'.$search.'%');
            });
        }

        $quizCourses = $quizCoursesQuery->get();

        $carouselCourses = collect();
        $frontendMatch = $search === ''
            || Str::contains(Str::lower('Frontend Craft Web Development'), Str::lower($search));

        if ($frontendMatch) {
            $carouselCourses->push([
                'title' => 'Frontend Craft',
                'category' => 'Web Development',
                'description' => 'Build interactive and responsive modern websites with practical projects.',
                'href' => route('courses.frontend-craft'),
                'image' => $frontendCraftContent?->hero_background_url,
            ]);
        }

        foreach ($quizCourses as $quizCourse) {
            if (Str::slug((string) $quizCourse->title) === 'frontend-craft') {
                continue;
            }

            $carouselCourses->push([
                'title' => $quizCourse->title,
                'category' => $quizCourse->category ?: 'General Course',
                'description' => 'Mentor: '.($quizCourse->mentor_name ?? 'Digital Skill Team').' - Difficulty: '.ucfirst((string) $quizCourse->difficulty),
                'href' => route('courses.quiz.show', ['quiz' => $quizCourse->id]),
                'image' => $quizCourse->hero_background_url,
            ]);
        }

        return $carouselCourses;
    }

    private function buildMentors(?string $search = null)
    {
        $search = trim((string) $search);

        if ($search === '') {
            return Cache::remember(
                $this->cacheKey('mentors', 'all'),
                self::CACHE_TTL_SECONDS,
                fn () => $this->buildMentorsResult('')
            );
        }

        return $this->buildMentorsResult($search);
    }

    private function buildMentorsResult(string $search)
    {
        $query = DB::table('users as u')
            ->leftJoin('quizzes as q', 'q.created_by', '=', 'u.id')
            ->where('u.role', 'dosen')
            ->select(
                'u.id',
                'u.name',
                'u.profile_image',
                'u.bio',
                DB::raw('COUNT(q.id) as courses_count')
            )
            ->groupBy('u.id', 'u.name', 'u.profile_image', 'u.bio')
            ->orderByDesc('courses_count')
            ->orderBy('u.name');

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('u.name', 'like', '%'.$search.'%')
                    ->orWhere('u.bio', 'like', '%'.$search.'%');
            });
        }

        return $query->get();
    }

    private function pathfinderProfile(): ?object
    {
        return DB::table('user_learning_profiles')
            ->where('user_id', auth()->id())
            ->first();
    }

    private function pathfinderDiscoverySignals(): array
    {
        $locale = app()->getLocale();

        return [
            'builder' => [
                'title' => $locale === 'id' ? 'Suka membangun hal yang interaktif' : 'I like building interactive things',
                'text' => $locale === 'id'
                    ? 'Website, interface, dan produk digital terasa paling seru saat bisa langsung dibuat.'
                    : 'Websites, interfaces, and digital products feel most exciting when I can build them directly.',
                'category_hint' => 'web-development',
            ],
            'strategist' => [
                'title' => $locale === 'id' ? 'Tertarik menyusun arah dan keputusan' : 'I enjoy shaping direction and decisions',
                'text' => $locale === 'id'
                    ? 'Saya suka memahami bagaimana bisnis, strategi, dan sistem bergerak.'
                    : 'I like understanding how business, strategy, and systems move together.',
                'category_hint' => 'business',
            ],
            'promoter' => [
                'title' => $locale === 'id' ? 'Tertarik membuat sesuatu lebih dikenal' : 'I like helping ideas reach more people',
                'text' => $locale === 'id'
                    ? 'Campaign, audience, dan pertumbuhan digital terasa menarik buat saya.'
                    : 'Campaigns, audience growth, and digital reach are the things I want to understand more.',
                'category_hint' => 'digital-marketing',
            ],
            'designer' => [
                'title' => $locale === 'id' ? 'Peka pada visual dan komunikasi' : 'I care about visual impact and communication',
                'text' => $locale === 'id'
                    ? 'Layout, identitas visual, dan storytelling lewat desain terasa paling natural.'
                    : 'Layout, visual identity, and storytelling through design feel the most natural to me.',
                'category_hint' => 'graphic-design',
            ],
        ];
    }

    private function categorySlugForSignal(string $signal): string
    {
        return $this->pathfinderDiscoverySignals()[$signal]['category_hint'] ?? 'web-development';
    }

    private function recommendationCatalog()
    {
        $frontendCraftContent = $this->frontendCraftContent();

        $catalog = collect([
            [
                'key' => 'frontend-craft',
                'title' => 'Frontend Craft',
                'category' => 'Web Development',
                'difficulty' => 'beginner',
                'summary' => (string) ($frontendCraftContent->tagline
                    ?? 'Build interactive and responsive modern websites with practical projects.'),
                'href' => route('courses.frontend-craft'),
                'roadmap_href' => route('courses.frontend-craft.roadmap'),
                'image' => $frontendCraftContent->hero_background_url ?? null,
            ],
        ]);

        $quizCourses = DB::table('quizzes as q')
            ->leftJoin('quiz_course_infos as qi', 'qi.quiz_id', '=', 'q.id')
            ->select(
                'q.id',
                'q.title',
                'q.category',
                'q.difficulty',
                'qi.tagline',
                'qi.about',
                'qi.hero_background_url'
            )
            ->orderByDesc('q.created_at')
            ->get();

        foreach ($quizCourses as $course) {
            $title = trim((string) $course->title);
            if ($title === '' || Str::slug($title) === 'frontend-craft') {
                continue;
            }

            $catalog->push([
                'key' => 'quiz-'.$course->id,
                'title' => $title,
                'category' => trim((string) ($course->category ?: 'General Course')),
                'difficulty' => trim((string) ($course->difficulty ?: 'beginner')),
                'summary' => trim((string) ($course->tagline ?: $course->about ?: 'Structured digital learning path with mentor guidance.')),
                'href' => route('courses.quiz.show', ['quiz' => $course->id]),
                'roadmap_href' => route('courses.quiz.roadmap', ['quiz' => $course->id]),
                'image' => $course->hero_background_url,
            ]);
        }

        return $catalog->values();
    }

    private function pathfinderCategoryOptions(): array
    {
        $descriptions = [
            'web-development' => [
                'en' => 'Build websites, interfaces, and front-end projects.',
                'id' => 'Bangun website, interface, dan project front-end.',
            ],
            'business' => [
                'en' => 'Learn strategy, operations, and business thinking.',
                'id' => 'Pelajari strategi, operasional, dan cara pikir bisnis.',
            ],
            'digital-marketing' => [
                'en' => 'Focus on campaigns, audience growth, and digital reach.',
                'id' => 'Fokus pada campaign, pertumbuhan audiens, dan jangkauan digital.',
            ],
            'graphic-design' => [
                'en' => 'Explore visual communication, layout, and creative tools.',
                'id' => 'Eksplor komunikasi visual, layout, dan tools kreatif.',
            ],
        ];

        $locale = app()->getLocale();

        return $this->recommendationCatalog()
            ->groupBy(fn ($course) => trim((string) $course['category']))
            ->map(function ($courses, $category) use ($descriptions, $locale) {
                $slug = Str::slug((string) $category);
                $fallbackDescription = $locale === 'id'
                    ? 'Rekomendasi kursus di area belajar ini.'
                    : 'Recommended courses in this learning area.';

                return [
                    'slug' => $slug,
                    'name' => $category,
                    'count' => $courses->count(),
                    'description' => $descriptions[$slug][$locale] ?? $fallbackDescription,
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    private function pathfinderAnswers(?object $profile): array
    {
        return [
            'discovery_signal' => (string) ($profile->primary_interest ?? ''),
            'goal' => (string) ($profile->goal ?? ''),
            'experience_level' => (string) ($profile->experience_level ?? ''),
            'study_pace' => (string) ($profile->study_pace ?? ''),
            'learning_style' => (string) ($profile->learning_style ?? ''),
        ];
    }

    private function recommendedCoursesForPathfinder(array $answers)
    {
        $signal = trim((string) ($answers['discovery_signal'] ?? ''));
        $primaryInterest = $this->categorySlugForSignal($signal);
        $goal = trim((string) ($answers['goal'] ?? ''));
        $experience = trim((string) ($answers['experience_level'] ?? ''));
        $pace = trim((string) ($answers['study_pace'] ?? ''));
        $style = trim((string) ($answers['learning_style'] ?? ''));

        return $this->recommendationCatalog()
            ->map(function ($course) use ($primaryInterest, $goal, $experience, $pace, $style) {
                $categorySlug = Str::slug((string) $course['category']);
                $difficulty = Str::lower((string) $course['difficulty']);
                $haystack = Str::lower(trim($course['title'].' '.$course['summary'].' '.$course['category']));

                $score = 18;
                $reasons = [];

                if ($primaryInterest !== '' && $categorySlug === $primaryInterest) {
                    $score += 60;
                    $reasons[] = 'interest_match';
                }

                if ($experience === 'beginner') {
                    if (in_array($difficulty, ['beginner', 'pemula'], true)) {
                        $score += 20;
                        $reasons[] = 'level_match';
                    } else {
                        $score += 8;
                    }
                } elseif ($experience === 'intermediate') {
                    if (in_array($difficulty, ['intermediate', 'menengah'], true)) {
                        $score += 18;
                        $reasons[] = 'level_match';
                    } else {
                        $score += 10;
                    }
                } elseif ($experience === 'advanced') {
                    $score += 8;
                }

                if ($goal === 'career') {
                    $score += 10;
                    $reasons[] = 'goal_match';
                } elseif ($goal === 'portfolio' && preg_match('/project|build|portfolio|craft|praktik|praktis/', $haystack)) {
                    $score += 16;
                    $reasons[] = 'goal_match';
                } elseif ($goal === 'business' && preg_match('/business|marketing|brand|sales|finance|strategy|startup/', $haystack)) {
                    $score += 18;
                    $reasons[] = 'goal_match';
                } elseif ($goal === 'explore') {
                    $score += 6;
                }

                if ($style === 'hands_on' && preg_match('/project|build|practice|practical|portfolio|hands-on|praktik|praktis/', $haystack)) {
                    $score += 14;
                    $reasons[] = 'style_match';
                } elseif ($style === 'guided') {
                    $score += 8;
                } elseif ($style === 'theory' && preg_match('/foundation|fundamental|core|strategy|basic|dasar/', $haystack)) {
                    $score += 12;
                    $reasons[] = 'style_match';
                }

                if ($pace === 'light' && in_array($difficulty, ['beginner', 'pemula'], true)) {
                    $score += 8;
                } elseif ($pace === 'steady') {
                    $score += 6;
                } elseif ($pace === 'intensive' && preg_match('/project|core|advanced|deploy|responsive|javascript/', $haystack)) {
                    $score += 8;
                }

                $course['match_score'] = min(98, $score);
                $course['reasons'] = array_values(array_unique($reasons));

                return $course;
            })
            ->sortByDesc('match_score')
            ->values()
            ->take(3);
    }

    private function topRecommendedCourseFromProfile(?object $profile): ?array
    {
        if (!$profile || empty($profile->recommendations_json)) {
            return null;
        }

        $recommendations = json_decode((string) $profile->recommendations_json, true);
        if (!is_array($recommendations) || empty($recommendations[0]['key'])) {
            return null;
        }

        $topKey = (string) $recommendations[0]['key'];
        $course = $this->recommendationCatalog()->firstWhere('key', $topKey);

        if (!$course) {
            return null;
        }

        $course['match_score'] = (int) ($recommendations[0]['match_score'] ?? ($course['match_score'] ?? 0));

        return $course;
    }

    public function pathfinder(Request $request): View
    {
        $catalog = $this->recommendationCatalog();
        $profile = $this->pathfinderProfile();
        $answers = $this->pathfinderAnswers($profile);
        $recommendations = !empty($profile?->completed_at)
            ? $this->recommendedCoursesForPathfinder($answers)
            : collect();

        return view('student.pathfinder', [
            'showWelcome' => $request->boolean('welcome'),
            'pathfinderProfile' => $profile,
            'pathfinderAnswers' => $answers,
            'pathfinderCategories' => $this->pathfinderCategoryOptions(),
            'pathfinderSignals' => $this->pathfinderDiscoverySignals(),
            'pathfinderRecommendations' => $recommendations,
            'pathfinderCourseCount' => $catalog->count(),
            'pathfinderTopRecommendation' => $this->topRecommendedCourseFromProfile($profile),
        ]);
    }

    public function savePathfinder(Request $request): RedirectResponse
    {
        if ($request->input('action') === 'skip') {
            DB::table('user_learning_profiles')->updateOrInsert(
                ['user_id' => auth()->id()],
                [
                    'skipped_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            return redirect()->route('student.dashboard')
                ->with('success', __('ui.pathfinder.skip_success'));
        }

        $signals = array_keys($this->pathfinderDiscoverySignals());

        $validated = $request->validate([
            'discovery_signal' => ['required', 'string', 'in:'.implode(',', $signals)],
            'goal' => ['required', 'string', 'in:career,business,portfolio,explore'],
            'experience_level' => ['required', 'string', 'in:beginner,intermediate,advanced'],
            'study_pace' => ['required', 'string', 'in:light,steady,intensive'],
            'learning_style' => ['required', 'string', 'in:hands_on,guided,theory'],
        ]);

        $recommendations = $this->recommendedCoursesForPathfinder($validated);
        $topRecommendation = $recommendations->first();

        DB::table('user_learning_profiles')->updateOrInsert(
            ['user_id' => auth()->id()],
            [
                'primary_interest' => $validated['discovery_signal'],
                'goal' => $validated['goal'],
                'experience_level' => $validated['experience_level'],
                'study_pace' => $validated['study_pace'],
                'learning_style' => $validated['learning_style'],
                'recommendations_json' => json_encode(
                    $recommendations
                        ->map(fn ($course) => [
                            'key' => $course['key'],
                            'title' => $course['title'],
                            'category' => $course['category'],
                            'href' => $course['href'],
                            'match_score' => $course['match_score'],
                        ])
                        ->values()
                        ->all()
                ),
                'completed_at' => now(),
                'skipped_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->to($topRecommendation['href'] ?? route('student.dashboard'))
            ->with('success', __('ui.pathfinder.saved_success'));
    }

    public function dashboard(): View|RedirectResponse
    {
        $states = auth()->user()
            ->courseStates()
            ->get()
            ->keyBy('course_slug');

        $enrolledCourses = [];
        $favoriteCourses = [];

        foreach ($states as $state) {
            if ($state->course_slug === 'frontend-craft') {
                if ($state->is_enrolled) {
                    $enrolledCourses[] = [
                        'slug' => $state->course_slug,
                        'title' => $state->course_title,
                        'route' => route('courses.frontend-craft'),
                        'roadmap_route' => route('courses.frontend-craft.roadmap'),
                    ];
                }

                if ($state->is_favorite) {
                    $favoriteCourses[] = [
                        'slug' => $state->course_slug,
                        'title' => $state->course_title,
                        'route' => route('courses.frontend-craft'),
                    ];
                }

                continue;
            }

            $quizId = $this->quizIdFromSlug($state->course_slug);
            if (!$quizId) {
                continue;
            }

            if ($state->is_enrolled) {
                $enrolledCourses[] = [
                    'slug' => $state->course_slug,
                    'title' => $state->course_title,
                    'route' => route('courses.quiz.show', ['quiz' => $quizId]),
                    'roadmap_route' => route('courses.quiz.roadmap', ['quiz' => $quizId]),
                ];
            }

            if ($state->is_favorite) {
                $favoriteCourses[] = [
                    'slug' => $state->course_slug,
                    'title' => $state->course_title,
                    'route' => route('courses.quiz.show', ['quiz' => $quizId]),
                ];
            }
        }

        $carouselCourses = $this->buildCarouselCourses();
        $mentors = $this->buildMentors();
        $pathfinderRecommendation = $this->topRecommendedCourseFromProfile($this->pathfinderProfile());

        return view('student.dashboard', compact('enrolledCourses', 'favoriteCourses', 'carouselCourses', 'mentors', 'pathfinderRecommendation'));
    }

    public function coursesDirectory(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $courses = $this->buildCarouselCourses($search);

        return view('student.courses-directory', [
            'courses' => $courses,
            'search' => $search,
        ]);
    }

    public function mentorsDirectory(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $mentors = $this->buildMentors($search);

        return view('student.mentors-directory', [
            'mentors' => $mentors,
            'search' => $search,
        ]);
    }

    public function showQuizCourse(int $quiz): View|RedirectResponse
    {
        $courseSlug = $this->quizCourseSlug($quiz);
        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', $courseSlug)
            ->first();

        if ($state && $state->is_enrolled) {
            return redirect()->route('courses.quiz.roadmap', ['quiz' => $quiz]);
        }

        return $this->renderQuizCoursePage($quiz, (bool) optional($state)->is_enrolled);
    }

    public function quizInfo(int $quiz): View
    {
        $courseSlug = $this->quizCourseSlug($quiz);
        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', $courseSlug)
            ->first();

        return $this->renderQuizCoursePage($quiz, (bool) optional($state)->is_enrolled);
    }

    private function renderQuizCoursePage(int $quiz, bool $isEnrolled): View
    {
        $course = $this->findQuizCourse($quiz);
        abort_if(!$course, 404);

        $courseSlug = $this->quizCourseSlug($quiz);
        $reviews = DB::table('course_reviews')
            ->where('course_slug', $courseSlug)
            ->latest()
            ->limit(12)
            ->get(['user_id', 'rating', 'review_text', 'created_at']);
        $reviewUsers = User::query()
            ->whereIn('id', $reviews->pluck('user_id')->unique())
            ->pluck('name', 'id');
        $reviewSummary = DB::table('course_reviews')
            ->where('course_slug', $courseSlug)
            ->selectRaw('COUNT(*) as total_reviews, ROUND(AVG(rating), 1) as avg_rating')
            ->first();
        $userReview = DB::table('course_reviews')
            ->where('course_slug', $courseSlug)
            ->where('user_id', auth()->id())
            ->first(['id', 'rating', 'review_text']);

        $questions = DB::table('course_questions')
            ->where('course_slug', $courseSlug)
            ->latest()
            ->limit(12)
            ->get(['user_id', 'chapter_number', 'question_text', 'answer_text', 'created_at']);
        $questionUsers = User::query()
            ->whereIn('id', $questions->pluck('user_id')->unique())
            ->pluck('name', 'id');
        $chapterCount = $this->chapterCountForSlug($courseSlug);

        return view('courses.quiz-course', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'courseSlug' => $courseSlug,
            'reviews' => $reviews,
            'reviewUsers' => $reviewUsers,
            'reviewSummary' => $reviewSummary,
            'userReview' => $userReview,
            'questions' => $questions,
            'questionUsers' => $questionUsers,
            'chapterCount' => $chapterCount,
        ]);
    }

    public function mentorProfile(User $mentor): View
    {
        abort_unless($mentor->role === 'dosen', 404);

        $courses = DB::table('quizzes')
            ->where('created_by', $mentor->id)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'category', 'difficulty', 'created_at']);

        return view('student.mentor-profile', compact('mentor', 'courses'));
    }

    public function frontendCraft(): View|RedirectResponse
    {
        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', 'frontend-craft')
            ->first();

        if ($state && $state->is_enrolled) {
            return redirect()->route('courses.frontend-craft.roadmap');
        }

        return $this->renderFrontendCraftPage((bool) optional($state)->is_enrolled);
    }

    public function frontendCraftInfo(): View
    {
        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', 'frontend-craft')
            ->first();

        return $this->renderFrontendCraftPage((bool) optional($state)->is_enrolled);
    }

    private function renderFrontendCraftPage(bool $isEnrolled): View
    {
        $content = $this->frontendCraftContent();

        $syllabus = [];
        if (!empty($content?->syllabus_json)) {
            $decoded = json_decode($content->syllabus_json, true);
            if (is_array($decoded)) {
                $syllabus = $decoded;
            }
        }

        $reviews = DB::table('course_reviews')
            ->where('course_slug', 'frontend-craft')
            ->latest()
            ->limit(12)
            ->get(['user_id', 'rating', 'review_text', 'created_at']);
        $reviewUsers = User::query()
            ->whereIn('id', $reviews->pluck('user_id')->unique())
            ->pluck('name', 'id');
        $reviewSummary = DB::table('course_reviews')
            ->where('course_slug', 'frontend-craft')
            ->selectRaw('COUNT(*) as total_reviews, ROUND(AVG(rating), 1) as avg_rating')
            ->first();
        $userReview = DB::table('course_reviews')
            ->where('course_slug', 'frontend-craft')
            ->where('user_id', auth()->id())
            ->first(['id', 'rating', 'review_text']);

        $questions = DB::table('course_questions')
            ->where('course_slug', 'frontend-craft')
            ->latest()
            ->limit(12)
            ->get(['user_id', 'chapter_number', 'question_text', 'answer_text', 'created_at']);
        $questionUsers = User::query()
            ->whereIn('id', $questions->pluck('user_id')->unique())
            ->pluck('name', 'id');
        $chapterCount = $this->chapterCountForSlug('frontend-craft');

        return view('courses.frontend-craft', [
            'isEnrolled' => $isEnrolled,
            'courseContent' => $content,
            'courseSyllabus' => $syllabus,
            'reviews' => $reviews,
            'reviewUsers' => $reviewUsers,
            'reviewSummary' => $reviewSummary,
            'userReview' => $userReview,
            'questions' => $questions,
            'questionUsers' => $questionUsers,
            'chapterCount' => $chapterCount,
        ]);
    }

    public function frontendCraftRoadmap(): View
    {
        @set_time_limit(180);

        $context = $this->resolveCourseContextBySlug('frontend-craft');
        abort_if(!$context, 404);
        $attendance = $this->beginAttendanceSession($context);

        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', 'frontend-craft')
            ->first(['is_favorite']);

        $lessons = $this->lessonsByCourseSlug('frontend-craft');
        $chapterDefaults = $this->chapterDefaults();
        $chaptersCount = $this->chapterCountForLessons($lessons);
        $progress = $this->chapterStateMap('frontend-craft', $chaptersCount);
        $startChapter = $progress['unlocked_chapter'];

        $chapters = [];
        for ($i = 1; $i <= $chaptersCount; $i++) {
            $lesson = $lessons->get($i);
            $default = $chapterDefaults[$i] ?? [
                'title' => 'Chapter '.$i,
                'description' => 'Materi lanjutan chapter '.$i.'.',
            ];

            $chapters[] = [
                'number' => $i,
                'title' => $lesson?->title ?? $default['title'],
                'description' => $lesson?->description ? Str::limit($lesson->description, 90) : $default['description'],
                'video_ready' => (bool) ($lesson && ($lesson->video_url || $lesson->video_path)),
                'position' => $i % 2 === 0 ? 'up' : 'down',
                'href' => ($progress['states'][$i]['is_locked'] ?? false) ? null : route('courses.frontend-craft.chapter', ['chapter' => $i]),
                'is_completed' => $progress['states'][$i]['is_completed'] ?? false,
                'is_locked' => $progress['states'][$i]['is_locked'] ?? false,
                'pop_quiz' => $progress['pop_quizzes'][$i] ?? null,
            ];
        }

        $roadmapQuestions = collect();
        $roadmapQuestionUsers = collect();

        return view('courses.frontend-craft-roadmap', [
            'isFavorite' => (bool) optional($state)->is_favorite,
            'roadmapTitle' => !empty($this->frontendCraftContent()?->hero_title) ? $this->frontendCraftContent()->hero_title : 'Frontend Craft',
            'chapters' => $chapters,
            'attendance' => $attendance,
            'roadmapQuestions' => $roadmapQuestions,
            'roadmapQuestionUsers' => $roadmapQuestionUsers,
            'startChapterUrl' => route('courses.frontend-craft.chapter', ['chapter' => $startChapter]),
            'pendingPopQuiz' => $progress['next_action']['type'] === 'pop_quiz' ? $progress['next_action'] : null,
        ]);
    }

    public function enrollQuiz(int $quiz): RedirectResponse
    {
        $course = $this->quizRecord($quiz);
        abort_if(!$course, 404);

        $slug = $this->quizCourseSlug($quiz);
        $existing = DB::table('user_course_states')
            ->where('user_id', auth()->id())
            ->where('course_slug', $slug)
            ->first();

        if ($existing) {
            DB::table('user_course_states')
                ->where('id', $existing->id)
                ->update([
                    'course_title' => $course->title,
                    'is_enrolled' => DB::raw('TRUE'),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('user_course_states')->insert([
                'user_id' => auth()->id(),
                'course_slug' => $slug,
                'course_title' => $course->title,
                'is_enrolled' => DB::raw('TRUE'),
                'is_favorite' => DB::raw('FALSE'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('courses.quiz.roadmap', ['quiz' => $quiz]);
    }

    public function quizRoadmap(int $quiz): View|RedirectResponse
    {
        @set_time_limit(180);

        $course = $this->findQuizCourse($quiz);
        abort_if(!$course, 404);

        $slug = $this->quizCourseSlug($quiz);
        $context = $this->resolveCourseContextBySlug($slug);
        abort_if(!$context, 404);
        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', $slug)
            ->first();

        if (!$state || !$state->is_enrolled) {
            return redirect()->route('courses.quiz.show', ['quiz' => $quiz]);
        }

        $attendance = $this->beginAttendanceSession($context);

        $lessons = $this->lessonsByCourseSlug($slug);
        $chapterDefaults = $this->chapterDefaults();
        $chaptersCount = $this->chapterCountForLessons($lessons);
        $progress = $this->chapterStateMap($slug, $chaptersCount);
        $startChapter = $progress['unlocked_chapter'];

        $chapters = [];
        for ($i = 1; $i <= $chaptersCount; $i++) {
            $lesson = $lessons->get($i);
            $default = $chapterDefaults[$i] ?? [
                'title' => 'Chapter '.$i,
                'description' => 'Materi lanjutan chapter '.$i.'.',
            ];

            $chapters[] = [
                'number' => $i,
                'title' => $lesson?->title ?? $default['title'],
                'description' => $lesson?->description ? Str::limit($lesson->description, 90) : $default['description'],
                'video_ready' => (bool) ($lesson && ($lesson->video_url || $lesson->video_path)),
                'position' => $i % 2 === 0 ? 'up' : 'down',
                'href' => ($progress['states'][$i]['is_locked'] ?? false) ? null : route('courses.quiz.chapter', ['quiz' => $quiz, 'chapter' => $i]),
                'is_completed' => $progress['states'][$i]['is_completed'] ?? false,
                'is_locked' => $progress['states'][$i]['is_locked'] ?? false,
                'pop_quiz' => $progress['pop_quizzes'][$i] ?? null,
            ];
        }

        $roadmapQuestions = DB::table('course_questions')
            ->where('course_slug', $slug)
            ->latest()
            ->limit(40)
            ->get(['user_id', 'chapter_number', 'question_text', 'answer_text', 'created_at']);
        $roadmapQuestionUsers = User::query()
            ->whereIn('id', $roadmapQuestions->pluck('user_id')->unique())
            ->pluck('name', 'id');

        return view('courses.quiz-roadmap', [
            'course' => $course,
            'roadmapTitle' => !empty($course->hero_title) ? $course->hero_title : $course->title,
            'chapters' => $chapters,
            'isFavorite' => (bool) optional($state)->is_favorite,
            'attendance' => $attendance,
            'roadmapQuestions' => $roadmapQuestions,
            'roadmapQuestionUsers' => $roadmapQuestionUsers,
            'startChapterUrl' => route('courses.quiz.chapter', ['quiz' => $course->id, 'chapter' => $startChapter]),
            'pendingPopQuiz' => $progress['next_action']['type'] === 'pop_quiz' ? $progress['next_action'] : null,
        ]);
    }

    public function quizChapter(int $quiz, int $chapter): View|RedirectResponse
    {
        @set_time_limit(180);

        $course = $this->findQuizCourse($quiz);
        abort_if(!$course, 404);

        $slug = $this->quizCourseSlug($quiz);
        $context = $this->resolveCourseContextBySlug($slug);
        abort_if(!$context, 404);
        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', $slug)
            ->first();

        if (!$state || !$state->is_enrolled) {
            return redirect()->route('courses.quiz.show', ['quiz' => $quiz]);
        }

        $lessonRows = $this->lessonsByCourseSlug($slug);
        $lesson = $lessonRows->get($chapter);
        $chaptersCount = $this->chapterCountForLessons($lessonRows);

        if ($chapter < 1 || $chapter > $chaptersCount) {
            abort(404);
        }

        $progress = $this->chapterStateMap($slug, $chaptersCount);
        if ($progress['states'][$chapter]['is_locked'] ?? true) {
            if (($progress['next_action']['type'] ?? null) === 'pop_quiz') {
                return redirect()->route('courses.quiz.roadmap', ['quiz' => $course->id]);
            }

            return redirect()->route('courses.quiz.chapter', ['quiz' => $course->id, 'chapter' => $progress['unlocked_chapter']]);
        }

        $chapterDefaults = $this->chapterDefaults();
        $chapterItems = [];
        for ($i = 1; $i <= $chaptersCount; $i++) {
            $row = $lessonRows->get($i);
            $default = $chapterDefaults[$i] ?? [
                'title' => 'Chapter '.$i,
                'description' => 'Materi lanjutan chapter '.$i.'.',
            ];

            $chapterItems[] = [
                'number' => $i,
                'title' => $row?->title ?? $default['title'],
                'has_video' => (bool) ($row && ($row->video_url || $row->video_path)),
                'href' => ($progress['states'][$i]['is_locked'] ?? false) ? null : route('courses.quiz.chapter', ['quiz' => $course->id, 'chapter' => $i]),
                'is_completed' => $progress['states'][$i]['is_completed'] ?? false,
                'is_locked' => $progress['states'][$i]['is_locked'] ?? false,
            ];
        }

        $videoReadyCount = collect($chapterItems)->where('has_video', true)->count();
        $currentDefault = $chapterDefaults[$chapter] ?? [
            'title' => 'Chapter '.$chapter,
            'description' => 'Materi lanjutan chapter '.$chapter.'.',
        ];
        $qaItems = DB::table('course_questions')
            ->where('course_slug', $slug)
            ->where(function ($query) use ($chapter) {
                $query->whereNull('chapter_number')
                    ->orWhere('chapter_number', $chapter);
            })
            ->latest()
            ->limit(20)
            ->get(['user_id', 'chapter_number', 'question_text', 'answer_text', 'created_at']);
        $qaUsers = User::query()
            ->whereIn('id', $qaItems->pluck('user_id')->unique())
            ->pluck('name', 'id');
        $pendingPopQuizPrompt = (!empty($progress['states'][$chapter]['is_completed']) && !empty($progress['pop_quizzes'][$chapter]) && empty($progress['pop_quizzes'][$chapter]['is_passed']))
            ? $progress['pop_quizzes'][$chapter]
            : null;
        $chapterMentorName = $this->userNameById($lesson?->updated_by)
            ?? $course->mentor_name
            ?? 'Mentor not assigned yet';

        return view('courses.quiz-chapter', [
            'course' => $course,
            'chapter' => $chapter,
            'lesson' => $lesson,
            'lessonTitle' => $lesson?->title ?? $currentDefault['title'],
            'lessonDescription' => $lesson?->description ?? $currentDefault['description'],
            'chapterMentorName' => $chapterMentorName,
            'chapterItems' => $chapterItems,
            'chaptersCount' => $chaptersCount,
            'videoReadyCount' => $videoReadyCount,
            'hasPrevious' => $chapter > 1,
            'hasNext' => $chapter < $chaptersCount && !($progress['states'][$chapter + 1]['is_locked'] ?? true),
            'roadmapUrl' => route('courses.quiz.roadmap', ['quiz' => $course->id]),
            'dashboardUrl' => route('student.dashboard'),
            'chapterPrevUrl' => $chapter > 1 ? route('courses.quiz.chapter', ['quiz' => $course->id, 'chapter' => $chapter - 1]) : null,
            'chapterNextUrl' => ($chapter < $chaptersCount && !($progress['states'][$chapter + 1]['is_locked'] ?? true)) ? route('courses.quiz.chapter', ['quiz' => $course->id, 'chapter' => $chapter + 1]) : null,
            'notesStorageKey' => 'quiz_course_notes_'.$course->id.'_'.$chapter,
            'attendance' => $this->attendancePayload($context),
            'qaItems' => $qaItems,
            'qaUsers' => $qaUsers,
            'qaPostUrl' => route('courses.questions.store', ['slug' => $slug]),
            'completionUrl' => route('courses.quiz.chapter.complete', ['quiz' => $course->id, 'chapter' => $chapter]),
            'isCurrentChapterCompleted' => $progress['states'][$chapter]['is_completed'] ?? false,
            'pendingPopQuizPrompt' => $pendingPopQuizPrompt,
        ]);
    }

    public function frontendCraftChapter(int $chapter): View|RedirectResponse
    {
        @set_time_limit(180);

        $context = $this->resolveCourseContextBySlug('frontend-craft');
        abort_if(!$context, 404);
        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', 'frontend-craft')
            ->first();

        if (!$state || !$state->is_enrolled) {
            return redirect()->route('courses.frontend-craft.info');
        }

        $lessonRows = $this->lessonsByCourseSlug('frontend-craft');
        $lesson = $lessonRows->get($chapter);
        $chaptersCount = $this->chapterCountForLessons($lessonRows);

        if ($chapter < 1 || $chapter > $chaptersCount) {
            abort(404);
        }

        $progress = $this->chapterStateMap('frontend-craft', $chaptersCount);
        if ($progress['states'][$chapter]['is_locked'] ?? true) {
            if (($progress['next_action']['type'] ?? null) === 'pop_quiz') {
                return redirect()->route('courses.frontend-craft.roadmap');
            }

            return redirect()->route('courses.frontend-craft.chapter', ['chapter' => $progress['unlocked_chapter']]);
        }

        $content = $this->frontendCraftContent();
        $chapterDefaults = $this->chapterDefaults();
        $chapterItems = [];
        for ($i = 1; $i <= $chaptersCount; $i++) {
            $row = $lessonRows->get($i);
            $default = $chapterDefaults[$i] ?? [
                'title' => 'Chapter '.$i,
                'description' => 'Materi lanjutan chapter '.$i.'.',
            ];

            $chapterItems[] = [
                'number' => $i,
                'title' => $row?->title ?? $default['title'],
                'has_video' => (bool) ($row && ($row->video_url || $row->video_path)),
                'href' => ($progress['states'][$i]['is_locked'] ?? false) ? null : route('courses.frontend-craft.chapter', ['chapter' => $i]),
                'is_completed' => $progress['states'][$i]['is_completed'] ?? false,
                'is_locked' => $progress['states'][$i]['is_locked'] ?? false,
            ];
        }

        $videoReadyCount = collect($chapterItems)->where('has_video', true)->count();
        $currentDefault = $chapterDefaults[$chapter] ?? [
            'title' => 'Chapter '.$chapter,
            'description' => 'Materi lanjutan chapter '.$chapter.'.',
        ];
        $qaItems = DB::table('course_questions')
            ->where('course_slug', 'frontend-craft')
            ->where(function ($query) use ($chapter) {
                $query->whereNull('chapter_number')
                    ->orWhere('chapter_number', $chapter);
            })
            ->latest()
            ->limit(20)
            ->get(['user_id', 'chapter_number', 'question_text', 'answer_text', 'created_at']);
        $qaUsers = User::query()
            ->whereIn('id', $qaItems->pluck('user_id')->unique())
            ->pluck('name', 'id');
        $course = (object) [
            'id' => null,
            'title' => !empty($content?->hero_title) ? $content->hero_title : 'Frontend Craft',
            'tagline' => $content->tagline ?? null,
            'difficulty' => 'beginner',
            'duration_text' => $content->duration_text ?? null,
            'mentor_name' => $content->instructor_name ?? 'Digital Skill Team',
            'about' => $content->about ?? null,
            'category' => 'Web Development',
            'updated_at' => $content->updated_at ?? now(),
            'created_at' => $content->created_at ?? now(),
        ];
        $pendingPopQuizPrompt = (!empty($progress['states'][$chapter]['is_completed']) && !empty($progress['pop_quizzes'][$chapter]) && empty($progress['pop_quizzes'][$chapter]['is_passed']))
            ? $progress['pop_quizzes'][$chapter]
            : null;
        $chapterMentorName = $this->userNameById($lesson?->updated_by)
            ?? $course->mentor_name
            ?? 'Mentor not assigned yet';

        return view('courses.quiz-chapter', [
            'course' => $course,
            'chapter' => $chapter,
            'lesson' => $lesson,
            'lessonTitle' => $lesson?->title ?? $currentDefault['title'],
            'lessonDescription' => $lesson?->description ?? $currentDefault['description'],
            'chapterMentorName' => $chapterMentorName,
            'chapterItems' => $chapterItems,
            'chaptersCount' => $chaptersCount,
            'videoReadyCount' => $videoReadyCount,
            'hasPrevious' => $chapter > 1,
            'hasNext' => $chapter < $chaptersCount && !($progress['states'][$chapter + 1]['is_locked'] ?? true),
            'roadmapUrl' => route('courses.frontend-craft.roadmap'),
            'dashboardUrl' => route('student.dashboard'),
            'chapterPrevUrl' => $chapter > 1 ? route('courses.frontend-craft.chapter', ['chapter' => $chapter - 1]) : null,
            'chapterNextUrl' => ($chapter < $chaptersCount && !($progress['states'][$chapter + 1]['is_locked'] ?? true)) ? route('courses.frontend-craft.chapter', ['chapter' => $chapter + 1]) : null,
            'notesStorageKey' => 'frontend_craft_notes_'.$chapter,
            'attendance' => $this->attendancePayload($context),
            'qaItems' => $qaItems,
            'qaUsers' => $qaUsers,
            'qaPostUrl' => route('courses.questions.store', ['slug' => 'frontend-craft']),
            'completionUrl' => route('courses.frontend-craft.chapter.complete', ['chapter' => $chapter]),
            'isCurrentChapterCompleted' => $progress['states'][$chapter]['is_completed'] ?? false,
            'pendingPopQuizPrompt' => $pendingPopQuizPrompt,
        ]);
    }

    public function completeQuizChapter(Request $request, int $quiz, int $chapter): JsonResponse|RedirectResponse
    {
        $course = $this->findQuizCourse($quiz);
        abort_if(!$course, 404);

        $slug = $this->quizCourseSlug($quiz);
        $context = $this->resolveCourseContextBySlug($slug);
        abort_if(!$context, 404);

        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', $slug)
            ->whereRaw('"is_enrolled" IS TRUE')
            ->exists();
        abort_unless($state, 403);

        $chaptersCount = $this->chapterCountForSlug($slug);
        abort_if($chapter < 1 || $chapter > $chaptersCount, 404);
        abort_unless($this->chapterHasCompletableVideo($slug, $chapter), 422);

        $progress = $this->chapterStateMap($slug, $chaptersCount);
        abort_if($progress['states'][$chapter]['is_locked'] ?? true, 403);

        $payload = $this->completionResponse(
            $slug,
            $chapter,
            $chaptersCount,
            route('courses.quiz.roadmap', ['quiz' => $quiz]),
            $context
        );

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        if (!empty($payload['pending_pop_quiz']['placement_after_chapter'])) {
            return redirect()->route('courses.quiz.chapter', [
                'quiz' => $quiz,
                'chapter' => $chapter,
                'quiz_gate' => $payload['pending_pop_quiz']['placement_after_chapter'],
            ]);
        }

        return $this->completionRedirectResponse($payload);
    }

    public function completeFrontendCraftChapter(Request $request, int $chapter): JsonResponse|RedirectResponse
    {
        $context = $this->resolveCourseContextBySlug('frontend-craft');
        abort_if(!$context, 404);

        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', 'frontend-craft')
            ->whereRaw('"is_enrolled" IS TRUE')
            ->exists();
        abort_unless($state, 403);

        $chaptersCount = $this->chapterCountForSlug('frontend-craft');
        abort_if($chapter < 1 || $chapter > $chaptersCount, 404);
        abort_unless($this->chapterHasCompletableVideo('frontend-craft', $chapter), 422);

        $progress = $this->chapterStateMap('frontend-craft', $chaptersCount);
        abort_if($progress['states'][$chapter]['is_locked'] ?? true, 403);

        $payload = $this->completionResponse(
            'frontend-craft',
            $chapter,
            $chaptersCount,
            route('courses.frontend-craft.roadmap'),
            $context
        );

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        if (!empty($payload['pending_pop_quiz']['placement_after_chapter'])) {
            return redirect()->route('courses.frontend-craft.chapter', [
                'chapter' => $chapter,
                'quiz_gate' => $payload['pending_pop_quiz']['placement_after_chapter'],
            ]);
        }

        return $this->completionRedirectResponse($payload);
    }

    private function renderPopQuizPage(object $course, string $slug, int $afterChapter, string $roadmapUrl, string $dashboardUrl, string $submitUrl): View|RedirectResponse
    {
        $questions = $this->popQuizQuestionsForPlacement($slug, $afterChapter);
        abort_if($questions->isEmpty(), 404);

        $chaptersCount = $this->chapterCountForSlug($slug);
        $progress = $this->chapterStateMap($slug, $chaptersCount);

        abort_unless(isset($progress['completed_lookup'][$afterChapter]), 403);

        $progressRow = $this->popQuizProgressRows($slug)[$afterChapter] ?? null;
        $questionPayload = $questions->map(function ($question) {
            $options = json_decode((string) ($question->options_json ?? ''), true);

            return [
                'id' => (int) $question->id,
                'question_text' => (string) $question->question_text,
                'question_type' => (string) $question->question_type,
                'difficulty' => (string) $question->difficulty,
                'options' => is_array($options) ? array_values(array_filter($options, fn ($item) => trim((string) $item) !== '')) : [],
            ];
        })->all();

        $nextChapterUrl = $afterChapter < $chaptersCount
            ? ($slug === 'frontend-craft'
                ? route('courses.frontend-craft.chapter', ['chapter' => $afterChapter + 1])
                : route('courses.quiz.chapter', ['quiz' => $course->id, 'chapter' => $afterChapter + 1]))
            : $roadmapUrl;

        return view('courses.pop-quiz', [
            'course' => $course,
            'afterChapter' => $afterChapter,
            'questions' => $questionPayload,
            'roadmapUrl' => $roadmapUrl,
            'dashboardUrl' => $dashboardUrl,
            'submitUrl' => $submitUrl,
            'isPassed' => !empty($progressRow?->passed_at),
            'lastScorePercent' => $progressRow ? (float) $progressRow->score_percent : null,
            'lastCorrectAnswers' => $progressRow ? (int) $progressRow->correct_answers : null,
            'lastTotalQuestions' => $progressRow ? (int) $progressRow->total_questions : count($questionPayload),
            'nextChapterUrl' => $nextChapterUrl,
        ]);
    }

    private function handlePopQuizSubmission(Request $request, object $course, string $slug, int $afterChapter, string $roadmapUrl, string $nextChapterUrl): RedirectResponse
    {
        $questions = $this->popQuizQuestionsForPlacement($slug, $afterChapter);
        abort_if($questions->isEmpty(), 404);

        $answers = $request->input('answers', []);
        abort_unless(is_array($answers), 422);

        $evaluation = $this->evaluatePopQuizAnswers($questions, $answers);
        $this->savePopQuizProgress($slug, $afterChapter, $evaluation);

        if (!$evaluation['passed']) {
            return back()
                ->withInput()
                ->withErrors([
                    'pop_quiz' => 'Jawaban belum sempurna. Kamu harus menjawab semua soal dengan benar untuk membuka chapter berikutnya.',
                ])
                ->with('popQuizResult', $evaluation);
        }

        return redirect()->to($nextChapterUrl)
            ->with('success', 'Pop quiz berhasil diselesaikan. Chapter berikutnya sekarang terbuka.');
    }

    public function frontendCraftPopQuiz(int $afterChapter): View|RedirectResponse
    {
        $context = $this->resolveCourseContextBySlug('frontend-craft');
        abort_if(!$context, 404);

        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', 'frontend-craft')
            ->whereRaw('"is_enrolled" IS TRUE')
            ->exists();
        abort_unless($state, 403);

        $content = $this->frontendCraftContent();
        $course = (object) [
            'id' => null,
            'title' => !empty($content?->hero_title) ? $content->hero_title : 'Frontend Craft',
            'difficulty' => 'beginner',
        ];

        return $this->renderPopQuizPage(
            $course,
            'frontend-craft',
            $afterChapter,
            route('courses.frontend-craft.roadmap'),
            route('student.dashboard'),
            route('courses.frontend-craft.pop-quiz.submit', ['afterChapter' => $afterChapter])
        );
    }

    public function submitFrontendCraftPopQuiz(Request $request, int $afterChapter): RedirectResponse
    {
        $context = $this->resolveCourseContextBySlug('frontend-craft');
        abort_if(!$context, 404);

        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', 'frontend-craft')
            ->whereRaw('"is_enrolled" IS TRUE')
            ->exists();
        abort_unless($state, 403);

        $nextChapterUrl = route('courses.frontend-craft.chapter', ['chapter' => min($this->chapterCountForSlug('frontend-craft'), $afterChapter + 1)]);

        return $this->handlePopQuizSubmission(
            $request,
            (object) ['id' => null, 'title' => 'Frontend Craft'],
            'frontend-craft',
            $afterChapter,
            route('courses.frontend-craft.roadmap'),
            $nextChapterUrl
        );
    }

    public function quizPopQuiz(int $quiz, int $afterChapter): View|RedirectResponse
    {
        $course = $this->findQuizCourse($quiz);
        abort_if(!$course, 404);

        $slug = $this->quizCourseSlug($quiz);
        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', $slug)
            ->whereRaw('"is_enrolled" IS TRUE')
            ->exists();
        abort_unless($state, 403);

        return $this->renderPopQuizPage(
            $course,
            $slug,
            $afterChapter,
            route('courses.quiz.roadmap', ['quiz' => $quiz]),
            route('student.dashboard'),
            route('courses.quiz.pop-quiz.submit', ['quiz' => $quiz, 'afterChapter' => $afterChapter])
        );
    }

    public function submitQuizPopQuiz(Request $request, int $quiz, int $afterChapter): RedirectResponse
    {
        $course = $this->findQuizCourse($quiz);
        abort_if(!$course, 404);

        $slug = $this->quizCourseSlug($quiz);
        $state = auth()->user()
            ->courseStates()
            ->where('course_slug', $slug)
            ->whereRaw('"is_enrolled" IS TRUE')
            ->exists();
        abort_unless($state, 403);

        $nextChapterUrl = route('courses.quiz.chapter', ['quiz' => $quiz, 'chapter' => min($this->chapterCountForSlug($slug), $afterChapter + 1)]);

        return $this->handlePopQuizSubmission(
            $request,
            $course,
            $slug,
            $afterChapter,
            route('courses.quiz.roadmap', ['quiz' => $quiz]),
            $nextChapterUrl
        );
    }

    public function enroll(string $slug): RedirectResponse
    {
        $course = $this->catalog()[$slug] ?? null;
        abort_if(!$course, 404);
        $existing = DB::table('user_course_states')
            ->where('user_id', auth()->id())
            ->where('course_slug', $slug)
            ->first();

        if ($existing) {
            DB::table('user_course_states')
                ->where('id', $existing->id)
                ->update([
                    'course_title' => $course['title'],
                    'is_enrolled' => DB::raw('TRUE'),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('user_course_states')->insert([
                'user_id' => auth()->id(),
                'course_slug' => $slug,
                'course_title' => $course['title'],
                'is_enrolled' => DB::raw('TRUE'),
                'is_favorite' => DB::raw('FALSE'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route($course['roadmap_route']);
    }

    public function toggleFavorite(string $slug): JsonResponse
    {
        $course = $this->resolveCourseBySlug($slug);
        abort_if(!$course, 404);

        $state = UserCourseState::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'course_slug' => $slug,
            ],
            [
                'course_title' => $course['title'],
                'is_enrolled' => false,
                'is_favorite' => false,
            ]
        );

        $state->is_favorite = !$state->is_favorite;
        $state->save();

        return response()->json([
            'is_favorite' => $state->is_favorite,
        ]);
    }

    public function updateConsistentMode(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $context = $this->resolveCourseContextBySlug($slug);
        abort_if(!$context, 404);

        $enrolled = auth()->user()
            ->courseStates()
            ->where('course_slug', $context['course_slug'])
            ->whereRaw('"is_enrolled" IS TRUE')
            ->exists();

        if (!$enrolled) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Enroll course dulu sebelum pakai consistent mode.'], 422);
            }

            return redirect()->to($context['info_route'])->withErrors(['consistent_mode' => 'Enroll course dulu sebelum pakai consistent mode.']);
        }

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'target_chapters' => ['required', 'integer', 'min:1', 'max:'.$this->chapterCountForSlug($context['course_slug'])],
        ]);

        $state = $this->ensureCourseStateRow($context);
        $state->course_title = $context['course_title'];
        $state->consistent_mode_enabled = (bool) $validated['enabled'];
        $state->consistent_mode_target = max(1, (int) $validated['target_chapters']);
        $state->save();

        $attendance = $this->attendancePayload($context, $state);
        if ($state->consistent_mode_enabled) {
            $log = $this->upsertTodayAttendanceLog($context, $state, true);
            $log = $this->refreshAttendanceProgress($context, $log);
            $attendance = $this->attendancePayload($context, $state, $log);
        }

        $this->forgetAttendanceDashboardCaches($context['dosen_id']);

        return response()->json([
            'message' => $state->consistent_mode_enabled
                ? 'Consistent mode aktif. Progress attendance hari ini sudah dimulai.'
                : 'Consistent mode dimatikan untuk course ini.',
            'attendance' => $attendance,
        ]);
    }

    public function submitReview(Request $request, string $slug): RedirectResponse
    {
        $context = $this->resolveCourseContextBySlug($slug);
        abort_if(!$context, 404);

        $enrolled = auth()->user()
            ->courseStates()
            ->where('course_slug', $context['course_slug'])
            ->whereRaw('"is_enrolled" IS TRUE')
            ->exists();

        if (!$enrolled) {
            return redirect()->to($context['info_route'])->withErrors(['review' => 'Enroll course dulu sebelum kirim review.']);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string', 'max:2000'],
        ]);

        $existingReview = DB::table('course_reviews')
            ->where('course_slug', $context['course_slug'])
            ->where('user_id', auth()->id())
            ->first(['id']);

        if ($existingReview) {
            DB::table('course_reviews')
                ->where('id', $existingReview->id)
                ->update([
                    'dosen_id' => $context['dosen_id'],
                    'quiz_id' => $context['quiz_id'],
                    'course_title' => $context['course_title'],
                    'rating' => $validated['rating'],
                    'review_text' => $validated['review_text'] ?? null,
                    'updated_at' => now(),
                ]);

            return redirect()->to($context['info_route'].'#review')->with('success', 'Review berhasil diupdate.');
        }

        DB::table('course_reviews')->insert([
            'user_id' => auth()->id(),
            'dosen_id' => $context['dosen_id'],
            'quiz_id' => $context['quiz_id'],
            'course_slug' => $context['course_slug'],
            'course_title' => $context['course_title'],
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->to($context['info_route'].'#review')->with('success', 'Review berhasil dikirim.');
    }

    public function submitQuestion(Request $request, string $slug): RedirectResponse
    {
        $context = $this->resolveCourseContextBySlug($slug);
        abort_if(!$context, 404);

        $enrolled = auth()->user()
            ->courseStates()
            ->where('course_slug', $context['course_slug'])
            ->whereRaw('"is_enrolled" IS TRUE')
            ->exists();

        if (!$enrolled) {
            return redirect()->to($context['info_route'])->withErrors(['qa' => 'Enroll course dulu sebelum kirim pertanyaan.']);
        }

        $maxChapter = $this->chapterCountForSlug($context['course_slug']);
        $validated = $request->validate([
            'chapter_number' => ['nullable', 'integer', 'min:1', 'max:'.$maxChapter],
            'question_text' => ['required', 'string', 'max:3000'],
        ]);

        DB::table('course_questions')->insert([
            'user_id' => auth()->id(),
            'dosen_id' => $context['dosen_id'],
            'quiz_id' => $context['quiz_id'],
            'course_slug' => $context['course_slug'],
            'course_title' => $context['course_title'],
            'chapter_number' => $validated['chapter_number'] ?? null,
            'question_text' => $validated['question_text'],
            'answer_text' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->to($context['info_route'].'#qa')->with('success', 'Pertanyaan berhasil dikirim ke dosen.');
    }
}
