<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_course_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->string('tagline')->nullable();
            $table->longText('about')->nullable();
            $table->string('target_audience')->nullable();
            $table->longText('learning_outcomes')->nullable();
            $table->string('trailer_url')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('quiz_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_course_infos');
    }
};

