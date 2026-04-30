<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_course_states', function (Blueprint $table) {
            $table->boolean('consistent_mode_enabled')->default(false)->after('is_favorite');
            $table->unsignedTinyInteger('consistent_mode_target')->default(1)->after('consistent_mode_enabled');
        });

        Schema::create('course_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('quiz_id')->nullable();
            $table->string('course_slug', 120);
            $table->string('course_title', 255);
            $table->date('attendance_date');
            $table->unsignedTinyInteger('target_chapters')->default(1);
            $table->unsignedInteger('chapters_completed')->default(0);
            $table->boolean('is_attended')->default(false);
            $table->timestamp('roadmap_entered_at')->nullable();
            $table->timestamp('attended_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_slug', 'attendance_date'], 'course_attendance_logs_user_course_date_unique');
            $table->index(['course_slug', 'attendance_date'], 'course_attendance_logs_slug_date_idx');
            $table->index(['dosen_id', 'attendance_date'], 'course_attendance_logs_dosen_date_idx');
            $table->index(['attendance_date', 'is_attended'], 'course_attendance_logs_date_attended_idx');
        });

        Schema::create('course_attendance_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_log_id')->constrained('course_attendance_logs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('course_slug', 120);
            $table->date('attendance_date');
            $table->unsignedInteger('chapter_number');
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->unique(
                ['user_id', 'course_slug', 'attendance_date', 'chapter_number'],
                'course_attendance_chapters_user_course_date_chapter_unique'
            );
            $table->index(['attendance_log_id'], 'course_attendance_chapters_log_idx');
            $table->index(['course_slug', 'attendance_date'], 'course_attendance_chapters_slug_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_attendance_chapters');
        Schema::dropIfExists('course_attendance_logs');

        Schema::table('user_course_states', function (Blueprint $table) {
            $table->dropColumn(['consistent_mode_enabled', 'consistent_mode_target']);
        });
    }
};
