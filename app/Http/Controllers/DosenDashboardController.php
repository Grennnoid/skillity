<?php

namespace App\Http\Controllers;

use App\Services\AiQuestionGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DosenDashboardController extends Controller
{
    private const DASHBOARD_CACHE_TTL_SECONDS = 120;
    private const AI_PREVIEW_SESSION_KEY = 'dosen_ai_question_preview';

    private function dashboardCacheKey(int $dosenId): string
    {
        return 'dashboard:dosen:'.$dosenId.':data';
    }

    private function buildSyllabusLines(?string $syllabusJson): string
    {
        if (empty($syllabusJson)) {
            return '';
        }

        $rows = json_decode($syllabusJson, true);
        if (!is_array($rows)) {
            return '';
        }

        return collect($rows)
            ->map(fn ($row) => trim(($row['title'] ?? '').'|'.($row['description'] ?? '')))
            ->filter()
            ->implode("\n");
    }

    private function formatQuestionBankCourseLabel(object $row): string
    {
        if (!empty($row->quiz_title)) {
            return $row->quiz_title;
        }

        if (($row->course_slug ?? null) === 'frontend-craft') {
            return 'Frontend Craft';
        }

        return Str::title(str_replace('-', ' ', (string) ($row->course_slug ?? 'unknown-course')));
    }

    private function buildQuestionBankPresentation($questionBankRows): array
    {
        $collection = collect($questionBankRows);

        $groupedCourses = $collection
            ->groupBy(fn ($row) => $this->formatQuestionBankCourseLabel($row).'||'.$row->course_slug)
            ->map(function ($courseRows, $courseKey) {
                [$courseLabel, $courseSlug] = array_pad(explode('||', (string) $courseKey, 2), 2, '');

                $chapterGroups = $courseRows
                    ->groupBy(fn ($row) => $row->placement_after_chapter ? 'chapter_'.$row->placement_after_chapter : 'general')
                    ->sortKeysUsing(function ($left, $right) {
                        if ($left === 'general') {
                            return -1;
                        }

                        if ($right === 'general') {
                            return 1;
                        }

                        return (int) str_replace('chapter_', '', (string) $left) <=> (int) str_replace('chapter_', '', (string) $right);
                    })
                    ->map(function ($chapterRows, $chapterKey) {
                        return [
                            'key' => $chapterKey,
                            'label' => $chapterKey === 'general'
                                ? 'General Bank'
                                : 'Chapter '.(int) str_replace('chapter_', '', (string) $chapterKey),
                            'count' => $chapterRows->count(),
                            'rows' => $chapterRows->values(),
                        ];
                    })
                    ->values();

                return [
                    'course_label' => $courseLabel,
                    'course_slug' => $courseSlug,
                    'count' => $courseRows->count(),
                    'pop_quiz_count' => $courseRows->where('is_pop_quiz', true)->count(),
                    'chapters' => $chapterGroups,
                ];
            })
            ->values();

        return [
            'summary' => [
                'total' => $collection->count(),
                'pop_quiz' => $collection->where('is_pop_quiz', true)->count(),
                'ai' => $collection->where('question_origin', 'ai')->count(),
                'manual' => $collection->where('question_origin', 'manual')->count(),
            ],
            'courses' => $groupedCourses,
        ];
    }

    private function buildManagedCourseMeta($courses, ?object $frontendCraftContent, bool $canManageFrontendCraft, int $dosenId): array
    {
        $courseMeta = [];

        foreach ($courses as $course) {
            $courseMeta['quiz-'.$course->id] = [
                'title' => $course->title,
                'dosen_id' => $dosenId,
            ];
        }

        if ($canManageFrontendCraft) {
            $courseMeta['frontend-craft'] = [
                'title' => $frontendCraftContent->hero_title ?? 'Frontend Craft',
                'dosen_id' => $dosenId,
            ];
        }

        if (empty($courseMeta)) {
            return [];
        }

        $chapterCounts = DB::table('course_lessons')
            ->whereIn('course_slug', array_keys($courseMeta))
            ->select('course_slug', DB::raw('MAX(chapter_number) as max_chapter'))
            ->groupBy('course_slug')
            ->pluck('max_chapter', 'course_slug');

        foreach ($courseMeta as $slug => $meta) {
            $courseMeta[$slug]['chapter_count'] = max(8, (int) ($chapterCounts[$slug] ?? 0));
        }

        return $courseMeta;
    }

    private function buildDosenProgressAnalytics(array $courseMeta, int $dosenId): array
    {
        if (empty($courseMeta)) {
            return [
                'overview' => [
                    'active_learners' => 0,
                    'consistent_rate' => 0,
                    'avg_progress' => 0,
                    'attendance_rate' => 0,
                    'pop_quiz_mastery' => 0,
                    'qa_answer_rate' => 0,
                ],
                'weekly_rows' => [],
                'course_health_rows' => [],
                'student_focus_rows' => [],
            ];
        }

        $slugs = array_keys($courseMeta);
        $states = DB::table('user_course_states')
            ->whereIn('course_slug', $slugs)
            ->whereRaw('"is_enrolled" IS TRUE')
            ->get(['user_id', 'course_slug', 'consistent_mode_enabled']);

        $progressRows = DB::table('user_chapter_progress')
            ->whereIn('course_slug', $slugs)
            ->whereNotNull('completed_at')
            ->select('user_id', 'course_slug', DB::raw('COUNT(*) as completed_count'), DB::raw('MAX(completed_at) as last_completed_at'))
            ->groupBy('user_id', 'course_slug')
            ->get();

        $attendanceRows = DB::table('course_attendance_logs')
            ->whereIn('course_slug', $slugs)
            ->get(['user_id', 'course_slug', 'attendance_date', 'target_chapters', 'chapters_completed', 'is_attended', 'updated_at']);

        $popQuizRows = DB::table('user_pop_quiz_progress')
            ->whereIn('course_slug', $slugs)
            ->get(['user_id', 'course_slug', 'passed_at']);

        $qaRows = DB::table('course_questions')
            ->where('dosen_id', $dosenId)
            ->whereIn('course_slug', $slugs)
            ->get(['user_id', 'course_slug', 'answer_text']);

        $recentProgressRows = DB::table('user_chapter_progress')
            ->whereIn('course_slug', $slugs)
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', '>=', now()->copy()->subDays(6)->toDateString())
            ->get(['course_slug', 'completed_at']);

        $userIds = $states->pluck('user_id')
            ->merge($progressRows->pluck('user_id'))
            ->merge($attendanceRows->pluck('user_id'))
            ->merge($qaRows->pluck('user_id'))
            ->filter()
            ->unique()
            ->values();

        $userNames = \App\Models\User::query()
            ->whereIn('id', $userIds)
            ->pluck('name', 'id');

        $progressMap = $progressRows->keyBy(fn ($row) => $row->course_slug.'|'.$row->user_id);
        $attendanceMap = $attendanceRows->groupBy(fn ($row) => $row->course_slug.'|'.$row->user_id);
        $attendanceBySlug = $attendanceRows->groupBy('course_slug');
        $popQuizBySlug = $popQuizRows->groupBy('course_slug');
        $qaBySlug = $qaRows->groupBy('course_slug');

        $enrollmentAnalytics = $states->map(function ($state) use ($courseMeta, $progressMap, $attendanceMap, $userNames) {
            $key = $state->course_slug.'|'.$state->user_id;
            $course = $courseMeta[$state->course_slug];
            $progressRow = $progressMap->get($key);
            $completedCount = (int) ($progressRow->completed_count ?? 0);
            $chapterCount = max(1, (int) ($course['chapter_count'] ?? 1));
            $progressPercent = (int) round(min(100, ($completedCount / $chapterCount) * 100));
            $attendanceItems = collect($attendanceMap->get($key, collect()));
            $attendanceTotal = $attendanceItems->count();
            $attendanceCounted = $attendanceItems->where('is_attended', true)->count();
            $attendanceRate = $attendanceTotal > 0 ? (int) round(($attendanceCounted / $attendanceTotal) * 100) : 0;
            $lastAttendanceAt = $attendanceItems->sortByDesc('updated_at')->first()->updated_at ?? null;
            $lastCompletedAt = $progressRow->last_completed_at ?? null;
            $lastActivity = collect([$lastCompletedAt, $lastAttendanceAt])->filter()->sortDesc()->first();

            $status = 'On Track';
            if ($progressPercent >= 80 && $attendanceRate >= 70) {
                $status = 'Strong';
            } elseif ($progressPercent < 35 || ($attendanceTotal > 0 && $attendanceRate < 50)) {
                $status = 'Needs Attention';
            }

            return [
                'user_id' => (int) $state->user_id,
                'student_name' => $userNames[$state->user_id] ?? 'Student',
                'course_slug' => $state->course_slug,
                'course_title' => $course['title'],
                'progress_percent' => $progressPercent,
                'attendance_rate' => $attendanceRate,
                'consistent_mode_enabled' => (bool) $state->consistent_mode_enabled,
                'last_activity' => $lastActivity,
                'status' => $status,
            ];
        })->values();

        $attendanceCountedTotal = $attendanceRows->where('is_attended', true)->count();
        $attendanceRateTotal = $attendanceRows->count() > 0
            ? (int) round(($attendanceCountedTotal / $attendanceRows->count()) * 100)
            : 0;
        $popQuizPassed = $popQuizRows->filter(fn ($row) => !empty($row->passed_at))->count();
        $popQuizMastery = $popQuizRows->count() > 0
            ? (int) round(($popQuizPassed / $popQuizRows->count()) * 100)
            : 0;
        $qaAnswered = $qaRows->filter(fn ($row) => filled(trim((string) $row->answer_text)))->count();
        $qaAnswerRate = $qaRows->count() > 0
            ? (int) round(($qaAnswered / $qaRows->count()) * 100)
            : 0;
        $consistentRate = $states->count() > 0
            ? (int) round(($states->where('consistent_mode_enabled', true)->count() / $states->count()) * 100)
            : 0;

        $courseHealthRows = collect($courseMeta)->map(function ($meta, $slug) use ($enrollmentAnalytics, $attendanceBySlug, $popQuizBySlug, $qaBySlug) {
            $courseEnrollments = $enrollmentAnalytics->where('course_slug', $slug);
            $attendanceItems = collect($attendanceBySlug->get($slug, collect()));
            $popItems = collect($popQuizBySlug->get($slug, collect()));
            $qaItems = collect($qaBySlug->get($slug, collect()));
            $attendanceRate = $attendanceItems->count() > 0
                ? (int) round(($attendanceItems->where('is_attended', true)->count() / $attendanceItems->count()) * 100)
                : 0;
            $popMastery = $popItems->count() > 0
                ? (int) round(($popItems->filter(fn ($row) => !empty($row->passed_at))->count() / $popItems->count()) * 100)
                : 0;

            return [
                'course_title' => $meta['title'],
                'enrolled_students' => $courseEnrollments->pluck('user_id')->unique()->count(),
                'consistent_users' => $courseEnrollments->where('consistent_mode_enabled', true)->count(),
                'avg_progress' => (int) round($courseEnrollments->avg('progress_percent') ?? 0),
                'attendance_rate' => $attendanceRate,
                'pop_quiz_mastery' => $popMastery,
                'open_questions' => $qaItems->filter(fn ($row) => blank(trim((string) $row->answer_text)))->count(),
            ];
        })->values();

        $dateKeys = collect(range(6, 0))->map(fn ($offset) => now()->copy()->subDays($offset)->toDateString());
        $completionCounts = $recentProgressRows->groupBy(fn ($row) => substr((string) $row->completed_at, 0, 10))
            ->map(fn ($rows) => $rows->count());
        $attendanceCounts = $attendanceRows
            ->where('is_attended', true)
            ->where('attendance_date', '>=', now()->copy()->subDays(6)->toDateString())
            ->groupBy('attendance_date')
            ->map(fn ($rows) => $rows->count());
        $trendMax = max(1, (int) max(
            $completionCounts->max() ?? 0,
            $attendanceCounts->max() ?? 0
        ));

        $weeklyRows = $dateKeys->map(function ($date) use ($completionCounts, $attendanceCounts, $trendMax) {
            $completions = (int) ($completionCounts[$date] ?? 0);
            $attendance = (int) ($attendanceCounts[$date] ?? 0);

            return [
                'label' => \Illuminate\Support\Carbon::parse($date)->format('d M'),
                'completions' => $completions,
                'attendance' => $attendance,
                'width' => (int) round((max($completions, $attendance) / $trendMax) * 100),
            ];
        })->values();

        $studentFocusRows = $enrollmentAnalytics
            ->sortBy(fn ($row) => [
                $row['status'] === 'Needs Attention' ? 0 : ($row['status'] === 'On Track' ? 1 : 2),
                $row['progress_percent'],
                $row['attendance_rate'],
                strtotime((string) ($row['last_activity'] ?? '1970-01-01')),
            ])
            ->take(8)
            ->values()
            ->map(fn ($row) => [
                'student_name' => $row['student_name'],
                'course_title' => $row['course_title'],
                'progress_percent' => $row['progress_percent'],
                'attendance_rate' => $row['attendance_rate'],
                'last_activity' => $row['last_activity'],
                'status' => $row['status'],
            ]);

        return [
            'overview' => [
                'active_learners' => $states->pluck('user_id')->unique()->count(),
                'consistent_rate' => $consistentRate,
                'avg_progress' => (int) round($enrollmentAnalytics->avg('progress_percent') ?? 0),
                'attendance_rate' => $attendanceRateTotal,
                'pop_quiz_mastery' => $popQuizMastery,
                'qa_answer_rate' => $qaAnswerRate,
            ],
            'weekly_rows' => $weeklyRows,
            'course_health_rows' => $courseHealthRows,
            'student_focus_rows' => $studentFocusRows,
        ];
    }

    private function forgetDashboardCaches(?int $dosenId = null): void
    {
        if ($dosenId !== null) {
            Cache::forget($this->dashboardCacheKey($dosenId));
            return;
        }

        Cache::forget($this->dashboardCacheKey(auth()->id()));
    }

    private function forgetCourseCaches(?int $quizId = null, ?string $slug = null): void
    {
        $this->forgetDashboardCaches();
        Cache::forget('landing:courses');
        Cache::forget('landing:stats');
        Cache::forget('student_course:carousel_courses:all');
        Cache::forget('student_course:mentors:all');

        if ($quizId !== null) {
            Cache::forget('student_course:quiz_record:'.$quizId);
            Cache::forget('student_course:quiz_course_info:'.$quizId);
        }

        if ($slug !== null) {
            Cache::forget('student_course:course_lessons:'.$slug);
            Cache::forget('student_course:course_page_content:'.$slug);
        }
    }

    private function databaseBoolean(bool $value): mixed
    {
        if (DB::getDriverName() === 'pgsql') {
            return DB::raw($value ? 'TRUE' : 'FALSE');
        }

        return $value;
    }

    private function courseContextFromKey(string $courseKey): array
    {
        if ($courseKey === 'frontend-craft') {
            $content = DB::table('course_page_contents')
                ->where('course_slug', 'frontend-craft')
                ->where('updated_by', auth()->id())
                ->first(['hero_title']);

            abort_if(!$content, 404);

            return [
                'course_key' => 'frontend-craft',
                'course_slug' => 'frontend-craft',
                'quiz_id' => null,
                'course_title' => $content->hero_title ?? 'Frontend Craft',
                'course_category' => 'Web Development',
                'chapter_count' => max(
                    8,
                    (int) (DB::table('course_lessons')->where('course_slug', 'frontend-craft')->max('chapter_number') ?? 0)
                ),
            ];
        }

        $quiz = DB::table('quizzes')
            ->where('id', (int) $courseKey)
            ->where('created_by', auth()->id())
            ->first(['id', 'title', 'category']);

        abort_if(!$quiz, 404);

        return [
            'course_key' => (string) $quiz->id,
            'course_slug' => 'quiz-'.$quiz->id,
            'quiz_id' => (int) $quiz->id,
            'course_title' => $quiz->title,
            'course_category' => $quiz->category,
            'chapter_count' => max(
                8,
                (int) (DB::table('course_lessons')->where('course_slug', 'quiz-'.$quiz->id)->max('chapter_number') ?? 0)
            ),
        ];
    }

    private function validateAiQuestionRequest(Request $request): array
    {
        $validated = $request->validate([
            'course_key' => ['required', 'string', 'max:120'],
            'generation_notes' => ['required', 'string', 'max:4000'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'question_count' => ['required', 'integer', 'min:1', 'max:10'],
            'question_type_mode' => ['required', 'in:mcq,essay,true_false,mixed_mcq_essay,mixed_all'],
            'placement_after_chapter' => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);

        $context = $this->courseContextFromKey($validated['course_key']);
        if (!empty($validated['placement_after_chapter']) && $validated['placement_after_chapter'] > $context['chapter_count']) {
            throw ValidationException::withMessages([
                'placement_after_chapter' => 'Posisi chapter melebihi jumlah chapter course.',
            ]);
        }

        $isPopQuiz = !empty($validated['placement_after_chapter']);

        return array_merge($validated, $context, [
            'is_pop_quiz' => $isPopQuiz,
        ]);
    }

    private function insertGeneratedQuestions(array $preview): int
    {
        $databaseIsPopQuiz = $this->databaseBoolean((bool) $preview['is_pop_quiz']);
        $rows = [];
        foreach ($preview['questions'] as $question) {
            $rows[] = [
                'quiz_id' => $preview['quiz_id'],
                'course_slug' => $preview['course_slug'],
                'question_text' => $question['question_text'],
                'question_type' => $question['question_type'],
                'category' => $preview['course_category'],
                'difficulty' => $question['difficulty'],
                'correct_answer' => $question['correct_answer'] !== '' ? $question['correct_answer'] : null,
                'options_json' => $question['options_json'],
                'placement_after_chapter' => $preview['placement_after_chapter'] ?: null,
                'is_pop_quiz' => $databaseIsPopQuiz,
                'requires_perfect_score' => $databaseIsPopQuiz,
                'question_origin' => 'ai',
                'generation_notes' => $preview['generation_notes'],
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('question_bank')->insert($rows);

        if (!empty($preview['usage'])) {
            DB::table('ai_usage_logs')->insert([
                'provider' => $preview['usage']['provider'],
                'model' => $preview['usage']['model'],
                'token_count' => (int) ($preview['usage']['token_count'] ?? 0),
                'cost_usd' => 0,
                'logged_at' => now(),
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return count($rows);
    }

    public function dashboard()
    {
        $dosenId = auth()->id();
        $dashboardData = Cache::remember($this->dashboardCacheKey($dosenId), self::DASHBOARD_CACHE_TTL_SECONDS, function () use ($dosenId) {
            $stats = [
                'courses' => (int) DB::table('quizzes')->where('created_by', $dosenId)->count(),
                'questions' => (int) DB::table('question_bank')->where('created_by', $dosenId)->count(),
                'submissions' => (int) DB::table('quiz_submissions as s')
                    ->join('quizzes as q', 'q.id', '=', 's.quiz_id')
                    ->where('q.created_by', $dosenId)
                    ->count(),
            ];

            $avgScore = DB::table('quiz_submissions as s')
                ->join('quizzes as q', 'q.id', '=', 's.quiz_id')
                ->where('q.created_by', $dosenId)
                ->selectRaw('AVG(COALESCE(s.manual_score, s.score)) as avg_score')
                ->first();

            $courses = DB::table('quizzes')
                ->where('created_by', $dosenId)
                ->latest()
                ->limit(10)
                ->get();

            $courseInfosByQuizId = DB::table('quiz_course_infos')
                ->whereIn('quiz_id', $courses->pluck('id'))
                ->get()
                ->keyBy('quiz_id');

            $courseInfoData = [];
            foreach ($courses as $course) {
                $info = $courseInfosByQuizId->get($course->id);

                $courseInfoData[$course->id] = [
                    'mode' => 'quiz',
                    'hero_title' => $info->hero_title ?? '',
                    'hero_background_url' => $info->hero_background_url ?? '',
                    'tagline' => $info->tagline ?? '',
                    'instructor_name' => $info->instructor_name ?? '',
                    'instructor_photo_url' => $info->instructor_photo_url ?? '',
                    'about' => $info->about ?? '',
                    'target_audience' => $info->target_audience ?? '',
                    'duration_text' => $info->duration_text ?? '',
                    'syllabus_lines' => $this->buildSyllabusLines($info->syllabus_json ?? null),
                    'learning_outcomes' => $info->learning_outcomes ?? '',
                    'trailer_url' => $info->trailer_url ?? '',
                    'trailer_poster_url' => $info->trailer_poster_url ?? '',
                ];
            }

            $courseInfoRows = DB::table('quiz_course_infos as i')
                ->join('quizzes as q', 'q.id', '=', 'i.quiz_id')
                ->where('q.created_by', $dosenId)
                ->select('i.quiz_id', 'q.title', 'i.tagline', 'i.target_audience', 'i.updated_at')
                ->orderByDesc('i.updated_at')
                ->limit(10)
                ->get();

            $submissions = DB::table('quiz_submissions as s')
                ->join('quizzes as q', 'q.id', '=', 's.quiz_id')
                ->leftJoin('users as u', 'u.id', '=', 's.user_id')
                ->where('q.created_by', $dosenId)
                ->select(
                    's.id',
                    'q.title as course_title',
                    'u.name as student_name',
                    's.score',
                    's.manual_score',
                    's.status',
                    's.submitted_at'
                )
                ->orderByDesc('s.submitted_at')
                ->limit(12)
                ->get();

            $questionBankRows = DB::table('question_bank as qb')
                ->leftJoin('quizzes as q', 'q.id', '=', 'qb.quiz_id')
                ->where('qb.created_by', $dosenId)
                ->select(
                    'qb.id',
                    'qb.question_text',
                    'qb.question_type',
                    'qb.difficulty',
                    'qb.question_origin',
                    'qb.placement_after_chapter',
                    'qb.is_pop_quiz',
                    'qb.course_slug',
                    'q.title as quiz_title',
                    'qb.created_at'
                )
                ->orderByDesc('qb.created_at')
                ->limit(30)
                ->get();
            $questionBankPresentation = $this->buildQuestionBankPresentation($questionBankRows);

            $analytics = DB::table('quiz_submissions as s')
                ->join('quizzes as q', 'q.id', '=', 's.quiz_id')
                ->where('q.created_by', $dosenId)
                ->select('q.category', DB::raw('COUNT(*) as total'))
                ->groupBy('q.category')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            $qaInbox = DB::table('course_questions as cq')
                ->leftJoin('users as u', 'u.id', '=', 'cq.user_id')
                ->where('cq.dosen_id', $dosenId)
                ->select(
                    'cq.id',
                    'cq.course_title',
                    'cq.course_slug',
                    'cq.chapter_number',
                    'cq.question_text',
                    'cq.answer_text',
                    'cq.created_at',
                    'u.name as student_name'
                )
                ->orderByDesc('cq.created_at')
                ->limit(40)
                ->get();

            $attendanceStatsRow = DB::table('course_attendance_logs')
                ->where('dosen_id', $dosenId)
                ->selectRaw('COUNT(*) as total_sessions, SUM(CASE WHEN is_attended THEN 1 ELSE 0 END) as attended_sessions')
                ->first();

            $attendanceStats = [
                'total_sessions' => (int) ($attendanceStatsRow->total_sessions ?? 0),
                'attended_sessions' => (int) ($attendanceStatsRow->attended_sessions ?? 0),
                'students_in_mode' => (int) DB::table('course_attendance_logs')
                    ->where('dosen_id', $dosenId)
                    ->distinct('user_id')
                    ->count('user_id'),
            ];

            $attendanceRecords = DB::table('course_attendance_logs as a')
                ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                ->where('a.dosen_id', $dosenId)
                ->select(
                    'a.course_title',
                    'a.attendance_date',
                    'a.target_chapters',
                    'a.chapters_completed',
                    'a.is_attended',
                    'u.name as student_name'
                )
                ->orderByDesc('a.attendance_date')
                ->orderByDesc('a.updated_at')
                ->limit(18)
                ->get();

            $frontendCraftContent = DB::table('course_page_contents')->where('course_slug', 'frontend-craft')->first();
            $frontendCraftSyllabusText = $this->buildSyllabusLines($frontendCraftContent?->syllabus_json);

            $frontendCraftFallbackSyllabus = [
                ['title' => 'Module 1: Frontend Fundamentals', 'description' => 'Setup lingkungan kerja, HTML semantic, CSS modern basics, dan mindset frontend developer.'],
                ['title' => 'Module 2: Layout & Responsive Design', 'description' => 'Flexbox, CSS Grid, responsive breakpoints, serta mobile-first strategy untuk UI yang rapi.'],
                ['title' => 'Module 3: JavaScript Interactivity', 'description' => 'DOM manipulation, event handling, state sederhana, dan best practice UI interaction.'],
                ['title' => 'Module 4: Project Build & Deployment', 'description' => 'Bangun project portfolio nyata dan deploy ke hosting agar bisa dipamerkan ke recruiter/client.'],
            ];
            $frontendCraftFallbackSyllabusText = collect($frontendCraftFallbackSyllabus)
                ->map(fn ($row) => $row['title'].'|'.$row['description'])
                ->implode("\n");
            $frontendCraftFallbackOutcomes = implode("\n", [
                'Kamu akan bisa membuat landing page dan dashboard frontend sendiri.',
                'Kamu memahami cara membangun UI yang responsive dan reusable.',
                'Kamu mampu mengubah design menjadi implementasi web yang rapi.',
                'Kamu punya 1 project portfolio frontend siap publish.',
            ]);

            $courseInfoData['frontend-craft'] = [
                'mode' => 'frontend-craft',
                'hero_title' => $frontendCraftContent->hero_title ?? 'Frontend Craft',
                'hero_background_url' => $frontendCraftContent->hero_background_url ?? '',
                'tagline' => $frontendCraftContent->tagline ?? 'Kuasai seni web development modern dalam 30 hari.',
                'instructor_name' => $frontendCraftContent->instructor_name ?? 'Raka Pradana',
                'instructor_photo_url' => $frontendCraftContent->instructor_photo_url ?? 'https://images.unsplash.com/photo-1517180102446-f3ece451e9d8?auto=format&fit=crop&w=1400&q=80',
                'about' => $frontendCraftContent->about ?? 'Course ini dirancang untuk membantu kamu yang bingung memulai web development. Setelah menyelesaikan materi, kamu akan mampu membangun website interaktif, responsive, dan siap deploy dengan workflow profesional.',
                'target_audience' => $frontendCraftContent->target_audience ?? 'Pemula sampai Intermediate',
                'duration_text' => $frontendCraftContent->duration_text ?? 'Total Durasi: 18 Jam Video - 42 Materi',
                'syllabus_lines' => $frontendCraftSyllabusText !== '' ? $frontendCraftSyllabusText : $frontendCraftFallbackSyllabusText,
                'learning_outcomes' => $frontendCraftContent->outcomes_text ?? $frontendCraftFallbackOutcomes,
                'trailer_url' => $frontendCraftContent->trailer_url ?? 'https://cdn.coverr.co/videos/coverr-programming-workflow-1579/1080p.mp4',
                'trailer_poster_url' => $frontendCraftContent->trailer_poster_url ?? 'https://images.unsplash.com/photo-1517180102446-f3ece451e9d8?auto=format&fit=crop&w=1400&q=80',
            ];

            $canManageFrontendCraft = (($frontendCraftContent->updated_by ?? null) === $dosenId);
            $managedCourseMeta = $this->buildManagedCourseMeta($courses, $frontendCraftContent, $canManageFrontendCraft, $dosenId);
            $progressAnalytics = $this->buildDosenProgressAnalytics($managedCourseMeta, $dosenId);

            return compact(
                'stats',
                'avgScore',
                'courses',
                'courseInfoRows',
                'submissions',
                'questionBankRows',
                'questionBankPresentation',
                'analytics',
                'qaInbox',
                'attendanceStats',
                'attendanceRecords',
                'frontendCraftContent',
                'frontendCraftSyllabusText',
                'courseInfoData',
                'canManageFrontendCraft',
                'progressAnalytics'
            );
        });

        extract($dashboardData);

        $manageableCourses = $courses->map(fn ($course) => (object) [
                'key' => (string) $course->id,
                'quiz_id' => $course->id,
                'title' => $course->title,
                'category' => $course->category,
                'difficulty' => $course->difficulty,
                'owner_name' => auth()->user()->name,
            ]);

        if ($canManageFrontendCraft) {
            $manageableCourses->prepend((object) [
                'key' => 'frontend-craft',
                'quiz_id' => null,
                'title' => 'Frontend Craft',
                'category' => 'Web Development',
                'difficulty' => 'core',
                'owner_name' => auth()->user()->name,
            ]);
        }

        $progressOverview = $progressAnalytics['overview'] ?? [];
        $progressWeeklyRows = $progressAnalytics['weekly_rows'] ?? [];
        $progressCourseHealthRows = $progressAnalytics['course_health_rows'] ?? [];
        $progressStudentFocusRows = $progressAnalytics['student_focus_rows'] ?? [];

        return view('dosen.dashboard', compact('stats', 'avgScore', 'courses', 'submissions', 'questionBankRows', 'questionBankPresentation', 'analytics', 'qaInbox', 'attendanceStats', 'attendanceRecords', 'courseInfoRows', 'frontendCraftContent', 'frontendCraftSyllabusText', 'courseInfoData', 'manageableCourses', 'progressAnalytics', 'progressOverview', 'progressWeeklyRows', 'progressCourseHealthRows', 'progressStudentFocusRows'));
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:120'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
        ]);

        DB::table('quizzes')->insert([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'difficulty' => $validated['difficulty'],
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->forgetCourseCaches();

        return back()->with('success', 'Course berhasil dibuat.');
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_key' => ['required', 'string', 'max:120'],
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:mcq,essay,true_false'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'correct_answer' => ['nullable', 'string'],
            'options_json' => ['nullable', 'string'],
            'placement_after_chapter' => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);

        $context = $this->courseContextFromKey($validated['course_key']);
        $isPopQuiz = !empty($validated['placement_after_chapter']);
        $databaseIsPopQuiz = $this->databaseBoolean($isPopQuiz);

        DB::table('question_bank')->insert([
            'quiz_id' => $context['quiz_id'],
            'course_slug' => $context['course_slug'],
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'difficulty' => $validated['difficulty'],
            'category' => $context['course_category'],
            'correct_answer' => $validated['correct_answer'] ?? null,
            'options_json' => $validated['options_json'] ?? null,
            'placement_after_chapter' => $validated['placement_after_chapter'] ?? null,
            'is_pop_quiz' => $databaseIsPopQuiz,
            'requires_perfect_score' => $databaseIsPopQuiz,
            'question_origin' => 'manual',
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->forgetDashboardCaches();

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function deleteQuestion(int $question): RedirectResponse
    {
        $row = DB::table('question_bank')
            ->where('id', $question)
            ->where('created_by', auth()->id())
            ->first(['id', 'quiz_id', 'course_slug']);

        abort_if(!$row, 404);

        DB::table('question_bank')->where('id', $question)->delete();

        $this->forgetCourseCaches($row->quiz_id ? (int) $row->quiz_id : null, $row->course_slug);

        return back()->with('success', 'Soal berhasil dihapus dari question bank.');
    }

    public function previewAiQuestions(Request $request, AiQuestionGenerator $generator): RedirectResponse
    {
        $payload = $this->validateAiQuestionRequest($request);

        try {
            $generated = $generator->generate($payload);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['ai_preview' => $exception->getMessage()])->withInput();
        }

        session([
            self::AI_PREVIEW_SESSION_KEY => array_merge($payload, [
                'preview_batch' => (string) Str::uuid(),
                'questions' => $generated['questions'],
                'usage' => $generated['usage'],
            ]),
        ]);

        return back()->with('success', 'Preview soal AI berhasil dibuat.');
    }

    public function saveAiQuestions(Request $request): RedirectResponse
    {
        $preview = session(self::AI_PREVIEW_SESSION_KEY);
        if (!is_array($preview) || empty($preview['questions'])) {
            return back()->withErrors(['ai_preview' => 'Preview soal AI belum tersedia.']);
        }

        $savedCount = $this->insertGeneratedQuestions($preview);
        session()->forget(self::AI_PREVIEW_SESSION_KEY);
        $this->forgetDashboardCaches();

        return back()->with('success', $savedCount.' soal AI berhasil disimpan ke question bank.');
    }

    public function answerCourseQuestion(Request $request, int $question): RedirectResponse
    {
        $validated = $request->validate([
            'answer_text' => ['required', 'string', 'max:3000'],
        ]);

        $ownedQuestion = DB::table('course_questions')
            ->where('id', $question)
            ->where('dosen_id', auth()->id())
            ->first(['id', 'course_slug']);

        abort_if(!$ownedQuestion, 404);

        DB::table('course_questions')
            ->where('id', $question)
            ->update([
                'answer_text' => $validated['answer_text'],
                'updated_at' => now(),
            ]);

        $this->forgetCourseCaches(null, $ownedQuestion->course_slug ?? null);

        return back()->with('success', 'Jawaban Q&A berhasil disimpan.');
    }

    public function exportScores(): StreamedResponse
    {
        $rows = DB::table('quiz_submissions as s')
            ->join('quizzes as q', 'q.id', '=', 's.quiz_id')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->where('q.created_by', auth()->id())
            ->select(
                'q.title as course_title',
                'u.name as student_name',
                's.score',
                's.manual_score',
                's.status',
                's.submitted_at'
            )
            ->orderByDesc('s.submitted_at')
            ->get();

        $fileName = 'dosen_scores_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['course_title', 'student_name', 'score', 'manual_score', 'status', 'submitted_at']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->course_title,
                    $row->student_name,
                    $row->score,
                    $row->manual_score,
                    $row->status,
                    $row->submitted_at,
                ]);
            }

            fclose($out);
        }, $fileName);
    }

    public function updateCourseInfo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quiz_id' => [
                'required',
                Rule::exists('quizzes', 'id')->where(function ($query) {
                    $query->where('created_by', auth()->id());
                }),
            ],
            'hero_title' => ['nullable', 'string', 'max:150'],
            'hero_background_url' => ['nullable', 'url', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:5000'],
            'target_audience' => ['nullable', 'string', 'max:120'],
            'duration_text' => ['nullable', 'string', 'max:120'],
            'syllabus_lines' => ['nullable', 'string', 'max:8000'],
            'learning_outcomes' => ['nullable', 'string', 'max:5000'],
            'trailer_url' => ['nullable', 'url', 'max:255'],
            'trailer_poster_url' => ['nullable', 'url', 'max:255'],
            'instructor_name' => ['nullable', 'string', 'max:120'],
            'instructor_photo_url' => ['nullable', 'url', 'max:255'],
            'trailer_file' => ['nullable', 'file', 'mimes:mp4,mov,m4v,webm,avi', 'max:512000'],
            'trailer_poster_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'instructor_photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hero_background_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $existing = DB::table('quiz_course_infos')
            ->where('quiz_id', $validated['quiz_id'])
            ->first(['hero_background_url', 'trailer_url', 'trailer_poster_url', 'instructor_photo_url']);

        $syllabus = [];
        if (!empty($validated['syllabus_lines'])) {
            $lines = preg_split('/\r\n|\r|\n/', trim($validated['syllabus_lines']));
            foreach ($lines as $line) {
                if (trim($line) === '') {
                    continue;
                }
                [$title, $desc] = array_pad(explode('|', $line, 2), 2, '');
                $syllabus[] = [
                    'title' => trim($title),
                    'description' => trim($desc),
                ];
            }
        }

        $heroBackgroundUrl = $validated['hero_background_url'] ?? null;
        if ($request->hasFile('hero_background_file')) {
            $this->deleteStoredMediaFromValue($existing?->hero_background_url);
            $heroBackgroundUrl = $this->storeMediaAsPublicUrl($request->file('hero_background_file'), 'course_media/hero_backgrounds');
        }

        $trailerUrl = $validated['trailer_url'] ?? null;
        if ($request->hasFile('trailer_file')) {
            $this->deleteStoredMediaFromValue($existing?->trailer_url);
            $trailerUrl = $this->storeMediaAsPublicUrl($request->file('trailer_file'), 'course_media/trailers');
        }

        $trailerPosterUrl = $validated['trailer_poster_url'] ?? null;
        if ($request->hasFile('trailer_poster_file')) {
            $this->deleteStoredMediaFromValue($existing?->trailer_poster_url);
            $trailerPosterUrl = $this->storeMediaAsPublicUrl($request->file('trailer_poster_file'), 'course_media/posters');
        }

        $instructorPhotoUrl = $validated['instructor_photo_url'] ?? null;
        if ($request->hasFile('instructor_photo_file')) {
            $this->deleteStoredMediaFromValue($existing?->instructor_photo_url);
            $instructorPhotoUrl = $this->storeMediaAsPublicUrl($request->file('instructor_photo_file'), 'course_media/instructors');
        }

        DB::table('quiz_course_infos')->updateOrInsert(
            ['quiz_id' => $validated['quiz_id']],
            [
                'hero_title' => $validated['hero_title'] ?? null,
                'hero_background_url' => $heroBackgroundUrl,
                'tagline' => $validated['tagline'] ?? null,
                'about' => $validated['about'] ?? null,
                'target_audience' => $validated['target_audience'] ?? null,
                'duration_text' => $validated['duration_text'] ?? null,
                'syllabus_json' => !empty($syllabus) ? json_encode($syllabus) : null,
                'learning_outcomes' => $validated['learning_outcomes'] ?? null,
                'trailer_url' => $trailerUrl,
                'trailer_poster_url' => $trailerPosterUrl,
                'instructor_name' => $validated['instructor_name'] ?? null,
                'instructor_photo_url' => $instructorPhotoUrl,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->forgetCourseCaches((int) $validated['quiz_id'], 'quiz-'.$validated['quiz_id']);

        return back()->with('success', 'Course info berhasil diperbarui.');
    }

    public function updateFrontendCraftPage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_title' => ['nullable', 'string', 'max:150'],
            'hero_background_url' => ['nullable', 'url', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:5000'],
            'target_audience' => ['nullable', 'string', 'max:120'],
            'duration_text' => ['nullable', 'string', 'max:120'],
            'syllabus_lines' => ['nullable', 'string', 'max:8000'],
            'outcomes_text' => ['nullable', 'string', 'max:5000'],
            'trailer_url' => ['nullable', 'url', 'max:255'],
            'trailer_poster_url' => ['nullable', 'url', 'max:255'],
            'instructor_name' => ['nullable', 'string', 'max:120'],
            'instructor_photo_url' => ['nullable', 'url', 'max:255'],
            'trailer_file' => ['nullable', 'file', 'mimes:mp4,mov,m4v,webm,avi', 'max:512000'],
            'trailer_poster_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'instructor_photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hero_background_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $existing = DB::table('course_page_contents')
            ->where('course_slug', 'frontend-craft')
            ->first(['hero_background_url', 'trailer_url', 'trailer_poster_url', 'instructor_photo_url']);

        $syllabus = [];
        if (!empty($validated['syllabus_lines'])) {
            $lines = preg_split('/\r\n|\r|\n/', trim($validated['syllabus_lines']));
            foreach ($lines as $line) {
                if (trim($line) === '') {
                    continue;
                }
                [$title, $desc] = array_pad(explode('|', $line, 2), 2, '');
                $syllabus[] = [
                    'title' => trim($title),
                    'description' => trim($desc),
                ];
            }
        }

        $heroBackgroundUrl = $validated['hero_background_url'] ?? null;
        if ($request->hasFile('hero_background_file')) {
            $this->deleteStoredMediaFromValue($existing?->hero_background_url);
            $heroBackgroundUrl = $this->storeMediaAsPublicUrl($request->file('hero_background_file'), 'course_media/hero_backgrounds');
        }

        $trailerUrl = $validated['trailer_url'] ?? null;
        if ($request->hasFile('trailer_file')) {
            $this->deleteStoredMediaFromValue($existing?->trailer_url);
            $trailerUrl = $this->storeMediaAsPublicUrl($request->file('trailer_file'), 'course_media/trailers');
        }

        $trailerPosterUrl = $validated['trailer_poster_url'] ?? null;
        if ($request->hasFile('trailer_poster_file')) {
            $this->deleteStoredMediaFromValue($existing?->trailer_poster_url);
            $trailerPosterUrl = $this->storeMediaAsPublicUrl($request->file('trailer_poster_file'), 'course_media/posters');
        }

        $instructorPhotoUrl = $validated['instructor_photo_url'] ?? null;
        if ($request->hasFile('instructor_photo_file')) {
            $this->deleteStoredMediaFromValue($existing?->instructor_photo_url);
            $instructorPhotoUrl = $this->storeMediaAsPublicUrl($request->file('instructor_photo_file'), 'course_media/instructors');
        }

        DB::table('course_page_contents')->updateOrInsert(
            ['course_slug' => 'frontend-craft'],
            [
                'hero_title' => $validated['hero_title'] ?? null,
                'hero_background_url' => $heroBackgroundUrl,
                'tagline' => $validated['tagline'] ?? null,
                'about' => $validated['about'] ?? null,
                'target_audience' => $validated['target_audience'] ?? null,
                'duration_text' => $validated['duration_text'] ?? null,
                'syllabus_json' => !empty($syllabus) ? json_encode($syllabus) : null,
                'outcomes_text' => $validated['outcomes_text'] ?? null,
                'trailer_url' => $trailerUrl,
                'trailer_poster_url' => $trailerPosterUrl,
                'instructor_name' => $validated['instructor_name'] ?? null,
                'instructor_photo_url' => $instructorPhotoUrl,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->forgetCourseCaches(null, 'frontend-craft');

        return back()->with('success', 'Frontend Craft page content berhasil diperbarui.');
    }

    private function storeMediaAsPublicUrl(\Illuminate\Http\UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        return asset('storage/'.$path);
    }

    private function deleteStoredMediaFromValue(?string $value): void
    {
        if (empty($value)) {
            return;
        }

        $marker = '/storage/';
        $pos = strpos($value, $marker);
        if ($pos === false) {
            return;
        }

        $relative = substr($value, $pos + strlen($marker));
        if ($relative !== false && $relative !== '') {
            Storage::disk('public')->delete($relative);
        }
    }
}
