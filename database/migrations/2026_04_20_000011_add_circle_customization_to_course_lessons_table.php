<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->string('circle_bg_path')->nullable()->after('video_path');
            $table->string('circle_small_text')->nullable()->after('circle_bg_path');
            $table->string('circle_main_text')->nullable()->after('circle_small_text');
        });
    }

    public function down(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropColumn([
                'circle_bg_path',
                'circle_small_text',
                'circle_main_text',
            ]);
        });
    }
};

