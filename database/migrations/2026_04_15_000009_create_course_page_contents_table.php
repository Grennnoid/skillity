<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('course_slug')->unique();
            $table->string('hero_title')->nullable();
            $table->string('tagline')->nullable();
            $table->text('about')->nullable();
            $table->string('target_audience')->nullable();
            $table->string('duration_text')->nullable();
            $table->longText('syllabus_json')->nullable();
            $table->longText('outcomes_text')->nullable();
            $table->string('trailer_url')->nullable();
            $table->string('trailer_poster_url')->nullable();
            $table->string('instructor_name')->nullable();
            $table->string('instructor_photo_url')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_page_contents');
    }
};

