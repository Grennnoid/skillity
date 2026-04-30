<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('quiz_id')->nullable();
            $table->string('course_slug', 120);
            $table->string('course_title', 255);
            $table->unsignedInteger('chapter_number')->nullable();
            $table->text('question_text');
            $table->text('answer_text')->nullable();
            $table->timestamps();

            $table->index(['course_slug', 'chapter_number']);
            $table->index(['dosen_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_questions');
    }
};

