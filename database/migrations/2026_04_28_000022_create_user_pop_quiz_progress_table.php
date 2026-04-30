<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_pop_quiz_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('course_slug', 120);
            $table->unsignedInteger('placement_after_chapter');
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->default(0);
            $table->json('answers_json')->nullable();
            $table->timestamp('last_submitted_at')->nullable();
            $table->timestamp('passed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_slug', 'placement_after_chapter'], 'user_pop_quiz_progress_unique');
            $table->index(['course_slug', 'placement_after_chapter'], 'user_pop_quiz_progress_course_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_pop_quiz_progress');
    }
};
