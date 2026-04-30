<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\DosenDashboardController;
use App\Http\Controllers\InstructorCourseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\StudentChatbotController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureDosen;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $landingCourses = Cache::remember('landing:courses', 300, function () {
        $quizCourses = DB::table('quizzes')
            ->select('id', 'title')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $courses = collect([
            [
                'title' => 'Frontend Craft',
                'href' => route('courses.frontend-craft.info'),
            ],
        ]);

        foreach ($quizCourses as $quiz) {
            if (strtolower(trim((string) $quiz->title)) === 'frontend craft') {
                continue;
            }

            $courses->push([
                'title' => $quiz->title,
                'href' => route('courses.quiz.info', ['quiz' => $quiz->id]),
            ]);
        }

        return $courses;
    });

    $landingStats = Cache::remember('landing:stats', 300, function () use ($landingCourses) {
        return [
            'total_courses' => $landingCourses->count(),
            'total_learners' => DB::table('users')->where('role', 'student')->count(),
            'total_mentors' => DB::table('users')->where('role', 'dosen')->count(),
        ];
    });

    return view('landing', [
        'landingCourses' => $landingCourses,
        'landingStats' => $landingStats,
    ]);
})->name('landing');

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'id'], true), 404);
    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale.switch');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/teach-on-skillify', [AuthController::class, 'teachEntry'])->name('teach.entry');

Route::middleware(['auth'])->group(function () {
    Route::get('/dosen/pending-approval', [AuthController::class, 'showDosenPendingApproval'])->name('dosen.pending-approval');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::patch('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile', [AuthController::class, 'destroyAccount'])->name('profile.destroy');
    Route::post('/profile/upload', [AuthController::class, 'uploadProfileImage'])->name('profile.upload');
    Route::post('/update-password', [AuthController::class, 'updatePassword'])->name('password.update');
    Route::post('/teach-on-skillify', [AuthController::class, 'requestDosenAccess'])->name('teach.request');

    Route::middleware([EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

        Route::patch('/users/{user}/role', [AdminDashboardController::class, 'updateUserRole'])->name('users.role');
        Route::patch('/users/{user}/status', [AdminDashboardController::class, 'updateUserStatus'])->name('users.status');
        Route::patch('/users/{user}/dosen-request', [AdminDashboardController::class, 'handleDosenRequest'])->name('users.dosen-request');
        Route::delete('/users/{user}', [AdminDashboardController::class, 'deleteUser'])->name('users.delete');

        Route::post('/quizzes', [AdminDashboardController::class, 'storeQuiz'])->name('quizzes.store');
        Route::post('/courses', [AdminDashboardController::class, 'storeQuiz'])->name('courses.store');

        Route::post('/questions', [AdminDashboardController::class, 'storeQuestion'])->name('questions.store');
        Route::delete('/questions/{question}', [AdminDashboardController::class, 'deleteQuestion'])->name('questions.delete');
        Route::post('/questions/ai/preview', [AdminDashboardController::class, 'previewAiQuestions'])->name('questions.ai.preview');
        Route::post('/questions/ai/save', [AdminDashboardController::class, 'saveAiQuestions'])->name('questions.ai.save');
        Route::post('/questions/import', [AdminDashboardController::class, 'importQuestions'])->name('questions.import');
        Route::get('/questions/export', [AdminDashboardController::class, 'exportQuestions'])->name('questions.export');

        Route::patch('/submissions/{submission}/grade', [AdminDashboardController::class, 'gradeSubmission'])->name('submissions.grade');
        Route::get('/grades/export', [AdminDashboardController::class, 'exportGrades'])->name('grades.export');

        Route::post('/modules', [AdminDashboardController::class, 'storeModule'])->name('modules.store');
        Route::post('/categories', [AdminDashboardController::class, 'storeCategory'])->name('categories.store');

        Route::post('/settings/maintenance', [AdminDashboardController::class, 'setMaintenance'])->name('settings.maintenance');
        Route::post('/settings/identity', [AdminDashboardController::class, 'updateSiteIdentity'])->name('settings.identity');
        Route::post('/settings/chatbot', [AdminDashboardController::class, 'updateChatbotSettings'])->name('settings.chatbot');
        Route::post('/courses/info', [AdminDashboardController::class, 'updateCourseInfo'])->name('courses.info.update');
        Route::post('/courses/frontend-craft/page', [AdminDashboardController::class, 'updateFrontendCraftPage'])->name('courses.frontend-craft.page.update');
    });

    Route::get('/student/pathfinder', [StudentCourseController::class, 'pathfinder'])->name('student.pathfinder');
    Route::post('/student/pathfinder', [StudentCourseController::class, 'savePathfinder'])->name('student.pathfinder.save');
    Route::get('/student/dashboard', [StudentCourseController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/student/courses', [StudentCourseController::class, 'coursesDirectory'])->name('student.courses');
    Route::get('/student/mentors', [StudentCourseController::class, 'mentorsDirectory'])->name('student.mentors');

    Route::middleware([EnsureDosen::class])->prefix('dosen')->name('dosen.')->group(function () {
        Route::get('/dashboard', [DosenDashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/courses', [DosenDashboardController::class, 'storeCourse'])->name('courses.store');
        Route::post('/courses/info', [DosenDashboardController::class, 'updateCourseInfo'])->name('courses.info.update');
        Route::post('/courses/frontend-craft/page', [DosenDashboardController::class, 'updateFrontendCraftPage'])->name('courses.frontend-craft.page.update');
        Route::post('/questions', [DosenDashboardController::class, 'storeQuestion'])->name('questions.store');
        Route::delete('/questions/{question}', [DosenDashboardController::class, 'deleteQuestion'])->name('questions.delete');
        Route::post('/questions/ai/preview', [DosenDashboardController::class, 'previewAiQuestions'])->name('questions.ai.preview');
        Route::post('/questions/ai/save', [DosenDashboardController::class, 'saveAiQuestions'])->name('questions.ai.save');
        Route::patch('/questions/{question}/answer', [DosenDashboardController::class, 'answerCourseQuestion'])->name('questions.answer');
        Route::get('/scores/export', [DosenDashboardController::class, 'exportScores'])->name('scores.export');
    });

    Route::get('/courses/frontend-craft', [StudentCourseController::class, 'frontendCraft'])->name('courses.frontend-craft');
    Route::get('/courses/frontend-craft/info', [StudentCourseController::class, 'frontendCraftInfo'])->name('courses.frontend-craft.info');
    Route::get('/courses/frontend-craft/roadmap', [StudentCourseController::class, 'frontendCraftRoadmap'])->name('courses.frontend-craft.roadmap');
    Route::get('/courses/frontend-craft/chapters/{chapter}', [StudentCourseController::class, 'frontendCraftChapter'])
        ->whereNumber('chapter')
        ->name('courses.frontend-craft.chapter');
    Route::match(['get', 'post'], '/courses/frontend-craft/chapters/{chapter}/complete', [StudentCourseController::class, 'completeFrontendCraftChapter'])
        ->whereNumber('chapter')
        ->name('courses.frontend-craft.chapter.complete');
    Route::get('/courses/frontend-craft/pop-quiz/{afterChapter}', [StudentCourseController::class, 'frontendCraftPopQuiz'])
        ->whereNumber('afterChapter')
        ->name('courses.frontend-craft.pop-quiz');
    Route::post('/courses/frontend-craft/pop-quiz/{afterChapter}', [StudentCourseController::class, 'submitFrontendCraftPopQuiz'])
        ->whereNumber('afterChapter')
        ->name('courses.frontend-craft.pop-quiz.submit');
    Route::get('/courses/quiz/{quiz}', [StudentCourseController::class, 'showQuizCourse'])->whereNumber('quiz')->name('courses.quiz.show');
    Route::get('/courses/quiz/{quiz}/info', [StudentCourseController::class, 'quizInfo'])->whereNumber('quiz')->name('courses.quiz.info');
    Route::post('/courses/quiz/{quiz}/enroll', [StudentCourseController::class, 'enrollQuiz'])->whereNumber('quiz')->name('courses.quiz.enroll');
    Route::get('/courses/quiz/{quiz}/roadmap', [StudentCourseController::class, 'quizRoadmap'])->whereNumber('quiz')->name('courses.quiz.roadmap');
    Route::get('/courses/quiz/{quiz}/chapters/{chapter}', [StudentCourseController::class, 'quizChapter'])
        ->whereNumber('quiz')
        ->whereNumber('chapter')
        ->name('courses.quiz.chapter');
    Route::match(['get', 'post'], '/courses/quiz/{quiz}/chapters/{chapter}/complete', [StudentCourseController::class, 'completeQuizChapter'])
        ->whereNumber('quiz')
        ->whereNumber('chapter')
        ->name('courses.quiz.chapter.complete');
    Route::get('/courses/quiz/{quiz}/pop-quiz/{afterChapter}', [StudentCourseController::class, 'quizPopQuiz'])
        ->whereNumber('quiz')
        ->whereNumber('afterChapter')
        ->name('courses.quiz.pop-quiz');
    Route::post('/courses/quiz/{quiz}/pop-quiz/{afterChapter}', [StudentCourseController::class, 'submitQuizPopQuiz'])
        ->whereNumber('quiz')
        ->whereNumber('afterChapter')
        ->name('courses.quiz.pop-quiz.submit');
    Route::get('/mentors/{mentor}', [StudentCourseController::class, 'mentorProfile'])->whereNumber('mentor')->name('mentors.show');
    Route::post('/courses/{slug}/enroll', [StudentCourseController::class, 'enroll'])->name('courses.enroll');
    Route::post('/courses/{slug}/favorite', [StudentCourseController::class, 'toggleFavorite'])->name('courses.favorite');
    Route::post('/courses/{slug}/consistent-mode', [StudentCourseController::class, 'updateConsistentMode'])->name('courses.consistent-mode.update');
    Route::post('/courses/{slug}/reviews', [StudentCourseController::class, 'submitReview'])->name('courses.reviews.store');
    Route::post('/courses/{slug}/questions', [StudentCourseController::class, 'submitQuestion'])->name('courses.questions.store');
    Route::get('/student/chatbot/messages', [StudentChatbotController::class, 'history'])->name('student.chatbot.history');
    Route::post('/student/chatbot/messages', [StudentChatbotController::class, 'send'])->name('student.chatbot.send');
    Route::delete('/student/chatbot/messages', [StudentChatbotController::class, 'clear'])->name('student.chatbot.clear');

    Route::get('/instructor/courses/{course}/roadmap', [InstructorCourseController::class, 'roadmap'])->name('instructor.courses.roadmap');
    Route::get('/instructor/courses/{course}/chapters/{chapter}', [InstructorCourseController::class, 'showLesson'])
        ->whereNumber('chapter')
        ->name('instructor.courses.lesson');
    Route::post('/instructor/courses/{course}/chapters/{chapter}', [InstructorCourseController::class, 'saveLesson'])
        ->whereNumber('chapter')
        ->name('instructor.courses.lesson.save');
    Route::post('/instructor/courses/{course}/roadmap-title', [InstructorCourseController::class, 'saveRoadmapTitle'])
        ->name('instructor.courses.roadmap.title.save');
    Route::post('/instructor/courses/{course}/chapters/add', [InstructorCourseController::class, 'addChapter'])
        ->name('instructor.courses.chapters.add');
});
