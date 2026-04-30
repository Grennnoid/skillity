<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $roadmapTitle }} Roadmap | Skillify</title>
    <style>
        :root {
            --bg: #050914;
            --text: #e9f1ff;
            --muted: #9db1d6;
            --accent: #45d0ff;
            --accent-deep: #28b8ea;
            --line: rgba(154, 178, 225, 0.24);
            --ui-bg: #091427;
            --ui-line: rgba(116, 170, 255, 0.42);
            --ui-text: #e7f0ff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(1200px 620px at 15% -18%, rgba(69, 208, 255, 0.2), transparent 60%),
                radial-gradient(900px 480px at 85% -20%, rgba(124, 246, 214, 0.16), transparent 56%),
                var(--bg);
            overflow: hidden;
        }

        .page {
            height: 100vh;
            padding: 14px 0 12px;
            display: grid;
            grid-template-rows: auto auto auto 1fr auto;
        }

        .topbar,
        .hero,
        .controls,
        .actions {
            width: min(1240px, 95vw);
            margin-left: auto;
            margin-right: auto;
        }

        .topbar {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 8px 2px 10px;
            border-bottom: 1px solid var(--line);
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .brand-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: linear-gradient(120deg, var(--accent), #7cf6d6);
            box-shadow: 0 0 12px rgba(69, 208, 255, 0.65);
            flex-shrink: 0;
        }

        .brand {
            font-weight: 700;
            letter-spacing: 0.2px;
            color: #eef4ff;
            white-space: nowrap;
        }

        .course-chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 6px 11px;
            font-size: 12px;
            color: #cde0ff;
            background: rgba(8, 14, 27, 0.86);
            white-space: nowrap;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .link {
            text-decoration: none;
            color: #cfe0ff;
            padding: 4px 0;
            font-size: 16px;
            white-space: nowrap;
            border-bottom: 1px solid transparent;
            transition: border-color 0.18s ease, color 0.18s ease;
        }

        .link:hover {
            color: #eef5ff;
            border-color: rgba(143, 194, 255, 0.55);
        }

        .favorite-btn {
            border: 0;
            background: transparent;
            color: #cfe0ff;
            padding: 0;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            transition: color 0.18s ease, transform 0.18s ease;
        }

        .favorite-btn.active {
            color: #ffd86d;
        }

        .favorite-btn:hover {
            transform: translateY(-1px);
        }

        .attendance-trigger {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid rgba(255, 183, 76, 0.45);
            background: linear-gradient(180deg, rgba(44, 24, 6, 0.96), rgba(17, 11, 5, 0.96));
            color: #ffbf66;
            font-size: 19px;
            line-height: 1;
            cursor: pointer;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.34);
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .attendance-trigger.active {
            border-color: rgba(255, 197, 92, 0.8);
            color: #ffe19c;
            box-shadow: 0 0 0 3px rgba(255, 183, 76, 0.12), 0 12px 26px rgba(48, 26, 8, 0.42);
        }

        .attendance-trigger:hover {
            transform: translateY(-1px);
        }

        .profile-menu {
            position: relative;
            margin-left: 2px;
        }

        .profile-menu::after {
            content: "";
            position: absolute;
            right: -2px;
            bottom: 3px;
            width: 0;
            height: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 5px solid #d4e3ff;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.55));
            pointer-events: none;
            z-index: 2;
        }

        .profile-trigger {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1px solid var(--line);
            overflow: hidden;
            display: grid;
            place-items: center;
            background: rgba(11, 20, 38, 0.86);
            color: #f0f6ff;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            flex-shrink: 0;
        }

        .profile-trigger:hover { border-color: rgba(143, 194, 255, 0.72); }

        .profile-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 50%;
        }

        .profile-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            min-width: 260px;
            border: 1px solid var(--line);
            background: rgba(8, 14, 28, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            padding: 8px;
            display: none;
            box-shadow: 0 24px 56px rgba(0, 0, 0, 0.45);
            z-index: 20;
        }

        .profile-menu.open .profile-dropdown { display: block; }

        .profile-head {
            padding: 10px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 6px;
        }

        .profile-head strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .profile-head span {
            color: var(--muted);
            font-size: 12px;
        }

        .profile-item {
            width: 100%;
            text-align: left;
            border: 0;
            background: transparent;
            color: #dbe6ff;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
            display: block;
            cursor: pointer;
        }

        .profile-item:hover { background: rgba(121, 240, 212, 0.13); }
        .profile-form { margin: 0; }
        .profile-form button { font-family: inherit; }
        }

        .hero {
            margin-bottom: 8px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(11, 19, 35, 0.82);
            padding: 12px 16px;
            box-shadow: 0 18px 42px rgba(1, 7, 21, 0.46);
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(24px, 3.3vw, 38px);
            letter-spacing: -0.3px;
        }

        .hero p {
            margin: 6px 0 0;
            color: var(--muted);
            line-height: 1.5;
            max-width: 760px;
            font-size: 14px;
        }

        .attendance-summary {
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border: 1px solid rgba(255, 191, 102, 0.22);
            border-radius: 999px;
            background: rgba(16, 13, 8, 0.7);
            color: #f8dfb7;
            font-size: 13px;
        }

        .attendance-summary strong {
            color: #fff2cf;
        }

        .pop-quiz-alert {
            margin-top: 12px;
            border: 1px solid rgba(255, 107, 125, 0.32);
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(88, 16, 28, 0.88), rgba(24, 9, 15, 0.94));
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .pop-quiz-alert strong {
            display: block;
            margin-bottom: 4px;
            font-size: 17px;
        }

        .pop-quiz-alert span {
            color: #ffd8de;
            font-size: 13px;
            line-height: 1.5;
        }

        .pop-quiz-btn {
            flex-shrink: 0;
            border: 0;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            color: #fff4f6;
            text-decoration: none;
            background: linear-gradient(135deg, #ff6b7d, #ff9f68);
            box-shadow: 0 14px 24px rgba(255, 107, 125, 0.22);
        }

        .timeline {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-behavior: smooth;
            padding: 8px 0 0;
            cursor: grab;
            scrollbar-width: none;
        }

        .timeline::-webkit-scrollbar {
            display: none;
        }

        .timeline:active { cursor: grabbing; }

        .road {
            position: relative;
            min-width: 1760px;
            height: 360px;
            padding: 0 max(3vw, 20px);
        }

        .steps {
            list-style: none;
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            align-items: center;
            gap: 48px;
        }

        .step {
            width: 182px;
            position: relative;
            display: flex;
            justify-content: center;
        }

        .step-link {
            text-decoration: none;
            color: inherit;
            display: contents;
        }

        .step-link.disabled {
            pointer-events: none;
            cursor: default;
        }

        .step .node {
            width: 156px;
            height: 156px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #ffffff 0%, #f7f7f7 100%);
            border: 6px solid var(--accent);
            box-shadow: 0 16px 26px rgba(5, 10, 20, 0.3);
            display: grid;
            place-items: center;
            text-align: center;
            z-index: 2;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .step.completed .node {
            background: linear-gradient(145deg, #fff2bd 0%, #ffd45a 42%, #f0ad19 100%);
            border-color: #ffd45a;
            box-shadow: 0 16px 30px rgba(218, 160, 24, 0.34);
        }

        .step.completed .node small,
        .step.completed .node strong {
            color: #5f4309;
        }

        .step.locked .node {
            opacity: 0.38;
            filter: saturate(0.55);
            box-shadow: 0 16px 26px rgba(5, 10, 20, 0.12);
        }

        .step.locked .meta {
            opacity: 0.48;
        }

        .node small {
            display: block;
            color: #958d99;
            letter-spacing: 1px;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .node strong {
            font-size: 54px;
            line-height: 1;
            color: #7a7280;
            font-weight: 700;
        }

        .meta {
            position: absolute;
            width: 186px;
            text-align: center;
        }

        .meta .tag {
            display: block;
            color: #9eb2d7;
            letter-spacing: 0.9px;
            font-size: 11px;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .meta h3 {
            margin: 0 0 4px;
            font-size: 18px;
            line-height: 1.15;
            color: #edf3ff;
        }

        .meta p {
            margin: 0;
            font-size: 12px;
            line-height: 1.45;
            color: var(--muted);
        }

        .quiz-pill {
            display: inline-flex;
            align-items: center;
            margin-top: 8px;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid rgba(255, 107, 125, 0.3);
            background: rgba(92, 16, 28, 0.34);
            color: #ffd2d9;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .step.up {
            transform: translateY(-42px);
        }

        .step.down {
            transform: translateY(42px);
        }

        .step.up .meta {
            top: 162px;
        }

        .step.down .meta {
            bottom: 162px;
        }

        .step:hover .node {
            transform: scale(1.03);
            border-color: var(--accent-deep);
        }

        .controls {
            margin-top: 4px;
            margin-bottom: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
        }

        .control-btn {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid var(--ui-line);
            background: linear-gradient(180deg, #0c1b34 0%, #081225 100%);
            color: var(--ui-text);
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(6, 15, 32, 0.44);
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .control-btn:hover {
            transform: translateY(-2px);
            border-color: rgba(143, 194, 255, 0.72);
            box-shadow: 0 12px 26px rgba(5, 22, 52, 0.48);
        }

        .control-btn:active {
            transform: translateY(0);
        }

        .qa-drawer {
            position: fixed;
            right: 18px;
            top: 82px;
            width: min(420px, calc(100vw - 36px));
            max-height: calc(100vh - 110px);
            overflow: auto;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(8, 14, 28, 0.97);
            box-shadow: 0 24px 56px rgba(0, 0, 0, 0.45);
            z-index: 30;
            padding: 12px;
            display: none;
        }

        .qa-drawer.open { display: block; }

        .qa-list {
            display: grid;
            gap: 8px;
            margin-top: 10px;
        }

        .qa-item {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(9, 15, 29, 0.82);
            padding: 10px;
        }

        .qa-item-head {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 6px;
            color: #9db1d6;
            font-size: 12px;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(1, 6, 16, 0.7);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 60;
        }

        .modal-overlay.open { display: flex; }

        .attendance-modal {
            width: min(460px, 100%);
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(8, 14, 27, 0.97);
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.48);
            padding: 18px;
        }

        .attendance-modal h3 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        .attendance-modal p {
            margin: 0 0 12px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .attendance-modal label {
            display: block;
            margin-bottom: 6px;
            color: #d9e9ff;
            font-size: 13px;
            font-weight: 600;
        }

        .attendance-modal input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(9, 15, 29, 0.82);
            color: var(--text);
            padding: 11px 12px;
            font-size: 14px;
        }

        .attendance-modal-actions {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .modal-btn {
            border: 0;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .modal-btn.primary {
            color: #2f230c;
            background: linear-gradient(120deg, #ffdf88, #f2ad18);
        }

        .modal-btn.ghost {
            color: #dce9ff;
            border: 1px solid var(--line);
            background: rgba(12, 18, 34, 0.78);
        }

        .attendance-feedback {
            margin-top: 10px;
            font-size: 12px;
            color: #8fd5ff;
            min-height: 18px;
        }

        @media (max-width: 760px) {
            .topbar {
                flex-wrap: wrap;
            }

            .top-actions {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 2px;
                scrollbar-width: none;
            }

            .top-actions::-webkit-scrollbar {
                display: none;
            }

            .road {
                min-width: 1480px;
                height: 340px;
            }

            .step {
                width: 150px;
            }

            .step .node {
                width: 132px;
                height: 132px;
            }

            .node strong {
                font-size: 44px;
            }

            .meta {
                width: 150px;
            }

            .meta h3 {
                font-size: 15px;
            }

            .step.up {
                transform: translateY(-36px);
            }

            .step.down {
                transform: translateY(36px);
            }

            .step.up .meta {
                top: 138px;
            }

            .step.down .meta {
                bottom: 138px;
            }

            .attendance-summary {
                display: grid;
                border-radius: 14px;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <div class="brand-wrap">
            <span class="brand-dot" aria-hidden="true"></span>
            <span class="brand">{{ __('ui.course.skillify') }}</span>
        </div>
        <div class="top-actions">
            <a class="link" href="{{ route('courses.frontend-craft.info') }}">{{ __('ui.course.course_info') }}</a>
            <a class="link" href="{{ route('student.dashboard') }}">{{ __('ui.course.dashboard') }}</a>
            <button class="attendance-trigger {{ !empty($attendance['enabled']) ? 'active' : '' }}" id="attendanceTrigger" type="button" aria-label="Consistent mode">&#128293;</button>
            <button class="favorite-btn {{ !empty($isFavorite) ? 'active' : '' }}" id="favoriteBtn" type="button" aria-pressed="{{ !empty($isFavorite) ? 'true' : 'false' }}" aria-label="Favorite course">{!! !empty($isFavorite) ? '&#9733;' : '&#9734;' !!}</button>
            <a class="link" href="{{ route('courses.frontend-craft.info') }}#review">{{ __('ui.course.review') }}</a>
            <a class="link" href="#qa" id="qaToggleLink">{{ __('ui.course.qa') }}</a>
@php
    $nameParts = preg_split('/\s+/', trim(auth()->user()->name));
    $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1).substr($nameParts[1] ?? '', 0, 1));
    $initials = $initials !== '' ? $initials : 'U';
    $attendanceJson = json_encode($attendance ?? [
        'enabled' => false,
        'target_chapters' => 1,
        'today_completed' => 0,
        'today_attended' => false,
    ]);
@endphp
            <div class="profile-menu" id="profileMenuRoadmapFrontend">
                <a class="profile-trigger" href="{{ route('profile.show') }}" id="profileTriggerRoadmapFrontend" aria-expanded="false" aria-label="User profile">
                    @if(auth()->user()->profile_image)
                        <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="{{ auth()->user()->name }}" class="profile-image">
                    @else
                        {{ $initials }}
                    @endif
                </a>
                <div class="profile-dropdown">
                    <div class="profile-head">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ auth()->user()->email }}</span>
                    </div>
                    <a class="profile-item" href="{{ route('profile.show') }}">{{ __('ui.course.profile_settings') }}</a>
                    <a class="profile-item" href="{{ route('login', ['switch' => 1]) }}">{{ __('ui.course.switch_account') }}</a>
                    <form action="{{ route('logout') }}" method="POST" class="profile-form">
                        @csrf
                        <button type="submit" class="profile-item">{{ __('ui.course.logout') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <section class="hero">
        <h1>{{ $roadmapTitle }}</h1>
        <div class="attendance-summary" id="attendanceSummary">
            <span>{{ __('ui.course.consistent_mode') }}</span>
            <strong id="attendanceSummaryText">
                @if(!empty($attendance['enabled']))
                    {{ __('ui.course.chapter_today', ['completed' => $attendance['today_completed'], 'target' => $attendance['target_chapters']]) }} {{ !empty($attendance['today_attended']) ? '- '.__('ui.course.attendance_counted') : '- '.__('ui.course.target_not_met') }}
                @else
                    {{ __('ui.course.inactive') }}
                @endif
            </strong>
        </div>
        @if(!empty($pendingPopQuiz))
            <div class="pop-quiz-alert">
                <div>
                    <strong>{{ __('ui.course.pop_quiz_after_chapter', ['chapter' => $pendingPopQuiz['placement_after_chapter']]) }}</strong>
                    <span>{{ __('ui.course.pop_quiz_required', ['count' => $pendingPopQuiz['question_count']]) }}</span>
                </div>
                <a class="pop-quiz-btn" href="{{ $pendingPopQuiz['take_quiz_url'] }}">{{ __('ui.course.take_quiz_now') }}</a>
            </div>
        @endif
    </section>

    <div class="controls">
        <button class="control-btn" type="button" id="scrollLeftBtn" aria-label="Geser roadmap ke kiri">&larr;</button>
        <button class="control-btn" type="button" id="scrollRightBtn" aria-label="Geser roadmap ke kanan">&rarr;</button>
    </div>

    <div class="timeline" id="timeline">
        <div class="road">
            <ol class="steps">
                @foreach(($chapters ?? []) as $chapter)
                    <li class="step {{ $chapter['position'] }} {{ !empty($chapter['is_completed']) ? 'completed' : '' }} {{ !empty($chapter['is_locked']) ? 'locked' : '' }}">
                        <a class="step-link {{ empty($chapter['href']) ? 'disabled' : '' }}" href="{{ $chapter['href'] ?? '#' }}">
                            <div class="node"><div><small>{{ __('ui.course.step') }}</small><strong>{{ str_pad((string) $chapter['number'], 2, '0', STR_PAD_LEFT) }}</strong></div></div>
                            <article class="meta">
                                <span class="tag">
                                    @if(!empty($chapter['is_completed']))
                                        {{ __('ui.course.completed') }}
                                    @elseif(!empty($chapter['is_locked']))
                                        {{ __('ui.course.locked') }}
                                    @else
                                        {{ $chapter['video_ready'] ? __('ui.course.video_ready') : __('ui.course.chapter') }}
                                    @endif
                                </span>
                                <h3>{{ $chapter['title'] }}</h3>
                                <p>{{ $chapter['description'] }}</p>
                                @if(!empty($chapter['pop_quiz']))
                                    <span class="quiz-pill">{{ !empty($chapter['pop_quiz']['is_passed']) ? __('ui.course.pop_quiz_passed') : __('ui.course.pop_quiz_gate') }}</span>
                                @endif
                            </article>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

</div>

<aside class="qa-drawer" id="qaDrawer">
    <h3 style="margin:0;">{{ __('ui.course.qa_course') }}</h3>
    <p style="margin:6px 0 0;color:#9db1d6;font-size:13px;">{{ __('ui.course.qa_course_text', ['course' => $roadmapTitle]) }}</p>
    <div class="qa-list">
        @forelse(($roadmapQuestions ?? []) as $qa)
            <article class="qa-item">
                <div class="qa-item-head">
                    <span>{{ $roadmapQuestionUsers[$qa->user_id] ?? __('ui.course.student') }}</span>
                    <span>{{ $qa->chapter_number ? __('ui.course.chapter').' '.$qa->chapter_number : __('ui.course.general') }}</span>
                </div>
                <div style="font-size:14px;line-height:1.55;">{{ $qa->question_text }}</div>
                @if(!empty($qa->answer_text))
                    <div style="margin-top:6px;color:#c9e6ff;font-size:13px;"><strong>{{ __('ui.course.lecturer_answer') }}:</strong> {{ $qa->answer_text }}</div>
                @endif
            </article>
        @empty
            <article class="qa-item">{{ __('ui.course.no_questions') }}</article>
        @endforelse
    </div>
</aside>

<div class="modal-overlay" id="attendanceModal">
    <div class="attendance-modal">
        <h3>{{ __('ui.course.consistent_mode_heading') }}</h3>
        <p>{{ __('ui.course.consistent_mode_text') }}</p>
        <label for="attendanceTargetInput">{{ __('ui.course.target_chapters_per_day') }}</label>
        <input id="attendanceTargetInput" type="number" min="1" max="{{ count($chapters ?? []) }}" value="{{ max(1, (int)($attendance['target_chapters'] ?? 1)) }}">
        <p id="attendanceProgressText">
            Hari ini: <strong>{{ (int)($attendance['today_completed'] ?? 0) }}/{{ max(1, (int)($attendance['target_chapters'] ?? 1)) }}</strong>
            @if(!empty($attendance['today_attended']))
                {{ __('ui.course.attendance_recorded') }}
            @else
                {{ __('ui.course.attendance_not_recorded') }}
            @endif
        </p>
        <div class="attendance-modal-actions">
            <button class="modal-btn ghost" type="button" id="attendanceDisableBtn">{{ __('ui.course.turn_off') }}</button>
            <div style="display:flex; gap:10px;">
                <button class="modal-btn ghost" type="button" id="attendanceCloseBtn">{{ __('ui.course.close') }}</button>
                <button class="modal-btn primary" type="button" id="attendanceSaveBtn">{{ __('ui.course.save_target') }}</button>
            </div>
        </div>
        <div class="attendance-feedback" id="attendanceFeedback"></div>
    </div>
</div>

@php
    $roadmapUi = [
        'inactive' => __('ui.course.inactive'),
        'attendanceCounted' => __('ui.course.attendance_counted'),
        'targetNotMet' => __('ui.course.target_not_met'),
        'chapterToday' => __('ui.course.chapter_today', ['completed' => '__completed__', 'target' => '__target__']),
        'attendanceRecorded' => __('ui.course.attendance_recorded'),
        'attendanceNotRecorded' => __('ui.course.attendance_not_recorded'),
        'savingAttendance' => __('ui.course.saving_attendance'),
        'failedConsistentMode' => __('ui.course.failed_consistent_mode'),
        'savedConsistentMode' => __('ui.course.saved_consistent_mode'),
        'errorConsistentMode' => __('ui.course.error_consistent_mode'),
    ];
@endphp

<script>
    const timeline = document.getElementById('timeline');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');
    const favoriteBtn = document.getElementById('favoriteBtn');
    const profileMenu = document.getElementById('profileMenuRoadmapFrontend');
    const profileTrigger = document.getElementById('profileTriggerRoadmapFrontend');
    const qaDrawer = document.getElementById('qaDrawer');
    const qaToggleLink = document.getElementById('qaToggleLink');
    const attendanceTrigger = document.getElementById('attendanceTrigger');
    const attendanceModal = document.getElementById('attendanceModal');
    const attendanceTargetInput = document.getElementById('attendanceTargetInput');
    const attendanceSaveBtn = document.getElementById('attendanceSaveBtn');
    const attendanceDisableBtn = document.getElementById('attendanceDisableBtn');
    const attendanceCloseBtn = document.getElementById('attendanceCloseBtn');
    const attendanceFeedback = document.getElementById('attendanceFeedback');
    const attendanceSummaryText = document.getElementById('attendanceSummaryText');
    const attendanceProgressText = document.getElementById('attendanceProgressText');
    const roadmapUi = @json($roadmapUi);
    let attendanceState = {!! $attendanceJson !!};
    let holdTimer = null;

    timeline.addEventListener('wheel', (event) => {
        if (Math.abs(event.deltaY) > Math.abs(event.deltaX)) {
            event.preventDefault();
            timeline.scrollLeft += event.deltaY;
        }
    }, { passive: false });

    function startHoldScroll(direction) {
        stopHoldScroll();
        holdTimer = setInterval(() => {
            timeline.scrollLeft += 14 * direction;
        }, 16);
    }

    function stopHoldScroll() {
        if (holdTimer) {
            clearInterval(holdTimer);
            holdTimer = null;
        }
    }

    function bindHold(button, direction) {
        button.addEventListener('mousedown', () => startHoldScroll(direction));
        button.addEventListener('touchstart', (event) => {
            event.preventDefault();
            startHoldScroll(direction);
        }, { passive: false });
    }

    bindHold(scrollLeftBtn, -1);
    bindHold(scrollRightBtn, 1);
    window.addEventListener('mouseup', stopHoldScroll);
    window.addEventListener('mouseleave', stopHoldScroll);
    window.addEventListener('touchend', stopHoldScroll);
    window.addEventListener('touchcancel', stopHoldScroll);

    favoriteBtn.addEventListener('click', async () => {
        try {
            const response = await fetch('{{ route('courses.favorite', 'frontend-craft') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const active = !!data.is_favorite;
            favoriteBtn.classList.toggle('active', active);
            favoriteBtn.setAttribute('aria-pressed', String(active));
            favoriteBtn.textContent = active ? '\u2605' : '\u2606';
        } catch (error) {
            // Keep UI stable if request fails.
        }
    });

    if (profileMenu && profileTrigger) {
        profileTrigger.addEventListener('click', function (event) {
            if (!profileMenu.classList.contains('open')) {
                event.preventDefault();
                profileMenu.classList.add('open');
                profileTrigger.setAttribute('aria-expanded', 'true');
            }
        });

        document.addEventListener('click', function (event) {
            if (!profileMenu.contains(event.target)) {
                profileMenu.classList.remove('open');
                profileTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (qaDrawer && qaToggleLink) {
        qaToggleLink.addEventListener('click', function (event) {
            event.preventDefault();
            qaDrawer.classList.toggle('open');
        });

        document.addEventListener('click', function (event) {
            if (!qaDrawer.contains(event.target) && event.target !== qaToggleLink) {
                qaDrawer.classList.remove('open');
            }
        });
    }

    function renderAttendanceState() {
        const enabled = !!attendanceState.enabled;
        attendanceTrigger?.classList.toggle('active', enabled);

        if (attendanceSummaryText) {
            attendanceSummaryText.textContent = enabled
                ? `${roadmapUi.chapterToday.replace('__completed__', attendanceState.today_completed).replace('__target__', attendanceState.target_chapters)} ${attendanceState.today_attended ? '- ' + roadmapUi.attendanceCounted : '- ' + roadmapUi.targetNotMet}`
                : roadmapUi.inactive;
        }

        if (attendanceProgressText) {
            attendanceProgressText.innerHTML = `{{ __('ui.course.current_day_progress', ['completed' => '__completed__', 'target' => '__target__', 'status' => '__status__']) }}`.replace('__completed__', attendanceState.today_completed).replace('__target__', attendanceState.target_chapters).replace('__status__', attendanceState.today_attended ? roadmapUi.attendanceRecorded : roadmapUi.attendanceNotRecorded);
        }

        if (attendanceTargetInput) {
            attendanceTargetInput.value = attendanceState.target_chapters || 1;
        }
    }

    async function updateConsistentMode(enabled) {
        if (!attendanceTargetInput) {
            return;
        }

        attendanceFeedback.textContent = roadmapUi.savingAttendance;
        const formData = new FormData();
        formData.append('enabled', enabled ? '1' : '0');
        formData.append('target_chapters', attendanceTargetInput.value || '1');

        try {
            const response = await fetch('{{ route('courses.consistent-mode.update', 'frontend-craft') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            if (!response.ok) {
                attendanceFeedback.textContent = data.message || roadmapUi.failedConsistentMode;
                return;
            }

            attendanceState = data.attendance;
            attendanceFeedback.textContent = data.message || roadmapUi.savedConsistentMode;
            renderAttendanceState();
        } catch (error) {
            attendanceFeedback.textContent = roadmapUi.errorConsistentMode;
        }
    }

    attendanceTrigger?.addEventListener('click', () => {
        attendanceModal?.classList.add('open');
        renderAttendanceState();
        attendanceFeedback.textContent = '';
    });

    attendanceCloseBtn?.addEventListener('click', () => {
        attendanceModal?.classList.remove('open');
    });

    attendanceModal?.addEventListener('click', (event) => {
        if (event.target === attendanceModal) {
            attendanceModal.classList.remove('open');
        }
    });

    attendanceSaveBtn?.addEventListener('click', () => updateConsistentMode(true));
    attendanceDisableBtn?.addEventListener('click', () => updateConsistentMode(false));
    renderAttendanceState();
</script>
@include('partials.student-chatbot')
</body>
</html>
