<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InstructorCourseController extends Controller
{
    private const DEFAULT_CHAPTERS = 8;
    private const MAX_CHAPTERS = 255;

    private function forgetCourseCaches(string $slug): void
    {
        Cache::forget('student_course:course_lessons:'.$slug);
        Cache::forget('student_course:course_page_content:'.$slug);
        Cache::forget('student_course:carousel_courses:all');
        Cache::forget('landing:courses');
    }

    private function ensureInstructor(): void
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'dosen'], true), 403);
    }

    private function chapterCountForCourse(string $slug): int
    {
        $maxChapter = (int) (DB::table('course_lessons')
            ->where('course_slug', $slug)
            ->max('chapter_number') ?? 0);

        $chapters = max(self::DEFAULT_CHAPTERS, $maxChapter);

        return min(self::MAX_CHAPTERS, $chapters);
    }

    private function roadmapTitleForCourse(string $slug, string $fallbackTitle): string
    {
        if ($slug === 'frontend-craft') {
            $title = DB::table('course_page_contents')
                ->where('course_slug', 'frontend-craft')
                ->value('hero_title');

            return !empty($title) ? $title : $fallbackTitle;
        }

        if (preg_match('/^quiz-(\d+)$/', $slug, $matches)) {
            $quizId = (int) $matches[1];
            $title = DB::table('quiz_course_infos')
                ->where('quiz_id', $quizId)
                ->value('hero_title');

            return !empty($title) ? $title : $fallbackTitle;
        }

        return $fallbackTitle;
    }

    private function catalog(string $slug): array
    {
        if ($slug === 'frontend-craft') {
            return [
                'title' => 'Frontend Craft',
                'chapters' => $this->chapterCountForCourse($slug),
                'slug' => $slug,
            ];
        }

        if (preg_match('/^quiz-(\d+)$/', $slug, $matches)) {
            $quizId = (int) $matches[1];
            $quiz = DB::table('quizzes')
                ->where('id', $quizId)
                ->first(['id', 'title', 'created_by']);

            abort_if(!$quiz, 404);

            if (auth()->user()->role === 'dosen' && (int) $quiz->created_by !== (int) auth()->id()) {
                abort(403);
            }

            return [
                'title' => $quiz->title,
                'chapters' => $this->chapterCountForCourse($slug),
                'slug' => $slug,
            ];
        }

        abort(404);
    }

    public function roadmap(string $course): View
    {
        $this->ensureInstructor();

        $meta = $this->catalog($course);
        $lessons = DB::table('course_lessons')
            ->where('course_slug', $course)
            ->get()
            ->keyBy('chapter_number');

        return view('instructor.course-roadmap', [
            'courseSlug' => $course,
            'courseTitle' => $meta['title'],
            'roadmapTitle' => $this->roadmapTitleForCourse($course, $meta['title']),
            'chaptersCount' => $meta['chapters'],
            'lessons' => $lessons,
        ]);
    }

    public function showLesson(string $course, int $chapter): View
    {
        $this->ensureInstructor();

        $meta = $this->catalog($course);
        abort_if($chapter < 1 || $chapter > $meta['chapters'], 404);

        $lesson = DB::table('course_lessons')
            ->where('course_slug', $course)
            ->where('chapter_number', $chapter)
            ->first();

        return view('instructor.course-lesson', [
            'courseSlug' => $course,
            'courseTitle' => $meta['title'],
            'chaptersCount' => $meta['chapters'],
            'chapter' => $chapter,
            'lesson' => $lesson,
        ]);
    }

    public function saveLesson(Request $request, string $course, int $chapter): RedirectResponse
    {
        $this->ensureInstructor();

        $meta = $this->catalog($course);
        abort_if($chapter < 1 || $chapter > $meta['chapters'], 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'video_source' => ['required', 'in:none,url,file'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,mov,m4v,avi,mkv', 'max:512000'],
            'circle_small_text' => ['nullable', 'string', 'max:30'],
            'circle_main_text' => ['nullable', 'string', 'max:20'],
        ]);

        if ($validated['video_source'] === 'url' && empty($validated['video_url'])) {
            return back()->withErrors(['video_url' => 'Isi URL video jika memilih sumber URL.'])->withInput();
        }

        if ($validated['video_source'] === 'file' && !$request->hasFile('video_file')) {
            return back()->withErrors(['video_file' => 'Upload file video jika memilih sumber file.'])->withInput();
        }

        $existing = DB::table('course_lessons')
            ->where('course_slug', $course)
            ->where('chapter_number', $chapter)
            ->first();

        $videoPath = $existing->video_path ?? null;
        $videoUrl = null;

        if ($validated['video_source'] === 'none') {
            if ($videoPath) {
                Storage::disk('public')->delete($videoPath);
            }
            $videoPath = null;
        } elseif ($validated['video_source'] === 'url') {
            if ($videoPath) {
                Storage::disk('public')->delete($videoPath);
            }
            $videoPath = null;
            $videoUrl = $validated['video_url'];
        } else {
            if ($videoPath) {
                Storage::disk('public')->delete($videoPath);
            }
            $videoPath = $request->file('video_file')->store('course_videos', 'public');
            $videoUrl = null;
        }

        DB::table('course_lessons')->updateOrInsert(
            [
                'course_slug' => $course,
                'chapter_number' => $chapter,
            ],
            [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'video_url' => $videoUrl,
                'video_path' => $videoPath,
                'circle_small_text' => !empty($validated['circle_small_text']) ? strtoupper($validated['circle_small_text']) : null,
                'circle_main_text' => $validated['circle_main_text'] ?? null,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => $existing ? $existing->created_at : now(),
            ]
        );

        $this->forgetCourseCaches($course);

        return back()->with('success', 'Lesson chapter berhasil diperbarui.');
    }

    public function addChapter(string $course): RedirectResponse
    {
        $this->ensureInstructor();

        $meta = $this->catalog($course);
        $currentMax = (int) (DB::table('course_lessons')
            ->where('course_slug', $course)
            ->max('chapter_number') ?? 0);

        $nextChapter = max(self::DEFAULT_CHAPTERS, $currentMax) + 1;

        if ($nextChapter > self::MAX_CHAPTERS) {
            return back()->withErrors(['chapter' => 'Maksimal chapter tercapai (255).']);
        }

        DB::table('course_lessons')->updateOrInsert(
            [
                'course_slug' => $course,
                'chapter_number' => $nextChapter,
            ],
            [
                'title' => 'Chapter '.$nextChapter,
                'description' => null,
                'video_url' => null,
                'video_path' => null,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->forgetCourseCaches($course);

        return redirect()->route('instructor.courses.lesson', ['course' => $course, 'chapter' => $nextChapter])
            ->with('success', $meta['title'].' chapter '.$nextChapter.' berhasil ditambahkan.');
    }

    public function saveRoadmapTitle(Request $request, string $course): RedirectResponse
    {
        $this->ensureInstructor();

        $meta = $this->catalog($course);

        $validated = $request->validate([
            'roadmap_title' => ['required', 'string', 'max:150'],
        ]);

        $title = trim($validated['roadmap_title']);

        if ($course === 'frontend-craft') {
            $existing = DB::table('course_page_contents')
                ->where('course_slug', 'frontend-craft')
                ->first();

            DB::table('course_page_contents')->updateOrInsert(
                ['course_slug' => 'frontend-craft'],
                [
                    'hero_title' => $title,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                    'created_at' => $existing ? $existing->created_at : now(),
                ]
            );

            $this->forgetCourseCaches('frontend-craft');

            return back()->with('success', 'Roadmap title berhasil diperbarui.');
        }

        if (preg_match('/^quiz-(\d+)$/', $course, $matches)) {
            $quizId = (int) $matches[1];
            $existing = DB::table('quiz_course_infos')
                ->where('quiz_id', $quizId)
                ->first();

            DB::table('quiz_course_infos')->updateOrInsert(
                ['quiz_id' => $quizId],
                [
                    'hero_title' => $title,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                    'created_at' => $existing ? $existing->created_at : now(),
                ]
            );

            $this->forgetCourseCaches($course);

            return back()->with('success', 'Roadmap title berhasil diperbarui.');
        }

        return back()->withErrors(['roadmap_title' => 'Course tidak dikenali.']);
    }
}
