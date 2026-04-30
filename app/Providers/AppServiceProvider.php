<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['student.*', 'courses.*'], function ($view) {
            $settings = Cache::remember('settings:chatbot', 300, function () {
                return DB::table('system_settings')
                    ->whereIn('key', [
                        'chatbot_name',
                        'chatbot_welcome',
                        'chatbot_placeholder',
                    ])
                    ->pluck('value', 'key')
                    ->all();
            });

            $view->with('chatbotSettings', [
                'name' => $settings['chatbot_name'] ?? 'Skillify AI',
                'welcome' => $settings['chatbot_welcome'] ?? 'Hi, I am here to help with your courses, roadmap, and study questions.',
                'placeholder' => $settings['chatbot_placeholder'] ?? 'Ask about this course, chapter, or your study plan...',
                'configured' => (string) config('ai.deepseek_api_key', '') !== '',
            ]);
        });
    }
}
