<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->string('course_slug');
            $table->unsignedTinyInteger('chapter_number');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_path')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['course_slug', 'chapter_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_lessons');
    }
};

