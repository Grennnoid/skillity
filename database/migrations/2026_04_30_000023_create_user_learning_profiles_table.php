<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_learning_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('primary_interest', 120)->nullable();
            $table->string('goal', 40)->nullable();
            $table->string('experience_level', 40)->nullable();
            $table->string('study_pace', 40)->nullable();
            $table->string('learning_style', 40)->nullable();
            $table->json('recommendations_json')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_learning_profiles');
    }
};
