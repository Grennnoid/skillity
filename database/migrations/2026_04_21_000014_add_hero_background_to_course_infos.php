<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_course_infos', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_course_infos', 'hero_background_url')) {
                $table->string('hero_background_url')->nullable()->after('hero_title');
            }
        });

        Schema::table('course_page_contents', function (Blueprint $table) {
            if (!Schema::hasColumn('course_page_contents', 'hero_background_url')) {
                $table->string('hero_background_url')->nullable()->after('hero_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_course_infos', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_course_infos', 'hero_background_url')) {
                $table->dropColumn('hero_background_url');
            }
        });

        Schema::table('course_page_contents', function (Blueprint $table) {
            if (Schema::hasColumn('course_page_contents', 'hero_background_url')) {
                $table->dropColumn('hero_background_url');
            }
        });
    }
};

