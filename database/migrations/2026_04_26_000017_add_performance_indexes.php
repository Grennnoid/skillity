<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_idx');
            $table->index('account_status', 'users_account_status_idx');
            $table->index(['requested_role', 'dosen_request_status'], 'users_requested_role_dosen_status_idx');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->index(['created_by', 'created_at'], 'quizzes_created_by_created_at_idx');
            $table->index(['category', 'created_at'], 'quizzes_category_created_at_idx');
        });

        Schema::table('question_bank', function (Blueprint $table) {
            $table->index(['created_by', 'created_at'], 'question_bank_created_by_created_at_idx');
        });

        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->index(['quiz_id', 'submitted_at'], 'quiz_submissions_quiz_id_submitted_at_idx');
            $table->index(['user_id', 'submitted_at'], 'quiz_submissions_user_id_submitted_at_idx');
            $table->index(['status', 'submitted_at'], 'quiz_submissions_status_submitted_at_idx');
        });

        Schema::table('learning_modules', function (Blueprint $table) {
            $table->index(['uploaded_by', 'created_at'], 'learning_modules_uploaded_by_created_at_idx');
        });

        Schema::table('course_page_contents', function (Blueprint $table) {
            $table->index('updated_by', 'course_page_contents_updated_by_idx');
        });

        Schema::table('course_reviews', function (Blueprint $table) {
            $table->index(['course_slug', 'user_id'], 'course_reviews_course_slug_user_id_idx');
        });

        Schema::table('course_questions', function (Blueprint $table) {
            $table->index(['course_slug', 'created_at'], 'course_questions_course_slug_created_at_idx');
            $table->index(['course_slug', 'chapter_number', 'created_at'], 'course_questions_slug_chapter_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('course_questions', function (Blueprint $table) {
            $table->dropIndex('course_questions_slug_chapter_created_at_idx');
            $table->dropIndex('course_questions_course_slug_created_at_idx');
        });

        Schema::table('course_reviews', function (Blueprint $table) {
            $table->dropIndex('course_reviews_course_slug_user_id_idx');
        });

        Schema::table('course_page_contents', function (Blueprint $table) {
            $table->dropIndex('course_page_contents_updated_by_idx');
        });

        Schema::table('learning_modules', function (Blueprint $table) {
            $table->dropIndex('learning_modules_uploaded_by_created_at_idx');
        });

        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropIndex('quiz_submissions_status_submitted_at_idx');
            $table->dropIndex('quiz_submissions_user_id_submitted_at_idx');
            $table->dropIndex('quiz_submissions_quiz_id_submitted_at_idx');
        });

        Schema::table('question_bank', function (Blueprint $table) {
            $table->dropIndex('question_bank_created_by_created_at_idx');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex('quizzes_category_created_at_idx');
            $table->dropIndex('quizzes_created_by_created_at_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_requested_role_dosen_status_idx');
            $table->dropIndex('users_account_status_idx');
            $table->dropIndex('users_role_idx');
        });
    }
};
