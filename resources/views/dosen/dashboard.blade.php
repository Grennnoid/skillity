<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.dosen_dashboard.title_page') }}</title>
    <style>
        :root {
            --bg: #070b14;
            --sidebar: #0a1223;
            --panel: rgba(13, 21, 39, 0.82);
            --line: rgba(152, 179, 230, 0.28);
            --text: #e8f1ff;
            --muted: #98acd1;
            --primary: #45d0ff;
            --secondary: #7cf6d6;
            --shadow: 0 20px 46px rgba(1, 7, 21, 0.45);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--text);
            font-family: "Segoe UI", "Inter", Arial, sans-serif;
            background:
                radial-gradient(1200px 620px at 15% -18%, rgba(69, 208, 255, 0.2), transparent 60%),
                radial-gradient(900px 480px at 85% -20%, rgba(124, 246, 214, 0.18), transparent 56%),
                linear-gradient(180deg, #050911 0%, #060a12 100%);
            overflow-x: hidden;
        }

        .layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            width: 100%;
        }

        .sidebar {
            border-right: 1px solid var(--line);
            background: linear-gradient(180deg, #081022 0%, #0b1428 100%);
            padding: 18px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            min-width: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            font-weight: 700;
        }

        .dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            box-shadow: 0 0 16px rgba(69, 208, 255, 0.7);
        }

        .side-meta {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px;
            background: rgba(14, 23, 42, 0.72);
            margin-bottom: 14px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.5;
        }

        .side-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .side-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid var(--line);
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 700;
            color: #f0f6ff;
            background: rgba(10, 17, 31, 0.9);
            overflow: hidden;
            flex-shrink: 0;
        }

        .side-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .nav {
            display: grid;
            gap: 8px;
            margin-bottom: 14px;
        }

        .nav-btn {
            width: 100%;
            text-align: left;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 11px;
            font-size: 13px;
            color: #d8e7ff;
            background: rgba(14, 23, 42, 0.72);
            cursor: pointer;
        }

        .nav-btn.active {
            border-color: rgba(69, 208, 255, 0.75);
            background: linear-gradient(120deg, rgba(69, 208, 255, 0.25), rgba(124, 246, 214, 0.2));
            color: #fff;
        }

        .side-actions { display: grid; gap: 8px; }

        .locale-switcher {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 4px;
            background: rgba(14, 23, 42, 0.72);
            margin-bottom: 14px;
        }

        .locale-switcher span {
            color: var(--muted);
            font-size: 12px;
            padding: 0 8px;
        }

        .locale-switcher a {
            min-width: 36px;
            text-align: center;
            text-decoration: none;
            color: #d8e7ff;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 10px;
            border-radius: 999px;
        }

        .locale-switcher a.active {
            color: #07121f;
            background: linear-gradient(120deg, var(--primary), var(--secondary));
        }

        .content {
            padding: 22px;
            min-width: 0;
            overflow-x: hidden;
        }

        .topbar h1 {
            margin: 0;
            font-size: 25px;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
        }

        .flash {
            margin: 10px 0;
            border: 1px solid rgba(73, 214, 139, 0.4);
            border-radius: 10px;
            background: rgba(12, 42, 24, 0.5);
            color: #b4f2cc;
            padding: 10px 12px;
            font-size: 13px;
        }

        .error-box {
            margin: 10px 0;
            border: 1px solid rgba(255, 107, 125, 0.45);
            border-radius: 10px;
            background: rgba(72, 21, 33, 0.45);
            color: #ffc4cc;
            padding: 10px 12px;
            font-size: 13px;
        }

        .kpi {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin: 12px 0;
        }

        .kpi .box {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(12, 19, 35, 0.72);
            padding: 12px;
        }

        .kpi strong {
            display: block;
            font-size: 20px;
            margin-bottom: 3px;
        }

        .panel {
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 16px;
        }

        .panel h2 {
            margin: 0 0 6px;
            font-size: 19px;
        }

        .view { display: none; }
        .view.active { display: block; }

        .cards-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .cards-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(12, 19, 35, 0.72);
            padding: 12px;
        }

        .card h3 {
            margin: 0 0 10px;
            font-size: 15px;
        }

        .fields { display: grid; gap: 8px; }

        input, select, textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px;
            font-size: 13px;
            color: var(--text);
            background: rgba(8, 14, 27, 0.82);
            outline: none;
        }

        select {
            appearance: none;
            padding-right: 34px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23d4e3ff' d='M6 8 0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 12px 8px;
        }

        textarea { min-height: 95px; resize: vertical; }

        .btn {
            border: 0;
            border-radius: 10px;
            padding: 9px 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            color: #041220;
            background: linear-gradient(120deg, var(--primary), var(--secondary));
        }

        .btn-ghost {
            color: #d9e8ff;
            border: 1px solid var(--line);
            background: rgba(14, 24, 44, 0.7);
        }

        .btn-danger {
            color: #fff;
            background: linear-gradient(120deg, #ff6b7d, #ff9068);
        }

        .question-bank-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .question-bank-metric {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(12, 19, 35, 0.72);
            padding: 12px;
        }

        .question-bank-metric strong {
            display: block;
            font-size: 20px;
            margin-bottom: 4px;
        }

        .question-bank-list {
            display: grid;
            gap: 10px;
        }

        .question-bank-course {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(9, 15, 28, 0.74);
            padding: 14px;
        }

        .question-bank-course-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .question-bank-course-head h4 {
            margin: 0;
            font-size: 18px;
            color: #f3f7ff;
        }

        .question-bank-course-meta {
            color: var(--muted);
            font-size: 12px;
        }

        .question-bank-chapter-group {
            border-top: 1px solid rgba(154, 178, 225, 0.14);
            padding-top: 12px;
            margin-top: 12px;
        }

        .question-bank-chapter-group:first-of-type {
            border-top: 0;
            padding-top: 0;
            margin-top: 0;
        }

        .question-bank-chapter-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .question-bank-chapter-head h5 {
            margin: 0;
            font-size: 14px;
            color: #dfeeff;
            letter-spacing: 0.2px;
        }

        .question-bank-chapter-toggle {
            border-top: 1px solid rgba(154, 178, 225, 0.14);
            padding-top: 12px;
            margin-top: 12px;
        }

        .question-bank-chapter-toggle:first-of-type {
            border-top: 0;
            padding-top: 0;
            margin-top: 0;
        }

        .question-bank-chapter-toggle > summary {
            list-style: none;
            cursor: pointer;
        }

        .question-bank-chapter-toggle > summary::-webkit-details-marker {
            display: none;
        }

        .question-bank-chapter-toggle > summary .question-bank-chapter-head::after {
            content: '+';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            border: 1px solid var(--line);
            color: #dff1ff;
            background: rgba(12, 19, 35, 0.8);
            font-size: 15px;
            font-weight: 700;
            margin-left: auto;
            flex-shrink: 0;
        }

        .question-bank-chapter-toggle[open] > summary .question-bank-chapter-head::after {
            content: '-';
        }

        .question-bank-count {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid var(--line);
            color: #cfe0ff;
            background: rgba(14, 24, 44, 0.65);
            font-size: 11px;
            font-weight: 700;
        }

        .question-bank-item {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(10, 17, 31, 0.84);
            padding: 14px;
        }

        .question-bank-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .question-bank-title {
            margin: 0 0 6px;
            font-size: 16px;
            color: #f1f6ff;
        }

        .question-bank-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .question-tag {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(15, 24, 43, 0.9);
            color: #cfe0ff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .question-tag.pop {
            border-color: rgba(255, 107, 125, 0.38);
            background: rgba(70, 18, 31, 0.6);
            color: #ffc9d2;
        }

        .question-tag.ai {
            border-color: rgba(69, 208, 255, 0.38);
            background: rgba(10, 31, 48, 0.65);
            color: #bfeeff;
        }

        .question-bank-text {
            margin: 0 0 12px;
            color: #deebff;
            line-height: 1.6;
            font-size: 14px;
        }

        .question-bank-meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .question-bank-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .question-bank-empty {
            border: 1px dashed var(--line);
            border-radius: 14px;
            padding: 16px;
            color: var(--muted);
            background: rgba(10, 17, 31, 0.5);
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 12px;
            margin-top: 10px;
        }

        table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
        }

        th, td {
            padding: 9px;
            border-bottom: 1px solid rgba(152, 179, 230, 0.16);
            font-size: 13px;
            text-align: left;
            vertical-align: top;
        }

        th { color: #bdd0f4; }

        .status {
            display: inline-flex;
            padding: 3px 8px;
            border-radius: 999px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .status.active { border-color: rgba(73, 214, 139, 0.45); color: #9aefbf; }
        .status.suspended { border-color: rgba(255, 107, 125, 0.45); color: #ffb5be; }
        .status.info { border-color: rgba(69, 208, 255, 0.45); color: #bfeeff; }

        .analytics-bars { display: grid; gap: 8px; }

        .bar {
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            background: rgba(10, 17, 31, 0.8);
        }

        .bar > div {
            padding: 8px 10px;
            background: linear-gradient(120deg, rgba(69, 208, 255, 0.48), rgba(124, 246, 214, 0.42));
            color: #eaf8ff;
            font-size: 12px;
        }

        .site-footer {
            margin-top: 16px;
            border-top: 1px solid var(--line);
            padding-top: 14px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 1180px) {
            .kpi { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cards-2 { grid-template-columns: 1fr; }
            .cards-3 { grid-template-columns: 1fr; }
            .question-bank-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 920px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar {
                position: static;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--line);
                padding: 14px;
            }
            .nav { grid-template-columns: 1fr 1fr; }
            .content { padding: 16px; }
            .side-actions { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .nav { grid-template-columns: 1fr; }
            .kpi { grid-template-columns: 1fr; }
            .question-bank-summary { grid-template-columns: 1fr; }
            .sidebar { padding: 12px; }
            .content { padding: 12px 10px 16px; }
            .nav-btn,
            .btn { width: 100%; }
            .table-wrap {
                margin-left: -2px;
                margin-right: -2px;
            }
        }
    </style>
</head>
<body>
@php($dosenUi = __('ui.dosen_dashboard'))
<div class="layout">
    <aside class="sidebar">
        @php
            $profileImageUrl = auth()->user()->profile_image ? asset('storage/'.auth()->user()->profile_image) : null;
            $nameParts = preg_split('/\s+/', trim(auth()->user()->name));
            $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1).substr($nameParts[1] ?? '', 0, 1));
            $initials = $initials !== '' ? $initials : 'U';
        @endphp

        <div class="brand">
            <span class="dot"></span>
            <span>{{ $dosenUi['dashboard'] }}</span>
        </div>

        <div class="locale-switcher">
            <span>{{ __('ui.locale.switch') }}</span>
            <a href="{{ route('locale.switch', ['locale' => 'en']) }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">{{ __('ui.locale.en') }}</a>
            <a href="{{ route('locale.switch', ['locale' => 'id']) }}" class="{{ app()->getLocale() === 'id' ? 'active' : '' }}">{{ __('ui.locale.id') }}</a>
        </div>

        <div class="side-meta">
            <div class="side-profile">
                <div class="side-avatar">
                    @if($profileImageUrl)
                        <img src="{{ $profileImageUrl }}" alt="Profile picture">
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div>
                    <strong style="color:#e8f1ff;">{{ auth()->user()->name }}</strong>
                </div>
            </div>
            {{ $dosenUi['login_as'] }} <strong>{{ auth()->user()->name }}</strong><br>
            {{ $dosenUi['role'] }}: <strong>{{ strtoupper(auth()->user()->role) }}</strong>
        </div>

        <nav class="nav" id="sideNavDosen">
            <button class="nav-btn active" data-target="manage-courses">{{ $dosenUi['manage_courses'] }}</button>
            <button class="nav-btn" data-target="manage-quiz">{{ $dosenUi['manage_quiz'] }}</button>
            <button class="nav-btn" data-target="scores">{{ $dosenUi['scores'] }}</button>
            <button class="nav-btn" data-target="qa-inbox">{{ $dosenUi['qa_inbox'] }}</button>
            <button class="nav-btn" data-target="analytics">{{ $dosenUi['analytics'] }}</button>
        </nav>

        <div class="side-actions">
            <a class="btn btn-ghost" href="{{ route('profile.show') }}">{{ $dosenUi['profile'] }}</a>
            <a class="btn btn-ghost" href="{{ route('landing') }}">{{ $dosenUi['back_to_landing'] }}</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger" type="submit" style="width:100%;">{{ $dosenUi['logout'] }}</button>
            </form>
        </div>
    </aside>

    <main class="content">
        <div class="topbar">
            <h1>{{ $dosenUi['dashboard'] }}</h1>
            <p class="muted">{{ $dosenUi['dashboard_intro'] }}</p>
        </div>

        @if(session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <section class="kpi">
            <article class="box"><strong>{{ $stats['courses'] }}</strong><span class="muted">{{ $dosenUi['courses'] }}</span></article>
            <article class="box"><strong>{{ $stats['questions'] }}</strong><span class="muted">{{ $dosenUi['questions'] }}</span></article>
            <article class="box"><strong>{{ $stats['submissions'] }}</strong><span class="muted">{{ $dosenUi['submissions'] }}</span></article>
            <article class="box"><strong>{{ number_format((float)($avgScore->avg_score ?? 0), 1) }}</strong><span class="muted">{{ $dosenUi['average_score'] }}</span></article>
        </section>

        <section class="panel view active" id="manage-courses">
            <h2>{{ $dosenUi['manage_courses'] }}</h2>
            <p class="muted">{{ $dosenUi['manage_courses_text'] }}</p>
            <div style="margin-bottom: 10px;">
                <button class="btn btn-primary" type="button" id="jumpAddCourseBtn">{{ $dosenUi['add_new_course'] }}</button>
            </div>

            <div class="cards-2">
                <article class="card" id="add-course-form">
                    <h3>{{ $dosenUi['create_new_course'] }}</h3>
                    <form class="fields" action="{{ route('dosen.courses.store') }}" method="POST">
                        @csrf
                        <input type="text" name="title" placeholder="{{ $dosenUi['course_title_placeholder'] }}" required>
                        <input type="text" name="category" placeholder="{{ $dosenUi['course_category_placeholder'] }}" required>
                        <select name="difficulty" required>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                        <button class="btn btn-primary" type="submit">{{ $dosenUi['create_course'] }}</button>
                    </form>
                </article>

                <article class="card">
                    <h3>{{ __('ui.dosen_dashboard.my_courses', ['name' => auth()->user()->name]) }}</h3>
                    <div class="table-wrap">
                        <table style="min-width:100%;">
                            <thead>
                            <tr><th>{{ $dosenUi['title'] }}</th><th>{{ $dosenUi['category'] }}</th><th>{{ $dosenUi['difficulty'] }}</th><th>{{ $dosenUi['action'] }}</th></tr>
                            </thead>
                            <tbody>
                            @forelse($manageableCourses as $course)
                                <tr>
                                    <td>{{ $course->title }}</td>
                                    <td>{{ $course->category }}</td>
                                    <td>{{ ucfirst($course->difficulty) }}</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-ghost js-edit-course"
                                            data-course-key="{{ $course->key }}"
                                            style="padding:6px 10px;font-size:11px;"
                                        >
                                            {{ $dosenUi['edit_info'] }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4">{{ $dosenUi['no_courses'] }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            <div class="card" style="margin-top: 12px;">
                <h3>{{ $dosenUi['course_info_editor'] }}</h3>
                <p class="muted">{{ $dosenUi['course_info_text'] }}</p>
                <form class="fields" id="courseInfoForm" action="{{ route('dosen.courses.info.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <select id="courseInfoSelector" required>
                        <option value="">{{ $dosenUi['choose_course'] }}</option>
                        @foreach($manageableCourses as $courseOption)
                            <option value="{{ $courseOption->key }}">{{ $courseOption->title }}</option>
                        @endforeach
                    </select>
                    <a
                        id="chapterBuilderLink"
                        class="btn btn-ghost"
                        href="{{ route('instructor.courses.roadmap', ['course' => 'frontend-craft']) }}"
                        style="display:none; width: fit-content;"
                    >
                        {{ $dosenUi['open_chapter_builder'] }}
                    </a>
                    <input type="hidden" name="quiz_id" id="courseInfoQuizId">
                    <input type="text" name="hero_title" id="courseInfoHeroTitle" placeholder="{{ $dosenUi['hero_title_placeholder'] }}">
                    <input type="url" name="hero_background_url" id="courseInfoHeroBackgroundUrl" placeholder="{{ $dosenUi['hero_background_placeholder'] }}">
                    <input type="file" name="hero_background_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <input type="text" name="tagline" id="courseInfoTagline" placeholder="{{ $dosenUi['tagline_placeholder'] }}">
                    <input type="text" name="instructor_name" id="courseInfoInstructorName" placeholder="{{ $dosenUi['instructor_name_placeholder'] }}">
                    <input type="url" name="instructor_photo_url" id="courseInfoInstructorPhoto" placeholder="{{ $dosenUi['instructor_photo_placeholder'] }}">
                    <input type="file" name="instructor_photo_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <textarea name="about" id="courseInfoAbout" placeholder="{{ $dosenUi['about_placeholder'] }}"></textarea>
                    <input type="text" name="target_audience" id="courseInfoTargetAudience" placeholder="{{ $dosenUi['target_audience_placeholder'] }}">
                    <input type="text" name="duration_text" id="courseInfoDurationText" placeholder="{{ $dosenUi['duration_text_placeholder'] }}">
                    <textarea name="syllabus_lines" id="courseInfoSyllabusLines" placeholder="{{ $dosenUi['syllabus_placeholder'] }}"></textarea>
                    <textarea name="learning_outcomes" id="courseInfoLearningOutcomes" placeholder="{{ $dosenUi['learning_outcomes_placeholder'] }}"></textarea>
                    <input type="url" name="trailer_url" id="courseInfoTrailerUrl" placeholder="{{ $dosenUi['trailer_url_placeholder'] }}">
                    <input type="file" name="trailer_file" accept=".mp4,.mov,.m4v,.webm,.avi,video/mp4,video/quicktime,video/webm,video/x-msvideo">
                    <input type="url" name="trailer_poster_url" id="courseInfoTrailerPosterUrl" placeholder="{{ $dosenUi['trailer_poster_placeholder'] }}">
                    <input type="file" name="trailer_poster_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <button class="btn btn-primary" type="submit">{{ $dosenUi['save_course_info'] }}</button>
                </form>

                <div class="table-wrap">
                    <table style="min-width:100%;">
                        <thead>
                        <tr>
                            <th>{{ $dosenUi['course'] }}</th>
                            <th>Tagline</th>
                            <th>{{ $dosenUi['audience'] }}</th>
                            <th>{{ $dosenUi['updated'] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($courseInfoRows as $row)
                            <tr>
                                <td>{{ $row->title }}</td>
                                <td>{{ $row->tagline ?? '-' }}</td>
                                <td>{{ $row->target_audience ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->updated_at)->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">{{ $dosenUi['no_custom_course_info'] }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <section class="panel view" id="manage-quiz">
            <h2>{{ $dosenUi['manage_quiz'] }}</h2>
            <p class="muted">{{ $dosenUi['manage_quiz_text'] }}</p>
            @php($dosenAiPreview = session('dosen_ai_question_preview'))

            <div class="cards-2">
                <article class="card">
                    <h3>{{ $dosenUi['ai_quiz_generator'] }}</h3>
                    <form class="fields" action="{{ route('dosen.questions.ai.preview') }}" method="POST">
                        @csrf
                        <select name="course_key" required>
                            <option value="">{{ $dosenUi['choose_target_course'] }}</option>
                            @foreach($manageableCourses as $course)
                                <option value="{{ $course->key }}" @selected(old('course_key', $dosenAiPreview['course_key'] ?? '') == $course->key)>
                                    {{ $course->title }} ({{ ucfirst($course->difficulty) }})
                                </option>
                            @endforeach
                        </select>
                        <textarea name="generation_notes" placeholder="{{ $dosenUi['generation_notes_placeholder'] }}" required>{{ old('generation_notes', $dosenAiPreview['generation_notes'] ?? '') }}</textarea>
                        <select name="difficulty" required>
                            <option value="beginner" @selected(old('difficulty', $dosenAiPreview['difficulty'] ?? '') === 'beginner')>Beginner</option>
                            <option value="intermediate" @selected(old('difficulty', $dosenAiPreview['difficulty'] ?? '') === 'intermediate')>Intermediate</option>
                            <option value="advanced" @selected(old('difficulty', $dosenAiPreview['difficulty'] ?? '') === 'advanced')>Advanced</option>
                        </select>
                        <input type="number" name="question_count" min="1" max="10" value="{{ old('question_count', $dosenAiPreview['question_count'] ?? 5) }}" placeholder="{{ $dosenUi['question_count_placeholder'] }}">
                        <select name="question_type_mode" required>
                            <option value="mcq" @selected(old('question_type_mode', $dosenAiPreview['question_type_mode'] ?? '') === 'mcq')>MCQ Only</option>
                            <option value="essay" @selected(old('question_type_mode', $dosenAiPreview['question_type_mode'] ?? '') === 'essay')>Essay Only</option>
                            <option value="true_false" @selected(old('question_type_mode', $dosenAiPreview['question_type_mode'] ?? '') === 'true_false')>True / False Only</option>
                            <option value="mixed_mcq_essay" @selected(old('question_type_mode', $dosenAiPreview['question_type_mode'] ?? '') === 'mixed_mcq_essay')>Mixed MCQ + Essay</option>
                            <option value="mixed_all" @selected(old('question_type_mode', $dosenAiPreview['question_type_mode'] ?? '') === 'mixed_all')>Mixed All Types</option>
                        </select>
                        <input type="number" name="placement_after_chapter" min="1" max="40" value="{{ old('placement_after_chapter', $dosenAiPreview['placement_after_chapter'] ?? '') }}" placeholder="{{ $dosenUi['insert_after_chapter_placeholder'] }}">
                        <p class="muted" style="margin:0;">{{ $dosenUi['auto_pop_quiz_note'] }}</p>
                        <button class="btn btn-primary" type="submit">{{ $dosenUi['preview_questions'] }}</button>
                    </form>
                </article>

                <article class="card">
                    <h3>{{ $dosenUi['manual_question'] }}</h3>
                    <form class="fields" action="{{ route('dosen.questions.store') }}" method="POST">
                        @csrf
                        <select name="course_key" required>
                            <option value="">{{ $dosenUi['choose_course_or_quiz'] }}</option>
                            @foreach($manageableCourses as $course)
                                <option value="{{ $course->key }}">{{ $course->title }} ({{ ucfirst($course->difficulty) }})</option>
                            @endforeach
                        </select>
                        <textarea name="question_text" placeholder="{{ $dosenUi['question_placeholder'] }}" required></textarea>
                        <select name="question_type" required>
                            <option value="mcq">MCQ</option>
                            <option value="essay">Essay</option>
                            <option value="true_false">True / False</option>
                        </select>
                        <select name="difficulty" required>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                        <input type="number" name="placement_after_chapter" min="1" max="40" placeholder="{{ $dosenUi['insert_after_chapter_placeholder'] }}">
                        <p class="muted" style="margin:0;">{{ $dosenUi['auto_pop_quiz_note'] }}</p>
                        <input type="text" name="correct_answer" placeholder="{{ $dosenUi['correct_answer_placeholder'] }}">
                        <textarea name="options_json" placeholder="{{ $dosenUi['options_placeholder'] }}"></textarea>
                        <button class="btn btn-primary" type="submit">{{ $dosenUi['save_question'] }}</button>
                    </form>
                </article>
            </div>

            @if(is_array($dosenAiPreview) && !empty($dosenAiPreview['questions']))
                <div class="card" style="margin-top:12px;">
                    <h3>{{ __('ui.dosen_dashboard.ai_preview', ['course' => $dosenAiPreview['course_title']]) }}</h3>
                    <p class="muted">{{ $dosenUi['placement'] }}:
                        @if(!empty($dosenAiPreview['placement_after_chapter']))
                            {{ __('ui.dosen_dashboard.after_chapter', ['chapter' => $dosenAiPreview['placement_after_chapter']]) }}
                        @else
                            {{ $dosenUi['no_pop_quiz_placement'] }}
                        @endif
                        &bull; {{ $dosenUi['type_mode'] }}: {{ str_replace('_', ' ', $dosenAiPreview['question_type_mode']) }}
                    </p>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ $dosenUi['question'] }}</th>
                                <th>{{ $dosenUi['type'] }}</th>
                                <th>{{ $dosenUi['difficulty'] }}</th>
                                <th>{{ $dosenUi['answer_options'] }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dosenAiPreview['questions'] as $index => $question)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $question['question_text'] }}</td>
                                    <td>{{ strtoupper($question['question_type']) }}</td>
                                    <td>{{ ucfirst($question['difficulty']) }}</td>
                                    <td>
                                        <div>{{ $question['correct_answer'] ?: '-' }}</div>
                                        @if(!empty($question['options_json']))
                                            <div class="muted">{{ $question['options_json'] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <form action="{{ route('dosen.questions.ai.save') }}" method="POST" style="margin-top:10px;">
                        @csrf
                        <button class="btn btn-primary" type="submit">{{ $dosenUi['save_preview_to_bank'] }}</button>
                    </form>
                </div>
            @endif

            <div class="card" style="margin-top: 12px;">
                <h3>{{ $dosenUi['stored_question_bank'] }}</h3>
                <p class="muted">{{ $dosenUi['stored_question_bank_text'] }}</p>
                <div class="question-bank-summary">
                    <article class="question-bank-metric">
                        <strong>{{ $questionBankPresentation['summary']['total'] ?? 0 }}</strong>
                        <span class="muted">{{ $dosenUi['stored_total'] }}</span>
                    </article>
                    <article class="question-bank-metric">
                        <strong>{{ $questionBankPresentation['summary']['pop_quiz'] ?? 0 }}</strong>
                        <span class="muted">{{ $dosenUi['stored_pop_quiz'] }}</span>
                    </article>
                    <article class="question-bank-metric">
                        <strong>{{ $questionBankPresentation['summary']['ai'] ?? 0 }}</strong>
                        <span class="muted">{{ $dosenUi['stored_ai'] }}</span>
                    </article>
                    <article class="question-bank-metric">
                        <strong>{{ $questionBankPresentation['summary']['manual'] ?? 0 }}</strong>
                        <span class="muted">{{ $dosenUi['stored_manual'] }}</span>
                    </article>
                </div>
                <div class="question-bank-list">
                    @forelse(($questionBankPresentation['courses'] ?? []) as $courseGroup)
                        <section class="question-bank-course">
                            <div class="question-bank-course-head">
                                <div>
                                    <h4>{{ $courseGroup['course_label'] }}</h4>
                                    <div class="question-bank-course-meta">{{ $courseGroup['course_slug'] }} - {{ $courseGroup['count'] }} questions stored</div>
                                </div>
                                <span class="question-bank-count">{{ $courseGroup['pop_quiz_count'] }} pop quiz</span>
                            </div>

                            @foreach($courseGroup['chapters'] as $chapterIndex => $chapterGroup)
                                <details class="question-bank-chapter-toggle" {{ $chapterIndex === 0 ? 'open' : '' }}>
                                    <summary>
                                        <div class="question-bank-chapter-head">
                                            <h5>{{ $chapterGroup['label'] }}</h5>
                                            <span class="question-bank-count">{{ $chapterGroup['count'] }} questions</span>
                                        </div>
                                    </summary>
                                    <div class="question-bank-chapter-group">
                                        <div class="question-bank-list">
                                            @foreach($chapterGroup['rows'] as $row)
                                                <article class="question-bank-item">
                                                    <div class="question-bank-header">
                                                        <div>
                                                            <h4 class="question-bank-title">{{ \Illuminate\Support\Str::limit($row->question_text, 68) }}</h4>
                                                            <div class="question-bank-tags">
                                                                <span class="question-tag">{{ strtoupper($row->question_type) }}</span>
                                                                <span class="question-tag">{{ ucfirst($row->difficulty) }}</span>
                                                                <span class="question-tag {{ $row->question_origin === 'ai' ? 'ai' : '' }}">{{ strtoupper($row->question_origin) }}</span>
                                                                @if($row->is_pop_quiz && $row->placement_after_chapter)
                                                                    <span class="question-tag pop">Pop quiz gate</span>
                                                                @elseif($row->placement_after_chapter)
                                                                    <span class="question-tag">Lesson checkpoint</span>
                                                                @else
                                                                    <span class="question-tag">Reusable</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="question-bank-actions">
                                                            <form action="{{ route('dosen.questions.delete', $row->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-danger" type="submit">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <p class="question-bank-text">{{ \Illuminate\Support\Str::limit($row->question_text, 180) }}</p>
                                                    <div class="question-bank-meta">
                                                        <span class="muted">Created by {{ auth()->user()->name }}</span>
                                                        <span class="muted">{{ \Illuminate\Support\Carbon::parse($row->created_at)->format('d M Y H:i') }}</span>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </section>
                    @empty
                        <div class="question-bank-empty">{{ $dosenUi['empty_question_bank'] }}</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="panel view" id="scores">
            <h2>{{ $dosenUi['scores'] }}</h2>
            <p class="muted">{{ $dosenUi['view_scores_text'] }}</p>
            <a class="btn btn-ghost" href="{{ route('dosen.scores.export') }}">{{ $dosenUi['export_scores'] }}</a>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>{{ $dosenUi['student'] }}</th>
                        <th>{{ $dosenUi['course'] }}</th>
                        <th>{{ $dosenUi['auto_score'] }}</th>
                        <th>{{ $dosenUi['manual_score'] }}</th>
                        <th>{{ $dosenUi['status'] }}</th>
                        <th>{{ $dosenUi['submitted'] }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($submissions as $submission)
                        <tr>
                            <td>{{ $submission->student_name ?? '-' }}</td>
                            <td>{{ $submission->course_title ?? '-' }}</td>
                            <td>{{ $submission->score ?? '-' }}</td>
                            <td>{{ $submission->manual_score ?? '-' }}</td>
                            <td>{{ ucfirst($submission->status) }}</td>
                            <td>{{ $submission->submitted_at ? \Illuminate\Support\Carbon::parse($submission->submitted_at)->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">{{ $dosenUi['no_scores'] }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card" style="margin-top: 12px;">
                <h3>{{ $dosenUi['attendance_course'] }}</h3>
                <div class="cards-2">
                    <article class="card">
                        <strong style="display:block;font-size:22px;">{{ $attendanceStats['total_sessions'] ?? 0 }}</strong>
                        <span class="muted">{{ $dosenUi['total_attendance_sessions'] }}</span>
                    </article>
                    <article class="card">
                        <strong style="display:block;font-size:22px;">{{ $attendanceStats['attended_sessions'] ?? 0 }}</strong>
                        <span class="muted">{{ $dosenUi['attendance_counted'] }}</span>
                    </article>
                </div>
                <p class="muted" style="margin-top:10px;">{{ __('ui.dosen_dashboard.students_in_mode', ['count' => $attendanceStats['students_in_mode'] ?? 0]) }}</p>
                <div class="table-wrap" style="margin-top:10px;">
                    <table>
                        <thead>
                        <tr>
                            <th>{{ $dosenUi['student'] }}</th>
                            <th>{{ $dosenUi['course'] }}</th>
                            <th>{{ $dosenUi['date'] }}</th>
                            <th>{{ $dosenUi['progress'] }}</th>
                            <th>{{ $dosenUi['status'] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($attendanceRecords as $attendanceItem)
                            <tr>
                                <td>{{ $attendanceItem->student_name ?? 'Student' }}</td>
                                <td>{{ $attendanceItem->course_title }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($attendanceItem->attendance_date)->format('d M Y') }}</td>
                                <td>{{ $attendanceItem->chapters_completed }}/{{ $attendanceItem->target_chapters }} chapters</td>
                                <td>{{ $attendanceItem->is_attended ? $dosenUi['counted'] : $dosenUi['in_progress'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">{{ $dosenUi['no_attendance'] }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="panel view" id="qa-inbox">
            <h2>{{ $dosenUi['qa_inbox'] }}</h2>
            <p class="muted">{{ $dosenUi['qa_inbox_text'] }}</p>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>{{ $dosenUi['student'] }}</th>
                        <th>{{ $dosenUi['course'] }}</th>
                        <th>{{ $dosenUi['chapter'] }}</th>
                        <th>{{ $dosenUi['student_question'] }}</th>
                        <th>{{ $dosenUi['lecturer_answer'] }}</th>
                        <th>{{ $dosenUi['time'] }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($qaInbox as $qa)
                        <tr>
                            <td>{{ $qa->student_name ?? 'Student' }}</td>
                            <td>{{ $qa->course_title }}</td>
                                <td>{{ $qa->chapter_number ? 'Chapter '.$qa->chapter_number : $dosenUi['general'] }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($qa->question_text, 140) }}</td>
                            <td>
                                <form action="{{ route('dosen.questions.answer', ['question' => $qa->id]) }}" method="POST" class="fields" style="gap:6px;">
                                    @csrf
                                    @method('PATCH')
                                    <textarea name="answer_text" style="min-height:64px;" placeholder="{{ $dosenUi['answer_placeholder'] }}" required>{{ $qa->answer_text }}</textarea>
                                    <button class="btn btn-ghost" type="submit" style="width:fit-content;">{{ $dosenUi['save_answer'] }}</button>
                                </form>
                            </td>
                            <td>{{ \Illuminate\Support\Carbon::parse($qa->created_at)->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">{{ $dosenUi['no_questions'] }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel view" id="analytics">
            <h2>{{ $dosenUi['analytics'] }}</h2>
            <p class="muted">{{ $dosenUi['analytics_text'] }}</p>

            <div class="cards-3">
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $progressOverview['active_learners'] ?? 0 }}</strong>
                    <span class="muted">{{ $dosenUi['active_learners'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $progressOverview['consistent_rate'] ?? 0 }}%</strong>
                    <span class="muted">{{ $dosenUi['consistent_adoption'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $progressOverview['avg_progress'] ?? 0 }}%</strong>
                    <span class="muted">{{ $dosenUi['avg_progress'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $progressOverview['attendance_rate'] ?? 0 }}%</strong>
                    <span class="muted">{{ $dosenUi['attendance_rate'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $progressOverview['pop_quiz_mastery'] ?? 0 }}%</strong>
                    <span class="muted">{{ $dosenUi['pop_quiz_mastery'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $progressOverview['qa_answer_rate'] ?? 0 }}%</strong>
                    <span class="muted">{{ $dosenUi['qa_coverage'] }}</span>
                </article>
            </div>

            <div class="cards-2" style="margin-top:12px;">
                <article class="card">
                    <h3>{{ $dosenUi['seven_day_momentum'] }}</h3>
                    <p class="muted">{{ $dosenUi['seven_day_momentum_text'] }}</p>
                    <div class="analytics-bars">
                        @if(!empty($progressWeeklyRows))
                            @foreach($progressWeeklyRows as $row)
                                <div class="bar">
                                    <div style="width: {{ max(14, $row['width']) }}%;">
                                        {{ $row['label'] }} - {{ $row['completions'] }} chapter selesai / {{ $row['attendance'] }} attendance counted
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="muted">{{ $dosenUi['no_momentum'] }}</p>
                        @endif
                    </div>
                </article>

                <article class="card">
                    <h3>{{ $dosenUi['category_snapshot'] }}</h3>
                    <p class="muted">{{ $dosenUi['category_snapshot_text'] }}</p>
                    <div class="analytics-bars">
                        @forelse($analytics as $item)
                            <div class="bar">
                                <div style="width: {{ min(100, $item->total * 12) }}%;">{{ $item->category }} - {{ $item->total }} {{ $dosenUi['submissions_label'] }}</div>
                            </div>
                        @empty
                            <p class="muted">{{ $dosenUi['no_activity_snapshot'] }}</p>
                        @endforelse
                    </div>
                </article>
            </div>

            <div class="card" style="margin-top:12px;">
                <h3>{{ $dosenUi['course_health'] }}</h3>
                <p class="muted">{{ $dosenUi['course_health_text'] }}</p>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>{{ $dosenUi['course'] }}</th>
                            <th>{{ $dosenUi['students'] }}</th>
                            <th>{{ $dosenUi['consistent_mode'] }}</th>
                            <th>{{ $dosenUi['avg_progress'] }}</th>
                            <th>{{ $dosenUi['attendance_rate'] }}</th>
                            <th>{{ $dosenUi['pop_quiz_mastery'] }}</th>
                            <th>{{ $dosenUi['open_qa'] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!empty($progressCourseHealthRows))
                            @foreach($progressCourseHealthRows as $row)
                                <tr>
                                    <td>{{ $row['course_title'] }}</td>
                                    <td>{{ $row['enrolled_students'] }}</td>
                                    <td>{{ $row['consistent_users'] }}</td>
                                    <td>{{ $row['avg_progress'] }}%</td>
                                    <td>{{ $row['attendance_rate'] }}%</td>
                                    <td>{{ $row['pop_quiz_mastery'] }}%</td>
                                    <td>{{ $row['open_questions'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="7">{{ $dosenUi['no_course_health'] }}</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="margin-top:12px;">
                <h3>{{ $dosenUi['students_to_watch'] }}</h3>
                <p class="muted">{{ $dosenUi['students_to_watch_text'] }}</p>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>{{ $dosenUi['student'] }}</th>
                            <th>{{ $dosenUi['course'] }}</th>
                            <th>{{ $dosenUi['progress'] }}</th>
                            <th>{{ $dosenUi['attendance_rate'] }}</th>
                            <th>{{ $dosenUi['last_activity'] }}</th>
                            <th>{{ $dosenUi['status'] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!empty($progressStudentFocusRows))
                            @foreach($progressStudentFocusRows as $row)
                                <tr>
                                    <td>{{ $row['student_name'] }}</td>
                                    <td>{{ $row['course_title'] }}</td>
                                    <td>{{ $row['progress_percent'] }}%</td>
                                    <td>{{ $row['attendance_rate'] }}%</td>
                                    <td>{{ $row['last_activity'] ? \Illuminate\Support\Carbon::parse($row['last_activity'])->format('d M Y H:i') : $dosenUi['no_activity'] }}</td>
                                    <td>
                                        <span class="status {{ $row['status'] === 'Strong' ? 'active' : ($row['status'] === 'Needs Attention' ? 'suspended' : 'info') }}">
                                            {{ $row['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="6">{{ $dosenUi['no_students_to_watch'] }}</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <footer class="site-footer">
            <p>&copy; {{ date('Y') }} {{ $dosenUi['footer'] }}</p>
        </footer>
    </main>
</div>

<script>
    const nav = document.getElementById('sideNavDosen');
    const buttons = Array.from(nav.querySelectorAll('.nav-btn'));
    const views = Array.from(document.querySelectorAll('.view'));
    const courseInfoData = @json($courseInfoData ?? []);

    function setActive(targetId, pushHash = true) {
        buttons.forEach((btn) => btn.classList.toggle('active', btn.dataset.target === targetId));
        views.forEach((view) => view.classList.toggle('active', view.id === targetId));
        if (pushHash) {
            window.location.hash = targetId;
        }
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => setActive(btn.dataset.target));
    });

    const hash = window.location.hash.replace('#', '');
    if (hash && views.some((v) => v.id === hash)) {
        setActive(hash, false);
    }

    const courseInfoForm = document.getElementById('courseInfoForm');
    const courseSelector = document.getElementById('courseInfoSelector');
    const quizIdHidden = document.getElementById('courseInfoQuizId');
    const editButtons = Array.from(document.querySelectorAll('.js-edit-course'));
    const jumpAddCourseBtn = document.getElementById('jumpAddCourseBtn');
    const addCourseForm = document.getElementById('add-course-form');
    const chapterBuilderLink = document.getElementById('chapterBuilderLink');
    const chapterBuilderBaseUrl = @json(url('/instructor/courses'));
    const quizInfoUpdateUrl = "{{ route('dosen.courses.info.update') }}";
    const frontendCraftUpdateUrl = "{{ route('dosen.courses.frontend-craft.page.update') }}";
    const learningOutcomesField = document.getElementById('courseInfoLearningOutcomes');

    function fillCourseInfoForm(courseKey) {
        const data = courseInfoData[String(courseKey)] || {};
        document.getElementById('courseInfoHeroTitle').value = data.hero_title || '';
        document.getElementById('courseInfoHeroBackgroundUrl').value = data.hero_background_url || '';
        document.getElementById('courseInfoTagline').value = data.tagline || '';
        document.getElementById('courseInfoInstructorName').value = data.instructor_name || '';
        document.getElementById('courseInfoInstructorPhoto').value = data.instructor_photo_url || '';
        document.getElementById('courseInfoAbout').value = data.about || '';
        document.getElementById('courseInfoTargetAudience').value = data.target_audience || '';
        document.getElementById('courseInfoDurationText').value = data.duration_text || '';
        document.getElementById('courseInfoSyllabusLines').value = data.syllabus_lines || '';
        document.getElementById('courseInfoLearningOutcomes').value = data.learning_outcomes || '';
        document.getElementById('courseInfoTrailerUrl').value = data.trailer_url || '';
        document.getElementById('courseInfoTrailerPosterUrl').value = data.trailer_poster_url || '';

        if (!courseInfoForm || !quizIdHidden) {
            return;
        }

        if (!courseKey) {
            if (chapterBuilderLink) {
                chapterBuilderLink.style.display = 'none';
            }
            return;
        }

        if (chapterBuilderLink) {
            const builderSlug = String(courseKey) === 'frontend-craft' ? 'frontend-craft' : `quiz-${courseKey}`;
            chapterBuilderLink.href = `${chapterBuilderBaseUrl}/${builderSlug}/roadmap`;
            chapterBuilderLink.style.display = 'inline-flex';
        }

        if (String(courseKey) === 'frontend-craft') {
            courseInfoForm.action = frontendCraftUpdateUrl;
            quizIdHidden.value = '';
            quizIdHidden.disabled = true;
            if (learningOutcomesField) {
                learningOutcomesField.name = 'outcomes_text';
            }
        } else {
            courseInfoForm.action = quizInfoUpdateUrl;
            quizIdHidden.value = String(courseKey);
            quizIdHidden.disabled = false;
            if (learningOutcomesField) {
                learningOutcomesField.name = 'learning_outcomes';
            }
        }
    }

    if (courseSelector) {
        courseSelector.addEventListener('change', (event) => {
            const selectedId = event.target.value;
            fillCourseInfoForm(selectedId);
        });
    }

    editButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const courseKey = btn.dataset.courseKey;
            if (!courseKey) {
                return;
            }
            setActive('manage-courses');
            if (courseSelector) {
                courseSelector.value = courseKey;
            }
            fillCourseInfoForm(courseKey);
            courseSelector?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    jumpAddCourseBtn?.addEventListener('click', () => {
        setActive('manage-courses');
        addCourseForm?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
</script>
</body>
</html>
