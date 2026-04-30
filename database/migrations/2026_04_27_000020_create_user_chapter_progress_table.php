<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_chapter_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('course_slug');
            $table->unsignedInteger('chapter_number');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_slug', 'chapter_number'], 'user_chapter_progress_unique');
            $table->index(['course_slug', 'chapter_number'], 'user_chapter_progress_course_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_chapter_progress');
    }
};
