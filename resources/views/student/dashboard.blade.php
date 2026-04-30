<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.student.dashboard_title') }}</title>
    <style>
        :root {
            --bg: #04070f;
            --panel: rgba(12, 18, 34, 0.72);
            --line: rgba(160, 181, 222, 0.24);
            --text: #eef4ff;
            --muted: #9aadd3;
            --primary: #79f0d4;
            --shadow: 0 24px 56px rgba(0, 0, 0, 0.45);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--text);
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            background:
                radial-gradient(980px 540px at 20% -10%, rgba(48, 84, 159, 0.35), transparent 62%),
                radial-gradient(980px 540px at 90% -20%, rgba(121, 240, 212, 0.2), transparent 62%),
                linear-gradient(180deg, #03050b 0%, #060a14 100%);
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 24px 0 44px;
        }

        .nav,
        .hero,
        .lower {
            width: min(1180px, 94vw);
            margin-left: auto;
            margin-right: auto;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .brand {
            font-size: 22px;
            letter-spacing: 0.2px;
            font-weight: 700;
        }

        .brand span {
            display: inline-block;
            width: 24px;
            height: 2px;
            background: var(--text);
            margin-left: 8px;
            opacity: 0.7;
            transform: translateY(-6px);
        }

        .menu {
            display: flex;
            gap: 22px;
            flex-wrap: wrap;
            align-items: center;
        }

        .mobile-menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(8, 14, 28, 0.88);
            color: #e8f2ff;
            cursor: pointer;
            box-shadow: var(--shadow);
        }

        .mobile-menu-toggle span,
        .mobile-menu-toggle::before,
        .mobile-menu-toggle::after {
            content: "";
            display: block;
            width: 18px;
            height: 2px;
            border-radius: 999px;
            background: currentColor;
            transition: transform 0.18s ease, opacity 0.18s ease;
        }

        .mobile-menu-toggle {
            position: relative;
        }

        .mobile-menu-toggle span {
            position: absolute;
        }

        .mobile-menu-toggle::before {
            position: absolute;
            transform: translateY(-6px);
        }

        .mobile-menu-toggle::after {
            position: absolute;
            transform: translateY(6px);
        }

        .mobile-menu-toggle.open span {
            opacity: 0;
        }

        .mobile-menu-toggle.open::before {
            transform: rotate(45deg);
        }

        .mobile-menu-toggle.open::after {
            transform: rotate(-45deg);
        }

        .mobile-nav-panel {
            display: none;
            width: min(360px, calc(100vw - 18px));
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(8, 14, 28, 0.96);
            box-shadow: var(--shadow);
            padding: 14px;
            gap: 14px;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            z-index: 60;
            max-height: min(72vh, 560px);
            overflow: auto;
            scrollbar-width: none;
        }

        .mobile-nav-panel::-webkit-scrollbar {
            display: none;
        }

        .mobile-nav-panel.open {
            display: grid;
        }

        .mobile-nav-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .mobile-nav-link,
        .mobile-nav-action {
            width: 100%;
            display: block;
            text-align: left;
            text-decoration: none;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 12px;
            background: rgba(12, 18, 34, 0.82);
            color: #dbe6ff;
            font-size: 14px;
        }

        .mobile-nav-action {
            cursor: pointer;
        }

        .mobile-nav-link:hover,
        .mobile-nav-action:hover {
            border-color: rgba(121, 240, 212, 0.46);
            color: #ffffff;
        }

        .mobile-nav-group {
            border: 1px solid rgba(160, 181, 222, 0.18);
            border-radius: 14px;
            padding: 12px;
            background: rgba(10, 16, 30, 0.72);
        }

        .mobile-nav-group h4 {
            margin: 0 0 10px;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #a8c0ea;
        }

        .mobile-nav-stack {
            display: grid;
            gap: 8px;
        }

        .mobile-nav-user {
            display: grid;
            gap: 4px;
            margin-bottom: 4px;
        }

        .mobile-nav-user strong {
            font-size: 16px;
        }

        .mobile-nav-user span {
            font-size: 12px;
            color: var(--muted);
        }

        .mobile-nav-empty {
            color: var(--muted);
            font-size: 12px;
            padding: 4px 2px 0;
        }

        .menu a, .menu button {
            background: none;
            border: 0;
            color: #d4e3ff;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            padding: 7px 5px;
        }

        .menu a:hover, .menu button:hover { color: #ffffff; }

        .courses-menu,
        .progress-menu,
        .home-menu {
            position: relative;
        }

        .courses-trigger,
        .progress-trigger,
        .home-trigger {
            background: none;
            border: 0;
            color: #d4e3ff;
            font-size: 16px;
            cursor: pointer;
            padding: 7px 5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .courses-trigger:hover,
        .progress-trigger:hover,
        .home-trigger:hover { color: #ffffff; }

        .courses-dropdown,
        .progress-dropdown,
        .home-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            min-width: 230px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(8, 14, 28, 0.98);
            box-shadow: var(--shadow);
            padding: 8px;
            display: none;
            z-index: 26;
        }

        .courses-menu.open .courses-dropdown,
        .progress-menu.open .progress-dropdown,
        .home-menu.open .home-dropdown { display: block; }

        .courses-item,
        .progress-item,
        .home-item {
            display: block;
            color: #dbe6ff;
            text-decoration: none;
            border-radius: 9px;
            padding: 8px 10px;
            font-size: 13px;
            width: 100%;
            text-align: left;
            border: 0;
            background: transparent;
            cursor: pointer;
        }

        .courses-item:hover,
        .progress-item:hover,
        .home-item:hover { background: rgba(121, 240, 212, 0.13); }

        .courses-empty,
        .progress-empty,
        .home-empty {
            color: var(--muted);
            font-size: 12px;
            padding: 8px 10px;
        }

        .my-courses-menu {
            position: relative;
        }

        .my-courses-trigger {
            background: none;
            border: 0;
            color: #d4e3ff;
            font-size: 16px;
            cursor: pointer;
            padding: 7px 5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .my-courses-trigger:hover { color: #ffffff; }

        .my-courses-trigger::after,
        .mentors-trigger::after,
        .progress-trigger::after,
        .home-trigger::after {
            content: "";
            width: 0;
            height: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 5px solid currentColor;
            opacity: 0.8;
        }

        .my-courses-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            min-width: 220px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(8, 14, 28, 0.98);
            box-shadow: var(--shadow);
            padding: 8px;
            display: none;
            z-index: 25;
        }

        .my-courses-menu.open .my-courses-dropdown { display: block; }

        .my-courses-item {
            display: block;
            text-decoration: none;
            color: #dbe6ff;
            font-size: 13px;
            border-radius: 9px;
            padding: 8px 10px;
        }

        .my-courses-item:hover { background: rgba(121, 240, 212, 0.13); }

        .my-courses-empty {
            color: var(--muted);
            font-size: 12px;
            padding: 8px 10px;
        }

        .mentors-menu {
            position: relative;
        }

        .mentors-trigger {
            background: none;
            border: 0;
            color: #d4e3ff;
            font-size: 16px;
            cursor: pointer;
            padding: 7px 5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .mentors-trigger:hover { color: #ffffff; }

        .mentors-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            min-width: 230px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(8, 14, 28, 0.98);
            box-shadow: var(--shadow);
            padding: 8px;
            display: none;
            z-index: 26;
        }

        .mentors-menu.open .mentors-dropdown { display: block; }

        .mentor-item {
            display: block;
            color: #dbe6ff;
            text-decoration: none;
            border-radius: 9px;
            padding: 8px 10px;
            font-size: 13px;
        }

        .mentor-item:hover { background: rgba(121, 240, 212, 0.13); }

        .mentor-empty {
            color: var(--muted);
            font-size: 12px;
            padding: 8px 10px;
        }

        .profile-menu {
            position: relative;
            margin-left: 2px;
        }

        .profile-menu::after {
            content: "";
            position: absolute;
            right: -2px;
            bottom: 5px;
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
            width: 55px;
            height: 55px;
            border-radius: 50%;
            border: 2px solid rgba(121, 240, 212, 0.38);
            background: #0f172b;
            color: #f3f7ff;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 0 0 4px rgba(121, 240, 212, 0.13);
            overflow: hidden;
            line-height: 1;
        }

        .profile-trigger:hover { border-color: rgba(121, 240, 212, 0.65); }

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
            box-shadow: var(--shadow);
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

        .hero {
            text-align: center;
            margin: 64px auto 30px;
            max-width: 780px;
        }

        .hero h1 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(34px, 6vw, 62px);
            line-height: 1.06;
            letter-spacing: -0.6px;
            font-weight: 500;
        }

        .hero p {
            margin: 14px 0 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .slider-shell {
            margin-top: 26px;
            position: relative;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            padding-bottom: 34px;
        }

        .slider-controls {
            position: absolute;
            right: clamp(16px, 4vw, 48px);
            top: -54px;
            display: flex;
            gap: 8px;
        }

        .ctrl {
            border: 1px solid var(--line);
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: rgba(11, 18, 34, 0.86);
            color: #e6f0ff;
            cursor: pointer;
        }

        .ctrl:hover { border-color: rgba(121, 240, 212, 0.7); }

        .slider {
            display: flex;
            gap: 18px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding: 16px calc((100vw - 276px) / 2) 86px;
            scrollbar-width: none;
        }

        .slider::-webkit-scrollbar { display: none; }

        .card {
            flex: 0 0 276px;
            height: 384px;
            border-radius: 26px;
            border: 1px solid rgba(215, 229, 255, 0.22);
            box-shadow: var(--shadow);
            position: relative;
            overflow: visible;
            scroll-snap-align: center;
            transition: transform 0.35s ease, opacity 0.35s ease;
            transform: scale(0.88) rotateY(8deg);
            opacity: 0.5;
            display: flex;
            align-items: flex-end;
            padding: 18px;
            cursor: pointer;
        }

        .card.has-image {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(to top, rgba(5, 7, 13, 0.86), rgba(5, 7, 13, 0.08));
        }

        .card-content {
            position: relative;
            z-index: 1;
        }

        .card small {
            display: block;
            color: #c8d8fa;
            margin-bottom: 6px;
            font-size: 12px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .card h3 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 36px;
            line-height: 1.05;
            font-weight: 500;
        }

        .card p {
            margin: 8px 0 0;
            font-size: 14px;
            color: #d7e5ff;
            line-height: 1.55;
        }

        .card.active {
            transform: scale(1) rotateY(0deg);
            opacity: 1;
        }

        .card-link {
            color: inherit;
            text-decoration: none;
        }

        .c1 { background: radial-gradient(circle at 25% 20%, #ff5d4f 0%, #731610 46%, #18090a 100%); }
        .c2 { background: radial-gradient(circle at 62% 24%, #ffe87e 0%, #8c6224 44%, #17130e 100%); }
        .c3 { background: radial-gradient(circle at 35% 26%, #5fd9ff 0%, #1e607f 46%, #0c1219 100%); }
        .c4 { background: radial-gradient(circle at 60% 18%, #ec84ff 0%, #633878 48%, #100b14 100%); }
        .c5 { background: radial-gradient(circle at 40% 28%, #7dffbe 0%, #1c7050 48%, #0a1310 100%); }
        .c6 { background: radial-gradient(circle at 45% 25%, #ff9f8a 0%, #7d3f32 48%, #130d0b 100%); }

        .lower {
            margin-top: 34px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .panel {
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 16px;
            padding: 16px;
        }

        .panel h4 {
            margin: 0 0 10px;
            font-size: 15px;
        }

        .panel p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .recommendation-panel {
            display: grid;
            gap: 12px;
        }

        .recommendation-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid rgba(121, 240, 212, 0.28);
            background: rgba(12, 29, 39, 0.38);
            color: #dffff8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .recommendation-badge::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: linear-gradient(120deg, #67d7ff, #79f0d4);
            box-shadow: 0 0 10px rgba(103, 215, 255, 0.7);
        }

        .recommendation-title {
            margin: 0;
            font-size: 26px;
            line-height: 1.08;
            letter-spacing: -0.3px;
        }

        .recommendation-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .recommendation-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 600;
        }

        .recommendation-primary {
            color: #041220;
            background: linear-gradient(120deg, #67d7ff, #79f0d4);
        }

        .recommendation-secondary {
            color: #d8e5ff;
            border: 1px solid var(--line);
            background: rgba(9, 15, 29, 0.78);
        }

        .course-list {
            margin: 0;
            padding-left: 18px;
            display: grid;
            gap: 8px;
        }

        .course-list a {
            color: #dbe6ff;
            text-decoration: none;
            font-size: 13px;
        }

        .course-list a:hover { color: #ffffff; }

        .progress-track {
            margin-top: 10px;
            height: 8px;
            border-radius: 99px;
            background: rgba(145, 170, 219, 0.24);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 64%;
            background: linear-gradient(120deg, #67d7ff, #79f0d4);
        }

        @media (max-width: 980px) {
            .lower { grid-template-columns: 1fr; }
            .slider-controls {
                position: static;
                justify-content: flex-end;
                margin-bottom: 8px;
                padding-right: clamp(10px, 3vw, 22px);
            }
        }

        @media (max-width: 700px) {
            .nav {
                align-items: center;
            }
            .menu { display: none; }
            .mobile-menu-toggle { display: inline-flex; }
            .slider { padding-inline: calc((100vw - 78vw) / 2); }
            .card { flex-basis: 78vw; height: 320px; }
            .hero { margin-top: 40px; }
        }

        @media (max-width: 520px) {
            .nav,
            .hero,
            .lower,
            .site-footer {
                width: min(100%, calc(100vw - 18px));
            }

            .brand {
                font-size: 18px;
            }

            .brand span {
                width: 18px;
                margin-left: 6px;
            }

            .hero h1 {
                font-size: 33px;
            }

            .hero p {
                font-size: 18px;
            }

            .mobile-nav-panel {
                width: calc(100vw - 18px);
                padding: 12px;
                gap: 12px;
            }

            .mobile-nav-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }

        .site-footer {
            width: min(1180px, 94vw);
            margin: 26px auto 0;
            border-top: 1px solid var(--line);
            padding-top: 16px;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }

    </style>
</head>
<body>
<div class="container">
    @if(session('success'))
        <div style="width:min(1180px,94vw);margin:0 auto 12px;border:1px solid rgba(73,214,139,.4);border-radius:10px;background:rgba(12,42,24,.5);color:#b4f2cc;padding:10px 12px;font-size:13px;">
            {{ session('success') }}
        </div>
    @endif
    <header class="nav">
        <div class="brand">{{ __('ui.course.skillify') }}<span></span></div>
        <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-expanded="false" aria-controls="mobileNavPanel">
            <span></span>
        </button>
        <nav class="menu">
            <a href="{{ route('student.courses') }}">{{ __('ui.student.courses') }}</a>
            <a href="{{ route('student.mentors') }}">{{ __('ui.student.mentors') }}</a>
            <div class="progress-menu" id="progressMenu">
                <button class="progress-trigger" id="progressTrigger" type="button" aria-expanded="false">{{ __('ui.student.progress') }}</button>
                <div class="progress-dropdown">
                    <button class="progress-item" type="button">{{ __('ui.student.weekly_progress') }}: 64%</button>
                    <button class="progress-item" type="button">{{ __('ui.student.lessons_completed') }}: 8</button>
                    <button class="progress-item" type="button">{{ __('ui.student.badges') }}: 3</button>
                </div>
            </div>
            <div class="my-courses-menu" id="myCoursesMenu">
                <button class="my-courses-trigger" id="myCoursesTrigger" type="button" aria-expanded="false">{{ __('ui.student.my_courses') }}</button>
                <div class="my-courses-dropdown">
                    @forelse(($enrolledCourses ?? []) as $course)
                        <a class="my-courses-item" href="{{ $course['roadmap_route'] }}">{{ $course['title'] }}</a>
                    @empty
                        <div class="my-courses-empty">{{ __('ui.student.no_enrolled_courses') }}</div>
                    @endforelse
                </div>
            </div>
            <div class="home-menu" id="homeMenu">
                <button class="home-trigger" id="homeTrigger" type="button" aria-expanded="false">{{ __('ui.student.home') }}</button>
                <div class="home-dropdown">
                    <a class="home-item" href="{{ route('landing') }}">{{ __('ui.student.landing_page') }}</a>
                    <a class="home-item" href="{{ route('student.dashboard') }}">{{ __('ui.student.dashboard') }}</a>
                    <a class="home-item" href="{{ route('profile.show') }}">{{ __('ui.student.profile') }}</a>
                </div>
            </div>
            @php
                $nameParts = preg_split('/\s+/', trim(auth()->user()->name));
                $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1).substr($nameParts[1] ?? '', 0, 1));
                $initials = $initials !== '' ? $initials : 'U';
                $profileImageUrl = auth()->user()->profile_image ? asset('storage/'.auth()->user()->profile_image) : null;
            @endphp
            <div class="profile-menu" id="profileMenuStudent">
                <a class="profile-trigger" href="{{ route('profile.show') }}" id="profileTriggerStudent" aria-expanded="false">
                    @if($profileImageUrl)
                        <img src="{{ $profileImageUrl }}" alt="{{ __('ui.student.profile') }}" class="profile-image">
                    @else
                        {{ $initials }}
                    @endif
                </a>
                <div class="profile-dropdown">
                    <div class="profile-head">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ auth()->user()->email }}</span>
                    </div>
                    <a class="profile-item" href="{{ route('profile.show') }}">{{ __('ui.student.profile_settings') }}</a>
                    <a class="profile-item" href="{{ route('login', ['switch' => 1]) }}">{{ __('ui.student.switch_account') }}</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="profile-item">{{ __('ui.student.logout') }}</button>
                    </form>
                </div>
            </div>
        </nav>
        @php
            $weeklyProgress = 64;
            $lessonsCompleted = 8;
            $badgeCount = 3;
        @endphp
        <section class="mobile-nav-panel" id="mobileNavPanel">
            <div class="mobile-nav-grid">
                <a class="mobile-nav-link" href="{{ route('student.courses') }}">{{ __('ui.student.courses') }}</a>
                <a class="mobile-nav-link" href="{{ route('student.mentors') }}">{{ __('ui.student.mentors') }}</a>
                <a class="mobile-nav-link" href="{{ route('landing') }}">{{ __('ui.student.landing_page') }}</a>
                <a class="mobile-nav-link" href="{{ route('student.dashboard') }}">{{ __('ui.student.dashboard') }}</a>
                <a class="mobile-nav-link" href="{{ route('profile.show') }}">{{ __('ui.student.profile') }}</a>
                <a class="mobile-nav-link" href="{{ route('login', ['switch' => 1]) }}">{{ __('ui.student.switch_account') }}</a>
            </div>

            <div class="mobile-nav-group">
                <h4>{{ __('ui.student.my_courses') }}</h4>
                <div class="mobile-nav-stack">
                    @forelse(($enrolledCourses ?? []) as $course)
                        <a class="mobile-nav-link" href="{{ $course['roadmap_route'] }}">{{ $course['title'] }}</a>
                    @empty
                        <div class="mobile-nav-empty">{{ __('ui.student.no_enrolled_courses') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="mobile-nav-group">
                <h4>{{ __('ui.student.progress') }}</h4>
                <div class="mobile-nav-stack">
                    <div class="mobile-nav-link">{{ __('ui.student.weekly_progress') }}: {{ $weeklyProgress }}%</div>
                    <div class="mobile-nav-link">{{ __('ui.student.lessons_completed') }}: {{ $lessonsCompleted }}</div>
                    <div class="mobile-nav-link">{{ __('ui.student.badges') }}: {{ $badgeCount }}</div>
                </div>
            </div>

            <div class="mobile-nav-group">
                <h4>{{ __('ui.student.profile') }}</h4>
                <div class="mobile-nav-user">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->email }}</span>
                </div>
                <div class="mobile-nav-stack">
                    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="mobile-nav-action">{{ __('ui.student.logout') }}</button>
                    </form>
                </div>
            </div>
        </section>
    </header>

    <section class="hero">
        <h1>{{ __('ui.student.hero_title') }}</h1>
        <p>{{ __('ui.student.hero_subtitle', ['name' => auth()->user()->name]) }}</p>
    </section>

    <section class="slider-shell">
        <div class="slider-controls">
            <button class="ctrl" id="prevBtn" aria-label="Previous">&#8592;</button>
            <button class="ctrl" id="nextBtn" aria-label="Next">&#8594;</button>
        </div>

        <div class="slider" id="courseSlider">
            @php
                $cardPalette = ['c1', 'c2', 'c3', 'c4', 'c5', 'c6'];
            @endphp
            @foreach(($carouselCourses ?? []) as $index => $course)
                <article
                    class="card {{ empty($course['image']) ? $cardPalette[$index % count($cardPalette)] : 'has-image' }} {{ $index === 0 ? 'active' : '' }}"
                    data-href="{{ $course['href'] }}"
                    @if(!empty($course['image']))
                        style="background-image: url('{{ $course['image'] }}');"
                    @endif
                >
                    <div class="card-content">
                        <small>{{ $course['category'] }}</small>
                        <h3>{{ $course['title'] }}</h3>
                        <p>{{ $course['description'] }}</p>
                        <p><a class="card-link" href="{{ $course['href'] }}"><strong>{{ __('ui.student.view_course') }} -&gt;</strong></a></p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    
    <section class="lower">
        <article class="panel">
            <h4>{{ __('ui.student.continue_learning_title') }}</h4>
            <p>{{ __('ui.student.continue_learning_text') }}</p>
            <div class="progress-track"><div class="progress-fill"></div></div>
            <p style="margin-top: 8px;">{{ __('ui.student.progress_complete', ['percent' => 64]) }}</p>
        </article>

        <article class="panel">
            <h4>{{ __('ui.student.weekly_focus_title') }}</h4>
            <p>{{ __('ui.student.weekly_focus_text') }}</p>
        </article>

        @if(!empty($pathfinderRecommendation))
            <article class="panel recommendation-panel">
                <span class="recommendation-badge">{{ __('ui.pathfinder.nav_label') }}</span>
                <div>
                    <p class="recommendation-title">{{ $pathfinderRecommendation['title'] }}</p>
                    <p>{{ $pathfinderRecommendation['summary'] }}</p>
                </div>
                <div class="recommendation-actions">
                    <a class="recommendation-primary" href="{{ $pathfinderRecommendation['href'] }}">{{ __('ui.pathfinder.view_course') }}</a>
                    <a class="recommendation-secondary" href="{{ route('student.pathfinder') }}">{{ __('ui.pathfinder.retake') }}</a>
                </div>
            </article>
        @endif
    </section>

    <footer class="site-footer">
        <p>&copy; {{ date('Y') }} {{ __('ui.student.footer') }}</p>
    </footer>
</div>

<script>
    const slider = document.getElementById('courseSlider');
    const cards = Array.from(slider.querySelectorAll('.card'));
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let activeIndex = 0;

    function setActiveCard() {
        const center = slider.scrollLeft + slider.clientWidth / 2;
        let closest = cards[0];
        let minDistance = Number.POSITIVE_INFINITY;

        cards.forEach((card) => {
            const cardCenter = card.offsetLeft + card.clientWidth / 2;
            const distance = Math.abs(center - cardCenter);
            if (distance < minDistance) {
                minDistance = distance;
                closest = card;
            }
        });

        cards.forEach((card) => card.classList.remove('active'));
        closest.classList.add('active');
        activeIndex = cards.indexOf(closest);
    }

    function goToCard(index) {
        if (index < 0) {
            index = cards.length - 1;
        }
        if (index >= cards.length) {
            index = 0;
        }

        activeIndex = index;
        cards.forEach((card) => card.classList.remove('active'));
        const target = cards[index];
        target.classList.add('active');
        target.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }

    prevBtn.addEventListener('click', () => goToCard(activeIndex - 1));
    nextBtn.addEventListener('click', () => goToCard(activeIndex + 1));
    slider.addEventListener('scroll', () => window.requestAnimationFrame(setActiveCard));
    window.addEventListener('resize', setActiveCard);

    cards.forEach((card) => {
        card.addEventListener('mouseenter', () => {
            card.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            cards.forEach((item) => item.classList.remove('active'));
            card.classList.add('active');
        });

        card.addEventListener('click', (event) => {
            if (event.target.closest('a, button, input, select, textarea, form')) {
                return;
            }

            const href = card.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    const profileMenu = document.getElementById('profileMenuStudent');
    const profileTrigger = document.getElementById('profileTriggerStudent');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileNavPanel = document.getElementById('mobileNavPanel');
    const myCoursesMenu = document.getElementById('myCoursesMenu');
    const myCoursesTrigger = document.getElementById('myCoursesTrigger');
    const progressMenu = document.getElementById('progressMenu');
    const progressTrigger = document.getElementById('progressTrigger');
    const homeMenu = document.getElementById('homeMenu');
    const homeTrigger = document.getElementById('homeTrigger');

    const dropdownMenus = [
        { menu: myCoursesMenu, trigger: myCoursesTrigger },
        { menu: progressMenu, trigger: progressTrigger },
        { menu: homeMenu, trigger: homeTrigger },
        { menu: profileMenu, trigger: profileTrigger },
    ];

    function closeAllMenus(exceptMenu = null) {
        dropdownMenus.forEach(({ menu, trigger }) => {
            if (!menu || !trigger || menu === exceptMenu) {
                return;
            }
            menu.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        });
    }

    function closeMobileMenu() {
        if (!mobileNavPanel || !mobileMenuToggle) {
            return;
        }

        mobileNavPanel.classList.remove('open');
        mobileMenuToggle.classList.remove('open');
        mobileMenuToggle.setAttribute('aria-expanded', 'false');
    }

    if (mobileMenuToggle && mobileNavPanel) {
        mobileMenuToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            const willOpen = !mobileNavPanel.classList.contains('open');
            mobileNavPanel.classList.toggle('open', willOpen);
            mobileMenuToggle.classList.toggle('open', willOpen);
            mobileMenuToggle.setAttribute('aria-expanded', String(willOpen));
        });

        document.addEventListener('click', function (event) {
            if (!mobileNavPanel.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                closeMobileMenu();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 700) {
                closeMobileMenu();
            }
        });
    }

    if (profileMenu && profileTrigger) {
        profileTrigger.addEventListener('click', function (event) {
            if (!profileMenu.classList.contains('open')) {
                event.preventDefault();
                closeAllMenus(profileMenu);
                profileMenu.classList.add('open');
                profileTrigger.setAttribute('aria-expanded', 'true');
            } else {
                profileMenu.classList.remove('open');
                profileTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('click', function (event) {
            if (!profileMenu.contains(event.target)) {
                profileMenu.classList.remove('open');
                profileTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (myCoursesMenu && myCoursesTrigger) {
        myCoursesTrigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const willOpen = !myCoursesMenu.classList.contains('open');
            closeAllMenus(willOpen ? myCoursesMenu : null);
            myCoursesMenu.classList.toggle('open', willOpen);
            myCoursesTrigger.setAttribute('aria-expanded', String(willOpen));
        });

        document.addEventListener('click', function (event) {
            if (!myCoursesMenu.contains(event.target)) {
                myCoursesMenu.classList.remove('open');
                myCoursesTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (progressMenu && progressTrigger) {
        progressTrigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const willOpen = !progressMenu.classList.contains('open');
            closeAllMenus(willOpen ? progressMenu : null);
            progressMenu.classList.toggle('open', willOpen);
            progressTrigger.setAttribute('aria-expanded', String(willOpen));
        });

        document.addEventListener('click', function (event) {
            if (!progressMenu.contains(event.target)) {
                progressMenu.classList.remove('open');
                progressTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (homeMenu && homeTrigger) {
        homeTrigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const willOpen = !homeMenu.classList.contains('open');
            closeAllMenus(willOpen ? homeMenu : null);
            homeMenu.classList.toggle('open', willOpen);
            homeTrigger.setAttribute('aria-expanded', String(willOpen));
        });

        document.addEventListener('click', function (event) {
            if (!homeMenu.contains(event.target)) {
                homeMenu.classList.remove('open');
                homeTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    setActiveCard();
</script>
@include('partials.student-chatbot')
</body>
</html>
