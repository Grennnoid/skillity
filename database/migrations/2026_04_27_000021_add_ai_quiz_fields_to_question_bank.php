<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->string('course_slug', 120)->nullable()->after('quiz_id');
            $table->unsignedInteger('placement_after_chapter')->nullable()->after('difficulty');
            $table->boolean('is_pop_quiz')->default(false)->after('placement_after_chapter');
            $table->boolean('requires_perfect_score')->default(false)->after('is_pop_quiz');
            $table->string('question_origin', 20)->default('manual')->after('requires_perfect_score');
            $table->text('generation_notes')->nullable()->after('question_origin');
        });
    }

    public function down(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->dropColumn([
                'course_slug',
                'placement_after_chapter',
                'is_pop_quiz',
                'requires_perfect_score',
                'question_origin',
                'generation_notes',
            ]);
        });
    }
};
