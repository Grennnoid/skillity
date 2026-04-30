<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.pathfinder.page_title') }}</title>
    <style>
        :root {
            --bg: #050913;
            --panel: rgba(11, 18, 34, 0.88);
            --panel-2: rgba(13, 22, 42, 0.84);
            --line: rgba(160, 181, 222, 0.24);
            --line-strong: rgba(121, 240, 212, 0.42);
            --text: #eef4ff;
            --muted: #99add2;
            --primary: #67d7ff;
            --secondary: #79f0d4;
            --shadow: 0 24px 56px rgba(0, 0, 0, 0.46);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--text);
            font-family: "Segoe UI", "Inter", Arial, sans-serif;
            background:
                radial-gradient(980px 520px at 18% -10%, rgba(56, 102, 194, 0.28), transparent 62%),
                radial-gradient(920px 500px at 86% -16%, rgba(121, 240, 212, 0.17), transparent 60%),
                linear-gradient(180deg, #04070f 0%, #060a14 100%);
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .page {
            width: min(1180px, 94vw);
            margin: 0 auto;
            min-height: 100vh;
            padding: 18px 0 20px;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .brand {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .brand span {
            display: inline-block;
            width: 24px;
            height: 2px;
            background: var(--text);
            margin-left: 8px;
            opacity: 0.72;
            transform: translateY(-6px);
        }

        .top-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .ghost-link,
        .ghost-button,
        .primary-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 14px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .ghost-link,
        .ghost-button {
            color: #d8e5ff;
            border: 1px solid var(--line);
            background: rgba(9, 15, 29, 0.78);
        }

        .primary-button {
            color: #041220;
            border: 0;
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            box-shadow: 0 14px 28px rgba(75, 221, 231, 0.16);
        }

        .shell {
            display: grid;
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            gap: 18px;
            flex: 1;
            min-height: 0;
        }

        .intro,
        .wizard,
        .results {
            border: 1px solid var(--line);
            border-radius: 24px;
            background: var(--panel);
            box-shadow: var(--shadow);
        }

        .intro {
            padding: 24px;
            position: sticky;
            top: 20px;
            align-self: start;
            overflow: hidden;
            height: 100%;
        }

        .intro::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 82% 12%, rgba(103, 215, 255, 0.18), transparent 30%),
                radial-gradient(circle at 0% 100%, rgba(121, 240, 212, 0.12), transparent 38%);
            pointer-events: none;
        }

        .intro-content {
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            color: #c8f6ec;
            border: 1px solid rgba(121, 240, 212, 0.32);
            border-radius: 999px;
            padding: 7px 12px;
            background: rgba(13, 37, 43, 0.34);
        }

        .eyebrow::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            box-shadow: 0 0 12px rgba(103, 215, 255, 0.78);
        }

        .intro h1 {
            margin: 18px 0 12px;
            font-size: clamp(34px, 5vw, 50px);
            line-height: 1.02;
            letter-spacing: -0.9px;
        }

        .intro p {
            margin: 0 0 12px;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .mini-stats {
            display: grid;
            gap: 12px;
            margin-top: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mini-card {
            padding: 14px;
            border: 1px solid rgba(160, 181, 222, 0.16);
            border-radius: 18px;
            background: rgba(12, 20, 37, 0.62);
        }

        .mini-card strong {
            display: block;
            font-size: 24px;
            margin-bottom: 4px;
        }

        .mini-card span {
            color: var(--muted);
            font-size: 13px;
        }

        .wizard-wrap {
            display: grid;
            gap: 0;
            min-height: 0;
        }

        .wizard {
            padding: 24px;
            overflow: hidden;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .progress-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .progress-head strong {
            font-size: 14px;
        }

        .progress-bar {
            height: 9px;
            border-radius: 999px;
            background: rgba(143, 175, 230, 0.18);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 20%;
            border-radius: inherit;
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            box-shadow: 0 0 18px rgba(103, 215, 255, 0.26);
            transition: width 0.3s ease;
        }

        .steps {
            position: relative;
            min-height: 0;
            flex: 1;
            display: flex;
        }

        .step-pane {
            display: none;
            animation: fadeSlide 0.28s ease;
            width: 100%;
        }

        .step-pane.active {
            display: flex;
            flex-direction: column;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-pane h2 {
            margin: 0 0 16px;
            font-size: clamp(24px, 3.2vw, 36px);
            letter-spacing: -0.5px;
        }

        .choice-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: stretch;
        }

        .choice-grid.single {
            grid-template-columns: 1fr;
        }

        .choice {
            position: relative;
            display: block;
            min-width: 0;
        }

        .choice input {
            position: absolute;
            inset: 0;
            opacity: 0;
            pointer-events: none;
        }

        .choice-card {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            width: 100%;
            height: 100%;
            min-height: 146px;
            padding: 16px;
            border: 1px solid rgba(160, 181, 222, 0.18);
            border-radius: 18px;
            background: var(--panel-2);
            cursor: pointer;
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .choice-card:hover {
            transform: translateY(-2px);
            border-color: rgba(121, 240, 212, 0.34);
        }

        .choice input:checked + .choice-card {
            border-color: var(--line-strong);
            background: linear-gradient(180deg, rgba(17, 31, 55, 0.96), rgba(13, 24, 43, 0.96));
            box-shadow: 0 0 0 1px rgba(121, 240, 212, 0.12), 0 18px 28px rgba(0, 0, 0, 0.22);
        }

        .choice-card strong {
            display: block;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .choice-card p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .wizard-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
        }

        .wizard-actions .action-stack {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .hidden-submit {
            display: none;
        }

        .results {
            padding: 22px;
            display: none;
        }

        .results-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .results-head h3 {
            margin: 0;
            font-size: 24px;
        }

        .results-head p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .results-grid {
            display: grid;
            gap: 14px;
        }

        .result-card {
            padding: 18px;
            border: 1px solid rgba(160, 181, 222, 0.18);
            border-radius: 20px;
            background: rgba(12, 21, 40, 0.78);
        }

        .result-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .result-top small {
            display: inline-flex;
            align-items: center;
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(103, 215, 255, 0.12);
            color: #b7efff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .score-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 999px;
            background: rgba(121, 240, 212, 0.12);
            color: #d9fff6;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .result-card h4 {
            margin: 0 0 6px;
            font-size: 28px;
            line-height: 1.06;
            letter-spacing: -0.4px;
        }

        .result-card p {
            margin: 0 0 12px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .reason-list {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .reason-pill {
            display: inline-flex;
            align-items: center;
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(160, 181, 222, 0.08);
            color: #dce8ff;
            font-size: 12px;
        }

        .result-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .empty-results {
            padding: 18px;
            border-radius: 18px;
            border: 1px dashed rgba(160, 181, 222, 0.28);
            color: var(--muted);
        }

        .wizard.completed .progress-head,
        .wizard.completed .progress-bar,
        .wizard.completed .steps,
        .wizard.completed .wizard-actions {
            display: none;
        }

        .wizard.completed .results {
            display: block;
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
        }

        @media (max-width: 960px) {
            body {
                overflow: auto;
            }

            .page {
                min-height: auto;
            }

            .shell {
                grid-template-columns: 1fr;
            }

            .intro {
                position: static;
            }
        }

        @media (max-width: 680px) {
            .page {
                width: min(100%, calc(100vw - 18px));
                padding-top: 18px;
            }

            .topbar,
            .wizard-actions,
            .results-head,
            .result-top {
                flex-direction: column;
                align-items: stretch;
            }

            .choice-grid {
                grid-template-columns: 1fr;
            }

            .wizard,
            .intro,
            .results {
                padding: 18px;
                border-radius: 20px;
            }

            .steps {
                min-height: 0;
            }

            .result-card h4 {
                font-size: 24px;
            }

            .mini-stats {
                grid-template-columns: 1fr;
            }
        }

        @media (max-height: 860px) and (min-width: 961px) {
            .choice-card {
                min-height: 126px;
                padding: 14px;
            }

            .choice-card strong {
                font-size: 17px;
                margin-bottom: 6px;
            }

            .choice-card p {
                font-size: 13px;
                line-height: 1.5;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <header class="topbar">
        <div class="brand">{{ __('ui.course.skillify') }}<span></span></div>
        <div class="top-actions">
            <a class="ghost-link" href="{{ route('student.dashboard') }}">{{ __('ui.pathfinder.back_to_dashboard') }}</a>
            <form action="{{ route('student.pathfinder.save') }}" method="POST" style="margin:0;">
                @csrf
                <input type="hidden" name="action" value="skip">
                <button class="ghost-button" type="submit">{{ __('ui.pathfinder.skip') }}</button>
            </form>
        </div>
    </header>

    @if(session('success'))
        <div style="margin-bottom: 14px; border:1px solid rgba(73,214,139,.35); border-radius:16px; background:rgba(12,42,24,.52); color:#b4f2cc; padding:12px 14px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom: 14px; border:1px solid rgba(251,113,133,.35); border-radius:16px; background:rgba(80,21,33,.52); color:#ffd5db; padding:12px 14px;">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="shell">
        <aside class="intro">
            <div class="intro-content">
                <span class="eyebrow">{{ __('ui.pathfinder.eyebrow') }}</span>
                @if($showWelcome)
                    <div style="margin-top:14px; padding:12px 14px; border-radius:16px; border:1px solid rgba(121,240,212,0.24); background:rgba(10,28,35,0.42); color:#d8fff7; font-size:14px; line-height:1.6;">
                        {{ __('ui.pathfinder.welcome_note') }}
                    </div>
                @endif
                <h1>{{ __('ui.pathfinder.welcome_title') }}</h1>
                <p>{{ __('ui.pathfinder.welcome_text') }}</p>
                <p>{{ __('ui.pathfinder.optional_text') }}</p>

                <div class="mini-stats">
                    <div class="mini-card">
                        <strong>{{ $pathfinderCourseCount }}</strong>
                        <span>{{ __('ui.landing.available_courses') }}</span>
                    </div>
                    <div class="mini-card">
                        <strong>3</strong>
                        <span>{{ __('ui.pathfinder.top_suggestions') }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <div class="wizard-wrap">
            <section class="wizard {{ $pathfinderTopRecommendation ? 'completed' : '' }}" id="wizardShell">
                <div class="progress-head">
                    <strong id="stepLabel">{{ __('ui.pathfinder.progress', ['current' => 1, 'total' => 5]) }}</strong>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>

                <form method="POST" action="{{ route('student.pathfinder.save') }}" id="pathfinderForm">
                    @csrf

                    <div class="steps">
                        <section class="step-pane active" data-step="1">
                            <h2>{{ __('ui.pathfinder.step_interest') }}</h2>
                            <div class="choice-grid">
                                @foreach($pathfinderSignals as $signalKey => $signal)
                                    <label class="choice">
                                        <input
                                            type="radio"
                                            name="discovery_signal"
                                            value="{{ $signalKey }}"
                                            @checked(($pathfinderAnswers['discovery_signal'] ?? '') === $signalKey)
                                            required
                                        >
                                        <span class="choice-card">
                                            <strong>{{ $signal['title'] }}</strong>
                                            <p>{{ $signal['text'] }}</p>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="step-pane" data-step="2">
                            <h2>{{ __('ui.pathfinder.step_goal') }}</h2>
                            <div class="choice-grid">
                                @foreach(['career', 'business', 'portfolio', 'explore'] as $goal)
                                    <label class="choice">
                                        <input type="radio" name="goal" value="{{ $goal }}" @checked(($pathfinderAnswers['goal'] ?? '') === $goal) required>
                                        <span class="choice-card">
                                            <strong>{{ __('ui.pathfinder.goal_'.$goal) }}</strong>
                                            <p>{{ __('ui.pathfinder.goal_'.$goal.'_text') }}</p>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="step-pane" data-step="3">
                            <h2>{{ __('ui.pathfinder.step_experience') }}</h2>
                            <div class="choice-grid">
                                @foreach(['beginner', 'intermediate', 'advanced'] as $level)
                                    <label class="choice">
                                        <input type="radio" name="experience_level" value="{{ $level }}" @checked(($pathfinderAnswers['experience_level'] ?? '') === $level) required>
                                        <span class="choice-card">
                                            <strong>{{ __('ui.pathfinder.experience_'.$level) }}</strong>
                                            <p>{{ __('ui.pathfinder.experience_'.$level.'_text') }}</p>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="step-pane" data-step="4">
                            <h2>{{ __('ui.pathfinder.step_pace') }}</h2>
                            <div class="choice-grid">
                                @foreach(['light', 'steady', 'intensive'] as $pace)
                                    <label class="choice">
                                        <input type="radio" name="study_pace" value="{{ $pace }}" @checked(($pathfinderAnswers['study_pace'] ?? '') === $pace) required>
                                        <span class="choice-card">
                                            <strong>{{ __('ui.pathfinder.pace_'.$pace) }}</strong>
                                            <p>{{ __('ui.pathfinder.pace_'.$pace.'_text') }}</p>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="step-pane" data-step="5">
                            <h2>{{ __('ui.pathfinder.step_style') }}</h2>
                            <div class="choice-grid">
                                @foreach(['hands_on', 'guided', 'theory'] as $style)
                                    <label class="choice">
                                        <input type="radio" name="learning_style" value="{{ $style }}" @checked(($pathfinderAnswers['learning_style'] ?? '') === $style) required>
                                        <span class="choice-card">
                                            <strong>{{ __('ui.pathfinder.style_'.$style) }}</strong>
                                            <p>{{ __('ui.pathfinder.style_'.$style.'_text') }}</p>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <div class="wizard-actions">
                        <button class="ghost-button" type="button" id="backBtn">{{ __('ui.pathfinder.back') }}</button>
                        <div class="action-stack">
                            <button class="ghost-button" type="button" id="nextBtn">{{ __('ui.pathfinder.next') }}</button>
                            <button class="primary-button hidden-submit" type="submit" id="submitBtn">
                                {{ !empty($pathfinderProfile?->completed_at) ? __('ui.pathfinder.save_again') : __('ui.pathfinder.see_matches') }}
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <section class="results">
                @if($pathfinderTopRecommendation)
                    <div class="results-head">
                        <div>
                            <h3>{{ __('ui.pathfinder.results_title') }}</h3>
                            <p>{{ __('ui.pathfinder.results_text') }}</p>
                        </div>
                        <button class="ghost-button" type="button" id="retakeBtn">{{ __('ui.pathfinder.retake') }}</button>
                    </div>

                    <div class="results-grid">
                        <article class="result-card">
                            <div class="result-top">
                                <small>{{ $pathfinderTopRecommendation['category'] }}</small>
                                <span class="score-pill">{{ __('ui.pathfinder.matched_for_you') }} · {{ $pathfinderTopRecommendation['match_score'] }}%</span>
                            </div>
                            <h4>{{ $pathfinderTopRecommendation['title'] }}</h4>
                            <p>{{ $pathfinderTopRecommendation['summary'] }}</p>
                            <div class="result-actions">
                                <a class="primary-button" href="{{ $pathfinderTopRecommendation['href'] }}">{{ __('ui.pathfinder.view_course') }}</a>
                                <a class="ghost-link" href="{{ $pathfinderTopRecommendation['roadmap_href'] }}">{{ __('ui.pathfinder.open_roadmap') }}</a>
                            </div>
                        </article>
                    </div>
                @else
                    <div class="empty-results">
                        <strong>{{ __('ui.pathfinder.empty_title') }}</strong>
                        <p style="margin:8px 0 0;">{{ __('ui.pathfinder.empty_text') }}</p>
                    </div>
                @endif
            </section>
        </div>
    </section>
</div>

<script>
    const panes = Array.from(document.querySelectorAll('.step-pane'));
    const backBtn = document.getElementById('backBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressFill = document.getElementById('progressFill');
    const stepLabel = document.getElementById('stepLabel');
    const wizardShell = document.getElementById('wizardShell');
    const retakeBtn = document.getElementById('retakeBtn');
    const totalSteps = panes.length;
    let activeStep = 0;

    const progressTemplate = @json(__('ui.pathfinder.progress', ['current' => '__current__', 'total' => '__total__']));

    function updateWizard() {
        panes.forEach((pane, index) => {
            pane.classList.toggle('active', index === activeStep);
        });

        const current = activeStep + 1;
        progressFill.style.width = `${(current / totalSteps) * 100}%`;
        stepLabel.textContent = progressTemplate
            .replace('__current__', String(current))
            .replace('__total__', String(totalSteps));
        backBtn.style.visibility = activeStep === 0 ? 'hidden' : 'visible';
        nextBtn.classList.toggle('hidden-submit', activeStep === totalSteps - 1);
        submitBtn.classList.toggle('hidden-submit', activeStep !== totalSteps - 1);
    }

    function currentPaneHasSelection() {
        const pane = panes[activeStep];
        return !!pane.querySelector('input[type="radio"]:checked');
    }

    nextBtn.addEventListener('click', function () {
        if (!currentPaneHasSelection()) {
            const target = panes[activeStep].querySelector('input[type="radio"]');
            if (target) {
                target.reportValidity();
            }
            return;
        }

        if (activeStep < totalSteps - 1) {
            activeStep += 1;
            updateWizard();
        }
    });

    backBtn.addEventListener('click', function () {
        if (activeStep > 0) {
            activeStep -= 1;
            updateWizard();
        }
    });

    if (retakeBtn) {
        retakeBtn.addEventListener('click', function () {
            wizardShell.classList.remove('completed');
        });
    }

    updateWizard();
</script>
</body>
</html>
