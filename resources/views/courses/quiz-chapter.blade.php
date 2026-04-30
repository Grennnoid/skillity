<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.course.chapter_page_title', ['course' => $course->title, 'chapter' => $chapter]) }}</title>
    <style>
        :root {
            --bg: #050914;
            --panel: rgba(11, 19, 35, 0.84);
            --line: rgba(154, 178, 225, 0.24);
            --text: #e9f1ff;
            --muted: #9db1d6;
            --accent: #45d0ff;
            --accent2: #7cf6d6;
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

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            border-bottom: 1px solid var(--line);
            background: rgba(8, 13, 24, 0.96);
            backdrop-filter: blur(10px);
        }

        .topbar-inner {
            width: min(1400px, 96vw);
            margin: 0 auto;
            padding: 10px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .brand-wrap {
            display: grid;
            gap: 3px;
            min-width: 0;
        }

        .brand-wrap strong {
            font-size: 20px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .brand-wrap span {
            font-size: 12px;
            color: var(--muted);
        }

        .top-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 6px 10px;
            color: #d7e8ff;
            text-decoration: none;
            font-size: 12px;
            background: rgba(12, 19, 35, 0.72);
        }

        .chip:hover { border-color: rgba(124, 246, 214, 0.52); }

        .wrap {
            width: min(1400px, 96vw);
            margin: 0 auto;
            padding: 14px 0 24px;
            display: grid;
            gap: 12px;
        }

        .tabs {
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 14px;
            padding: 0 10px;
            display: flex;
            gap: 4px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .tabs::-webkit-scrollbar { display: none; }

        .tab-btn {
            border: 0;
            background: transparent;
            color: #cfe0ff;
            font-size: 14px;
            padding: 12px 14px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            white-space: nowrap;
        }

        .tab-btn.active {
            color: #ffffff;
            border-color: var(--accent);
            font-weight: 700;
        }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 12px;
            align-items: start;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel);
            padding: 14px;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 34px;
            letter-spacing: -0.3px;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .meta-grid {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .stat {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(8, 14, 27, 0.72);
            padding: 10px;
            min-height: 86px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat strong {
            display: flex;
            align-items: flex-start;
            font-size: 17px;
            line-height: 1.3;
            min-height: 44px;
            margin-bottom: 4px;
        }

        .video-box {
            margin-top: 12px;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            background: rgba(7, 13, 26, 0.9);
        }

        video, iframe {
            width: 100%;
            min-height: 500px;
            border: 0;
            display: block;
            background: #04070f;
        }

        .placeholder {
            min-height: 420px;
            display: grid;
            place-items: center;
            color: var(--muted);
            font-size: 14px;
            padding: 16px;
            text-align: center;
        }

        .lesson-actions {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: space-between;
            align-items: center;
        }

        .left-actions,
        .right-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .desc {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
            color: #d8e6ff;
            line-height: 1.75;
            font-size: 14px;
        }

        .callout {
            margin-top: 12px;
            border: 1px solid rgba(124, 246, 214, 0.34);
            border-radius: 12px;
            background: rgba(10, 30, 33, 0.35);
            padding: 12px;
        }

        .callout h3 {
            margin: 0 0 6px;
            font-size: 16px;
        }

        .panel { display: none; }
        .panel.active { display: block; }

        .chapter-rail {
            position: sticky;
            top: 70px;
            max-height: calc(100vh - 86px);
            overflow: auto;
            scrollbar-width: none;
        }

        .chapter-rail::-webkit-scrollbar { display: none; }

        .chapter-list {
            display: grid;
            gap: 8px;
            margin-top: 10px;
        }

        .chapter-item {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(9, 15, 29, 0.82);
            padding: 10px;
            text-decoration: none;
            color: #dcedff;
            display: grid;
            gap: 4px;
        }

        .chapter-item.active {
            border-color: rgba(69, 208, 255, 0.72);
            background: linear-gradient(120deg, rgba(69, 208, 255, 0.2), rgba(124, 246, 214, 0.18));
        }

        .chapter-item.completed {
            border-color: rgba(246, 216, 122, 0.48);
            background: linear-gradient(135deg, rgba(255, 223, 136, 0.24), rgba(242, 173, 24, 0.16));
        }

        .chapter-item.locked {
            opacity: 0.42;
            pointer-events: none;
            filter: saturate(0.65);
        }

        .chapter-item small { color: var(--muted); font-size: 11px; }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: #b7c9ec;
            font-size: 12px;
        }

        .badge {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 11px;
        }

        .notes-box textarea {
            width: 100%;
            min-height: 180px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(8, 14, 27, 0.82);
            color: var(--text);
            padding: 10px;
            font-size: 13px;
            resize: vertical;
        }

        .qa-form {
            display: grid;
            gap: 8px;
            margin-top: 10px;
        }

        .qa-form input,
        .qa-form select,
        .qa-form textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(8, 14, 27, 0.82);
            color: var(--text);
            padding: 10px;
            font-size: 13px;
            font-family: inherit;
        }

        .qa-form select {
            appearance: none;
            padding-right: 34px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23d4e3ff' d='M6 8 0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 12px 8px;
        }

        .qa-form textarea {
            min-height: 90px;
            resize: vertical;
        }

        .qa-form button {
            border: 0;
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            font-weight: 700;
            color: #2c210f;
            background: linear-gradient(120deg, var(--gold), var(--gold-2));
            width: fit-content;
        }

        .qa-list {
            margin-top: 12px;
            display: grid;
            gap: 8px;
        }

        .qa-item {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(8, 14, 27, 0.76);
            padding: 10px;
        }

        .qa-item-head {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 12px;
            color: var(--muted);
        }

        .quiz-gate-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 16, 0.62);
            backdrop-filter: blur(10px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 100;
        }

        .quiz-gate-overlay.open { display: flex; }

        .quiz-gate-modal {
            width: min(480px, 100%);
            border: 1px solid rgba(255, 107, 125, 0.28);
            border-radius: 18px;
            background: rgba(10, 14, 27, 0.98);
            padding: 18px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.5);
        }

        .quiz-gate-modal h3 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .quiz-gate-modal p {
            margin: 0 0 12px;
            color: var(--muted);
            line-height: 1.6;
        }

        .quiz-gate-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .quiz-gate-btn {
            border: 0;
            border-radius: 999px;
            text-decoration: none;
            padding: 11px 16px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .quiz-gate-btn.primary {
            color: #fff4f6;
            background: linear-gradient(135deg, #ff6b7d, #ff9f68);
        }

        .quiz-gate-btn.ghost {
            color: #dce9ff;
            background: rgba(12, 18, 34, 0.84);
            border: 1px solid var(--line);
        }

        @media (max-width: 1080px) {
            .content-grid { grid-template-columns: 1fr; }
            .chapter-rail { position: static; max-height: none; }
            .meta-grid { grid-template-columns: 1fr 1fr; }
            video, iframe { min-height: 320px; }
        }

        @media (max-width: 700px) {
            .meta-grid { grid-template-columns: 1fr; }
            .brand-wrap strong { font-size: 18px; }
        }
    </style>
</head>
<body>
@php
    $embedVideoUrl = null;
    $origin = request()->getSchemeAndHttpHost();
    if (!empty($lesson?->video_url)) {
        $rawUrl = trim($lesson->video_url);
        $embedVideoUrl = $rawUrl;

        if (str_contains($rawUrl, 'youtube.com/watch?v=')) {
            $parts = parse_url($rawUrl);
            parse_str($parts['query'] ?? '', $query);
            if (!empty($query['v'])) {
                $embedVideoUrl = 'https://www.youtube.com/embed/'.$query['v'].'?enablejsapi=1&rel=0&playsinline=1&origin='.urlencode($origin);
            }
        } elseif (str_contains($rawUrl, 'youtu.be/')) {
            $path = trim((string) parse_url($rawUrl, PHP_URL_PATH), '/');
            $videoId = explode('/', $path)[0] ?? '';
            if ($videoId !== '') {
                $embedVideoUrl = 'https://www.youtube.com/embed/'.$videoId.'?enablejsapi=1&rel=0&playsinline=1&origin='.urlencode($origin);
            }
        } elseif (str_contains($rawUrl, 'youtube.com/shorts/')) {
            $path = trim((string) parse_url($rawUrl, PHP_URL_PATH), '/');
            $videoId = explode('/', str_replace('shorts/', '', $path))[0] ?? '';
            if ($videoId !== '') {
                $embedVideoUrl = 'https://www.youtube.com/embed/'.$videoId.'?enablejsapi=1&rel=0&playsinline=1&origin='.urlencode($origin);
            }
        }
    }

    $hasCurrentVideo = (bool) ($lesson && ($lesson->video_path || $lesson->video_url));
    $estimatedTime = $course->duration_text ?: ($hasCurrentVideo ? __('ui.course.video_lesson_ready') : __('ui.course.waiting_for_mentor_update'));
    $mentorLabel = $chapterMentorName ?? ($course->mentor_name ?? __('ui.course.mentor_not_assigned'));
    $chapterUi = [
        'completed' => __('ui.course.completed'),
        'locked' => __('ui.course.locked'),
        'inProgress' => __('ui.course.in_progress'),
        'openingNextChapter' => __('ui.course.opening_next_chapter'),
    ];
@endphp
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand-wrap">
            <strong>{{ $course->title }} - {{ __('ui.course.chapter') }} {{ $chapter }}</strong>
            <span>{{ __('ui.course.learning_mode') }} • {{ __('ui.course.chapters_with_video', ['ready' => $videoReadyCount, 'total' => $chaptersCount]) }}</span>
        </div>
        <div class="top-actions">
            <a class="chip" href="{{ $roadmapUrl }}">{{ __('ui.course.back_to_roadmap') }}</a>
            <a class="chip" href="{{ $dashboardUrl }}">{{ __('ui.course.dashboard') }}</a>
        </div>
    </div>
</header>

<div class="wrap">
    <nav class="tabs">
        <button class="tab-btn active" data-tab="course-content">{{ __('ui.course.course_content') }}</button>
        <button class="tab-btn" data-tab="overview">{{ __('ui.course.overview') }}</button>
        <button class="tab-btn" data-tab="qa">Q&A</button>
        <button class="tab-btn" data-tab="notes">{{ __('ui.course.notes') }}</button>
    </nav>

    <div class="content-grid">
        <main>
            <section class="card panel active" id="panel-course-content">
                <h1>{{ $lessonTitle ?? ("Chapter {$chapter}") }}</h1>
                @if(!empty($course->tagline))
                    <p class="muted">{{ $course->tagline }}</p>
                @endif

                <div class="meta-grid">
                    <div class="stat"><strong>{{ ucfirst($course->difficulty ?? 'beginner') }}</strong><span class="muted">{{ __('ui.course.skill_level') }}</span></div>
                    <div class="stat"><strong>{{ $chaptersCount }}</strong><span class="muted">{{ __('ui.course.total_chapters') }}</span></div>
                    <div class="stat"><strong>{{ $estimatedTime }}</strong><span class="muted">{{ __('ui.course.estimated_time') }}</span></div>
                    <div class="stat"><strong>{{ $mentorLabel }}</strong><span class="muted">{{ __('ui.course.mentor') }}</span></div>
                </div>

                <div class="video-box">
                    @if($lesson && $lesson->video_path)
                        <video controls>
                            <source src="{{ asset('storage/' . $lesson->video_path) }}" type="video/mp4">
                        </video>
                    @elseif($lesson && $lesson->video_url)
                        <iframe id="lessonYoutubePlayer" src="{{ $embedVideoUrl }}" allowfullscreen loading="lazy"></iframe>
                    @else
                        <div class="placeholder">{{ __('ui.course.video_not_uploaded') }}</div>
                    @endif
                </div>

                <div class="lesson-actions">
                    <div class="left-actions">
                        @if($hasPrevious)
                            <a class="chip" href="{{ $chapterPrevUrl }}">&larr; {{ __('ui.course.chapter') }} {{ $chapter - 1 }}</a>
                        @endif
                        <a class="chip" href="{{ $roadmapUrl }}">{{ __('ui.course.all_chapters') }}</a>
                        @if($hasNext)
                            <a class="chip" href="{{ $chapterNextUrl }}">{{ __('ui.course.chapter') }} {{ $chapter + 1 }} &rarr;</a>
                        @endif
                    </div>
                    <div class="right-actions">
                        <span class="badge" id="completionBadge">{{ !empty($isCurrentChapterCompleted) ? __('ui.course.completed') : ($hasCurrentVideo ? __('ui.course.watching') : __('ui.course.ready_to_unlock_next')) }}</span>
                    </div>
                </div>

                <div class="desc">
                    {!! nl2br(e($lessonDescription ?? __('ui.course.chapter_default_description'))) !!}
                </div>

                <div class="callout">
                    <h3>{{ __('ui.course.schedule_learning_time') }}</h3>
                    <p class="muted">{{ __('ui.course.schedule_learning_time_text') }}</p>
                    <p class="muted" style="margin-top:8px;" id="attendanceProgressLabel">
                        @if(!empty($attendance['enabled']))
                            {{ __('ui.course.consistent_mode_active', ['completed' => (int)($attendance['today_completed'] ?? 0), 'target' => max(1, (int)($attendance['target_chapters'] ?? 1))]) }} {{ !empty($attendance['today_attended']) ? '- '.__('ui.course.attendance_recorded') : '- '.__('ui.course.attendance_not_recorded') }}
                        @else
                            {{ __('ui.course.attendance_not_active') }}
                        @endif
                    </p>
                </div>
            </section>

            <section class="card panel" id="panel-overview">
                <h1>{{ __('ui.course.overview_title', ['course' => $course->title]) }}</h1>
                <p class="muted">{{ $course->about ?: __('ui.course.chapter_builder_missing') }}</p>
                <div class="meta-grid">
                    <div class="stat"><strong>{{ ucfirst($course->difficulty ?? 'beginner') }}</strong><span class="muted">{{ __('ui.course.level') }}</span></div>
                    <div class="stat"><strong>{{ $course->category ?? __('ui.course.general') }}</strong><span class="muted">{{ __('ui.course.category') }}</span></div>
                    <div class="stat"><strong>{{ $chaptersCount }}</strong><span class="muted">{{ __('ui.course.total_chapters') }}</span></div>
                    <div class="stat"><strong>{{ \Illuminate\Support\Carbon::parse($course->updated_at ?? $course->created_at)->format('d M Y') }}</strong><span class="muted">{{ __('ui.course.updated') }}</span></div>
                </div>
            </section>

            <section class="card panel" id="panel-qa">
                <h1>{{ __('ui.course.qa_title') }}</h1>
                <p class="muted">{{ __('ui.course.qa_text') }}</p>
                <form class="qa-form" action="{{ $qaPostUrl }}" method="POST">
                    @csrf
                    <input type="hidden" name="chapter_number" value="{{ $chapter }}">
                    <textarea name="question_text" placeholder="{{ __('ui.course.qa_placeholder') }}" required></textarea>
                    <button type="submit">{{ __('ui.course.send_question') }}</button>
                </form>
                <div class="qa-list">
                    @forelse($qaItems as $qa)
                        <article class="qa-item">
                            <div class="qa-item-head">
                                <span>{{ $qaUsers[$qa->user_id] ?? __('ui.course.student') }}</span>
                                <span>{{ $qa->chapter_number ? __('ui.course.chapter').' '.$qa->chapter_number : __('ui.course.general') }}</span>
                            </div>
                            <div>{{ $qa->question_text }}</div>
                            @if(!empty($qa->answer_text))
                                <div style="margin-top:6px;color:#cde6ff;"><strong>{{ __('ui.course.lecturer_answer') }}:</strong> {{ $qa->answer_text }}</div>
                            @endif
                        </article>
                    @empty
                        <article class="qa-item">{{ __('ui.course.no_questions_chapter') }}</article>
                    @endforelse
                </div>
            </section>

            <section class="card panel notes-box" id="panel-notes">
                <h1>{{ __('ui.course.notes_title') }}</h1>
                <p class="muted">{{ __('ui.course.notes_text') }}</p>
                <textarea id="notesField" placeholder="{{ __('ui.course.notes_placeholder') }}"></textarea>
            </section>
        </main>

        <aside class="card chapter-rail">
            <h3 style="margin:0;">{{ __('ui.course.course_content') }}</h3>
            <p class="muted" style="margin:6px 0 0;">{{ __('ui.course.progress_completed', ['completed' => '0', 'total' => $chaptersCount]) }}</p>
            <div class="chapter-list">
                @foreach($chapterItems as $item)
                    <a class="chapter-item {{ $item['number'] === $chapter ? 'active' : '' }} {{ !empty($item['is_completed']) ? 'completed' : '' }} {{ !empty($item['is_locked']) ? 'locked' : '' }}" href="{{ $item['href'] ?? '#' }}" data-chapter-number="{{ $item['number'] }}" data-locked="{{ !empty($item['is_locked']) ? 'true' : 'false' }}">
                        <div class="status-row">
                            <strong>{{ __('ui.course.chapter') }} {{ str_pad((string) $item['number'], 2, '0', STR_PAD_LEFT) }}</strong>
                            <span class="badge">{{ $item['has_video'] ? __('ui.course.video') : __('ui.course.no_video') }}</span>
                        </div>
                        <span>{{ $item['title'] }}</span>
                        <small class="chapter-complete-text">
                            @if(!empty($item['is_completed']))
                                {{ __('ui.course.completed') }}
                            @elseif(!empty($item['is_locked']))
                                {{ __('ui.course.locked') }}
                            @else
                                {{ __('ui.course.in_progress') }}
                            @endif
                        </small>
                    </a>
                @endforeach
            </div>
        </aside>
    </div>
</div>

@if(!empty($pendingPopQuizPrompt))
    <div class="quiz-gate-overlay open" id="quizGateOverlay">
        <div class="quiz-gate-modal">
            <h3>{{ __('ui.course.pop_quiz_unlocked') }}</h3>
            <p>{{ __('ui.course.pop_quiz_unlock_text', ['chapter' => $pendingPopQuizPrompt['placement_after_chapter'], 'count' => $pendingPopQuizPrompt['question_count']]) }}</p>
            <div class="quiz-gate-actions">
                <a class="quiz-gate-btn primary" href="{{ $pendingPopQuizPrompt['take_quiz_url'] }}">{{ __('ui.course.do_quiz_now') }}</a>
                <a class="quiz-gate-btn ghost" href="{{ $dashboardUrl }}">{{ __('ui.course.back_to_dashboard') }}</a>
                <a class="quiz-gate-btn ghost" href="{{ $roadmapUrl }}">{{ __('ui.course.back_to_roadmap') }}</a>
            </div>
        </div>
    </div>
@endif

<script>
    const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
    const panels = {
        'course-content': document.getElementById('panel-course-content'),
        'overview': document.getElementById('panel-overview'),
        'qa': document.getElementById('panel-qa'),
        'notes': document.getElementById('panel-notes'),
    };

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const key = button.dataset.tab;
            tabButtons.forEach((b) => b.classList.remove('active'));
            Object.values(panels).forEach((panel) => panel.classList.remove('active'));
            button.classList.add('active');
            panels[key]?.classList.add('active');
        });
    });

    const currentChapter = {{ (int) $chapter }};
    const notesKey = @json($notesStorageKey);
    const completionUrl = @json($completionUrl);
    const roadmapUrl = @json($roadmapUrl);
    const chapterNextUrl = @json($chapterNextUrl);
    const hasCurrentVideo = {{ $hasCurrentVideo ? 'true' : 'false' }};
    let currentCompleted = {{ !empty($isCurrentChapterCompleted) ? 'true' : 'false' }};
    let navigatingChapter = false;

    const completionBadge = document.getElementById('completionBadge');
    const progressText = document.getElementById('progressText');
    const chapterLinks = Array.from(document.querySelectorAll('.chapter-item'));
    const notesField = document.getElementById('notesField');
    const videoElement = document.querySelector('video');
    const iframeElement = document.querySelector('iframe');
    const quizGateOverlay = document.getElementById('quizGateOverlay');
    const chapterUi = @json($chapterUi);
    const progressTemplate = @json(__('ui.course.progress_completed', ['completed' => '__completed__', 'total' => $chaptersCount]));
    let youtubeFallbackTimer = null;

    function updateProgressSummary() {
        const completedItems = chapterLinks.filter((link) => link.classList.contains('completed'));
        progressText.textContent = progressTemplate.replace('__completed__', completedItems.length).replace('__total__', '{{ $chaptersCount }}');
    }

    function markChapterCompletedUi(chapterNumber) {
        const link = chapterLinks.find((item) => Number(item.dataset.chapterNumber) === Number(chapterNumber));
        if (!link) {
            return;
        }

        link.classList.remove('locked');
        link.classList.add('completed');
        link.dataset.locked = 'false';
        const text = link.querySelector('.chapter-complete-text');
        if (text) {
            text.textContent = chapterUi.completed;
        }
    }

    function unlockNextChapterUi(chapterNumber) {
        const link = chapterLinks.find((item) => Number(item.dataset.chapterNumber) === Number(chapterNumber));
        if (!link) {
            return;
        }

        link.classList.remove('locked');
        link.dataset.locked = 'false';
        const text = link.querySelector('.chapter-complete-text');
        if (text && text.textContent.trim() === chapterUi.locked) {
            text.textContent = chapterUi.inProgress;
        }
    }

    function navigateChapterCompletion() {
        if (navigatingChapter || currentCompleted) {
            return;
        }

        navigatingChapter = true;
        completionBadge.textContent = chapterUi.openingNextChapter;
        window.location.href = completionUrl;
    }

    function bootYoutubeAdvance() {
        if (!iframeElement) {
            return;
        }

        const createPlayer = () => {
            if (!window.YT || !window.YT.Player) {
                return;
            }

            const player = new YT.Player('lessonYoutubePlayer', {
                events: {
                    onStateChange(event) {
                        if (event.data === YT.PlayerState.ENDED) {
                            navigateChapterCompletion();
                            return;
                        }

                        if (event.data === YT.PlayerState.PLAYING) {
                            if (youtubeFallbackTimer) {
                                clearInterval(youtubeFallbackTimer);
                            }

                            youtubeFallbackTimer = setInterval(() => {
                                try {
                                    const duration = player.getDuration();
                                    const current = player.getCurrentTime();

                                    if (duration > 0 && current >= Math.max(0, duration - 1.25)) {
                                        clearInterval(youtubeFallbackTimer);
                                        youtubeFallbackTimer = null;
                                        navigateChapterCompletion();
                                    }
                                } catch (error) {
                                    // keep polling light and silent
                                }
                            }, 1000);
                        } else if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.BUFFERING) {
                            if (youtubeFallbackTimer) {
                                clearInterval(youtubeFallbackTimer);
                                youtubeFallbackTimer = null;
                            }
                        }
                    }
                }
            });

            window.skillifyLessonPlayer = player;
        };

        if (window.YT && window.YT.Player) {
            createPlayer();
            return;
        }

        const previousReady = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = function () {
            if (typeof previousReady === 'function') {
                previousReady();
            }
            createPlayer();
        };

        if (!document.querySelector('script[data-skillify-youtube-api="true"]')) {
            const youtubeApiScript = document.createElement('script');
            youtubeApiScript.src = 'https://www.youtube.com/iframe_api';
            youtubeApiScript.dataset.skillifyYoutubeApi = 'true';
            document.head.appendChild(youtubeApiScript);
        }
    }

    if (notesField) {
        notesField.value = localStorage.getItem(notesKey) || '';
        notesField.addEventListener('input', () => {
            localStorage.setItem(notesKey, notesField.value);
        });
    }

    if (videoElement) {
        videoElement.addEventListener('ended', () => navigateChapterCompletion(), { once: true });
    }

    if (iframeElement && iframeElement.src.includes('youtube.com/embed/')) {
        bootYoutubeAdvance();
    }

    if (quizGateOverlay && quizGateOverlay.classList.contains('open')) {
        document.body.style.overflow = 'hidden';
    }

    updateProgressSummary();
</script>
@include('partials.student-chatbot')
</body>
</html>
