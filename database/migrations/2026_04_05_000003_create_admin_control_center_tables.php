<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'account_status')) {
                $table->enum('account_status', ['active', 'suspended'])->default('active')->after('role');
            }
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('question_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->nullOnDelete();
            $table->text('question_text');
            $table->enum('question_type', ['mcq', 'essay', 'true_false'])->default('mcq');
            $table->string('category')->nullable();
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->text('correct_answer')->nullable();
            $table->text('options_json')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('quiz_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('manual_score', 5, 2)->nullable();
            $table->enum('status', ['submitted', 'graded'])->default('submitted');
            $table->longText('answers_json')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('skill_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('learning_modules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('category_id')->nullable()->constrained('skill_categories')->nullOnDelete();
            $table->enum('type', ['pdf', 'youtube', 'article']);
            $table->string('content_url')->nullable();
            $table->longText('article_body')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('openai');
            $table->string('model')->default('gpt-5');
            $table->integer('token_count')->default(0);
            $table->decimal('cost_usd', 10, 4)->default(0);
            $table->timestamp('logged_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ai_feedback_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('prompt_summary');
            $table->string('detected_topic')->nullable();
            $table->string('sentiment')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('ai_feedback_logs');
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('learning_modules');
        Schema::dropIfExists('skill_categories');
        Schema::dropIfExists('quiz_submissions');
        Schema::dropIfExists('question_bank');
        Schema::dropIfExists('quizzes');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'account_status')) {
                $table->dropColumn('account_status');
            }
        });
    }
};
