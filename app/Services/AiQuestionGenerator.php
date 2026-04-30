<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiQuestionGenerator
{
    public function generate(array $payload): array
    {
        $apiKey = (string) config('ai.deepseek_api_key', '');
        if ($apiKey === '') {
            throw new \RuntimeException('DEEPSEEK_API_KEY belum diisi di file .env.');
        }

        $model = $this->model();
        $response = Http::timeout(90)
            ->withToken($apiKey)
            ->acceptJson()
            ->post('https://api.deepseek.com/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->systemPrompt(),
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->userPrompt($payload),
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->failed()) {
            $errorMessage = (string) data_get($response->json(), 'error.message', '');
            throw new \RuntimeException($errorMessage !== '' ? $errorMessage : 'DeepSeek tidak bisa membuat preview soal sekarang.');
        }

        $json = $response->json();
        $content = trim((string) data_get($json, 'choices.0.message.content', ''));
        $decoded = json_decode($content, true);

        if (!is_array($decoded) || !isset($decoded['questions']) || !is_array($decoded['questions'])) {
            throw new \RuntimeException('Format balasan AI tidak valid untuk preview soal.');
        }

        return [
            'questions' => $this->normalizeQuestions($decoded['questions'], $payload),
            'usage' => [
                'provider' => 'deepseek',
                'model' => (string) data_get($json, 'model', $model),
                'token_count' => (int) data_get($json, 'usage.total_tokens', 0),
            ],
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You generate high-quality course quiz questions for an LMS.

Return ONLY valid JSON with this shape:
{
  "questions": [
    {
      "question_text": "string",
      "question_type": "mcq|essay|true_false",
      "difficulty": "beginner|intermediate|advanced",
      "correct_answer": "string",
      "options": ["A", "B", "C", "D"]
    }
  ]
}

Rules:
- MCQ questions must contain exactly 4 options.
- true_false questions must contain exactly 2 options: ["True", "False"].
- essay questions must have an empty options array.
- Keep questions aligned to the requested course, difficulty, and generation notes.
- Do not wrap JSON in markdown fences.
PROMPT;
    }

    private function userPrompt(array $payload): string
    {
        return implode("\n", [
            'Course title: '.$payload['course_title'],
            'Course category: '.($payload['course_category'] ?: 'General'),
            'Requested difficulty: '.$payload['difficulty'],
            'Question count: '.$payload['question_count'],
            'Question type mode: '.$payload['question_type_mode'],
            'Placement after chapter: '.($payload['placement_after_chapter'] ? $payload['placement_after_chapter'] : 'none'),
            'Generation notes / lore:',
            trim($payload['generation_notes']),
        ]);
    }

    private function normalizeQuestions(array $questions, array $payload): array
    {
        $normalized = [];
        foreach ($questions as $question) {
            if (!is_array($question) || empty($question['question_text'])) {
                continue;
            }

            $type = (string) ($question['question_type'] ?? 'mcq');
            if (!in_array($type, ['mcq', 'essay', 'true_false'], true)) {
                $type = 'mcq';
            }

            $difficulty = (string) ($question['difficulty'] ?? $payload['difficulty']);
            if (!in_array($difficulty, ['beginner', 'intermediate', 'advanced'], true)) {
                $difficulty = $payload['difficulty'];
            }

            $options = array_values(array_filter(array_map(
                static fn ($option) => trim((string) $option),
                is_array($question['options'] ?? null) ? $question['options'] : []
            )));

            if ($type === 'mcq') {
                $options = array_slice(array_pad($options, 4, ''), 0, 4);
            } elseif ($type === 'true_false') {
                $options = ['True', 'False'];
            } else {
                $options = [];
            }

            $normalized[] = [
                'question_text' => trim((string) $question['question_text']),
                'question_type' => $type,
                'difficulty' => $difficulty,
                'correct_answer' => trim((string) ($question['correct_answer'] ?? '')),
                'options_json' => !empty($options) ? json_encode($options) : null,
            ];
        }

        return array_slice($normalized, 0, (int) $payload['question_count']);
    }

    private function model(): string
    {
        $rawModel = strtolower(trim((string) config('ai.deepseek_model', 'deepseek-chat')));
        $supportedModels = [
            'deepseek-v4-flash',
            'deepseek-v4-pro',
            'deepseek-chat',
            'deepseek-reasoner',
        ];

        return in_array($rawModel, $supportedModels, true) ? $rawModel : 'deepseek-chat';
    }
}
