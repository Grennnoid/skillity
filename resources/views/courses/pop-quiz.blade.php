<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.course.pop_quiz_title_page', ['course' => $course->title]) }}</title>
    <style>
        :root {
            --bg: #050914;
            --panel: rgba(11, 19, 35, 0.88);
            --line: rgba(154, 178, 225, 0.24);
            --text: #e9f1ff;
            --muted: #9db1d6;
            --accent: #45d0ff;
            --gold: #f6d87a;
            --gold-2: #fff2c2;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(1200px 620px at 15% -18%, rgba(69, 208, 255, 0.2), transparent 60%),
                radial-gradient(900px 480px at 85% -20%, rgba(124, 246, 214, 0.16), transparent 56%),
                var(--bg);
        }

        .wrap {
            width: min(1080px, 94vw);
            margin: 0 auto;
            padding: 24px 0 40px;
            display: grid;
            gap: 14px;
        }

        .topbar,
        .hero,
        .card {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--panel);
            box-shadow: 0 18px 42px rgba(1, 7, 21, 0.42);
        }

        .topbar {
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .hero {
            padding: 18px;
            background:
                linear-gradient(135deg, rgba(92, 12, 22, 0.82), rgba(24, 9, 15, 0.92)),
                var(--panel);
        }

        h1 {
            margin: 0 0 6px;
            font-size: clamp(30px, 4vw, 44px);
        }

        .muted {
            color: var(--muted);
            line-height: 1.6;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid rgba(255, 137, 153, 0.26);
            background: rgba(63, 12, 22, 0.45);
            color: #ffd4db;
            font-size: 13px;
            font-weight: 700;
        }

        .top-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .chip,
        .submit-btn {
            border: 0;
            border-radius: 999px;
            text-decoration: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
        }

        .chip {
            padding: 10px 14px;
            border: 1px solid var(--line);
            background: rgba(8, 14, 27, 0.84);
            color: #d8e6ff;
        }

        .submit-btn {
            padding: 12px 18px;
            color: #251a09;
            background: linear-gradient(120deg, var(--gold), var(--gold-2));
            box-shadow: 0 14px 28px rgba(246, 216, 122, 0.22);
        }

        .error-box,
        .success-box {
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 14px;
        }

        .error-box {
            border: 1px solid rgba(255, 107, 125, 0.4);
            background: rgba(72, 21, 33, 0.42);
            color: #ffcad2;
        }

        .success-box {
            border: 1px solid rgba(124, 246, 214, 0.32);
            background: rgba(10, 41, 33, 0.4);
            color: #c7f9ea;
        }

        .card {
            padding: 16px;
        }

        .question-list {
            display: grid;
            gap: 12px;
        }

        .question {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(8, 14, 27, 0.72);
            padding: 14px;
        }

        .question.correct {
            border-color: rgba(124, 246, 214, 0.38);
            background: rgba(10, 41, 33, 0.34);
        }

        .question.correct .question-head strong {
            color: #dffdf5;
        }

        .question-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .question-head strong {
            font-size: 18px;
        }

        .difficulty {
            font-size: 12px;
            color: #dfeaff;
            border: 1px solid rgba(69, 208, 255, 0.25);
            background: rgba(10, 20, 37, 0.85);
            padding: 4px 8px;
            border-radius: 999px;
            height: fit-content;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .status-pill.correct {
            color: #d7fff1;
            border: 1px solid rgba(124, 246, 214, 0.36);
            background: rgba(10, 41, 33, 0.42);
        }

        .options {
            display: grid;
            gap: 8px;
        }

        .option {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(10, 17, 31, 0.82);
            padding: 10px 12px;
        }

        .option input {
            margin-top: 2px;
        }

        textarea.answer-field {
            width: 100%;
            min-height: 110px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(8, 14, 27, 0.82);
            color: var(--text);
            padding: 12px;
            font-size: 14px;
            resize: vertical;
            font-family: inherit;
        }

        @media (max-width: 760px) {
            .wrap {
                width: min(100%, calc(100vw - 24px));
                padding: 16px 0 28px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .top-actions {
                width: 100%;
            }

            .top-actions .chip,
            .top-actions .submit-btn {
                flex: 1 1 100%;
                text-align: center;
            }

            .hero,
            .card,
            .topbar {
                border-radius: 16px;
            }

            .hero,
            .card {
                padding: 14px;
            }

            .question {
                padding: 12px;
            }

            .question-head {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 26px;
            }

            .badge {
                width: 100%;
                justify-content: center;
                text-align: center;
                line-height: 1.5;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="topbar">
        <div>
            <strong>{{ $course->title }}</strong><br>
            <span class="muted">{{ __('ui.course.pop_quiz_after', ['chapter' => $afterChapter]) }}</span>
        </div>
        <div class="top-actions">
            <a class="chip" href="{{ $roadmapUrl }}">{{ __('ui.course.back_to_roadmap') }}</a>
            <a class="chip" href="{{ $dashboardUrl }}">{{ __('ui.course.dashboard') }}</a>
        </div>
    </div>

    <div class="hero">
        <div class="badge">{{ __('ui.course.pop_quiz_gate_label') }}</div>
        <h1>{{ __('ui.course.checkpoint_quiz') }}</h1>
        <p class="muted">{{ __('ui.course.checkpoint_text') }}</p>
    </div>

    @if($errors->has('pop_quiz'))
        <div class="error-box">{{ $errors->first('pop_quiz') }}</div>
    @endif

    @if(session('success'))
        <div class="success-box">{{ session('success') }}</div>
    @endif

    @php($result = session('popQuizResult'))
    @if($isPassed)
        <div class="success-box">
            {{ __('ui.course.already_passed') }}
            @if($lastScorePercent !== null)
                {{ __('ui.course.last_score', ['score' => rtrim(rtrim(number_format($lastScorePercent, 2), '0'), '.')]) }}
            @endif
        </div>
    @elseif(is_array($result))
        <div class="error-box">
            {{ __('ui.course.score_summary', ['score' => rtrim(rtrim(number_format($result['score_percent'], 2), '0'), '.'), 'correct' => $result['correct_answers'], 'total' => $result['total_questions']]) }}
        </div>
    @endif

    <form class="card" action="{{ $submitUrl }}" method="POST">
        @csrf
        <div class="question-list">
            @foreach($questions as $index => $question)
                @php($questionResult = collect(is_array($result['results'] ?? null) ? $result['results'] : [])->keyBy('id')->get($question['id']))
                <article class="question {{ !empty($questionResult['is_correct']) ? 'correct' : '' }}">
                    <div class="question-head">
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            <strong>{{ __('ui.course.question_label', ['number' => $index + 1]) }}</strong>
                            @if(!empty($questionResult['is_correct']))
                                <span class="status-pill correct">{{ __('ui.course.correct') }}</span>
                            @endif
                        </div>
                        <span class="difficulty">{{ strtoupper($question['difficulty']) }}</span>
                    </div>
                    <p style="margin: 0 0 12px; line-height: 1.7;">{{ $question['question_text'] }}</p>

                    @if(in_array($question['question_type'], ['mcq', 'true_false'], true))
                        <div class="options">
                            @foreach($question['options'] as $option)
                                <label class="option">
                                    <input type="radio" name="answers[{{ $question['id'] }}]" value="{{ $option }}" @checked(old('answers.'.$question['id']) === $option)>
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <textarea class="answer-field" name="answers[{{ $question['id'] }}]" placeholder="{{ __('ui.course.essay_placeholder') }}">{{ old('answers.'.$question['id']) }}</textarea>
                    @endif
                </article>
            @endforeach
        </div>

        <div style="margin-top: 14px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <button class="submit-btn" type="submit">{{ __('ui.course.submit_pop_quiz') }}</button>
            @if($isPassed)
                <a class="chip" href="{{ $nextChapterUrl }}">{{ __('ui.course.continue_to_next') }}</a>
            @endif
        </div>
    </form>
</div>
@include('partials.student-chatbot')
</body>
</html>
