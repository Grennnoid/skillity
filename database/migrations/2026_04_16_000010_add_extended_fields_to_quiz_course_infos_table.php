<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_course_infos', function (Blueprint $table) {
            $table->string('hero_title')->nullable()->after('quiz_id');
            $table->string('duration_text')->nullable()->after('target_audience');
            $table->longText('syllabus_json')->nullable()->after('duration_text');
            $table->string('trailer_poster_url')->nullable()->after('trailer_url');
            $table->string('instructor_name')->nullable()->after('trailer_poster_url');
            $table->string('instructor_photo_url')->nullable()->after('instructor_name');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_course_infos', function (Blueprint $table) {
            $table->dropColumn([
                'hero_title',
                'duration_text',
                'syllabus_json',
                'trailer_poster_url',
                'instructor_name',
                'instructor_photo_url',
            ]);
        });
    }
};

