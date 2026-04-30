<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.landing.title_page') }}</title>
    <style>
        :root {
            --bg: #070b14;
            --panel: rgba(13, 21, 39, 0.72);
            --text: #e6efff;
            --muted: #90a3c7;
            --line: rgba(154, 178, 225, 0.25);
            --primary: #45d0ff;
            --primary-2: #7cf6d6;
            --shadow: 0 28px 60px rgba(2, 7, 20, 0.45);
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", "Inter", "Helvetica Neue", Arial, sans-serif;
            color: var(--text);
            overflow-x: hidden;
            overflow-y: auto;
            background:
                radial-gradient(1100px 580px at 14% -12%, rgba(69, 208, 255, 0.24), transparent 60%),
                radial-gradient(1000px 520px at 86% -16%, rgba(124, 246, 214, 0.2), transparent 56%),
                linear-gradient(180deg, #050911 0%, #060a12 100%);
        }

        .grid-overlay {
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 46px 46px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), transparent 92%);
        }

        .container {
            width: min(1120px, 92vw);
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 2;
        }

        .nav {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 18px;
            padding: 29px 0 12px;
            position: sticky;
            top: 0;
            z-index: 30;
            backdrop-filter: blur(12px);
            background: linear-gradient(180deg, rgba(5, 9, 17, 0.92), rgba(5, 9, 17, 0.55));
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 19px;
            letter-spacing: 0.3px;
            color: var(--text);
            text-decoration: none;
        }

        .brand-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: linear-gradient(140deg, var(--primary), var(--primary-2));
            box-shadow: 0 0 22px rgba(69, 208, 255, 0.65);
        }

        .nav-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(16px, 2vw, 26px);
            min-width: 0;
            flex-wrap: wrap;
        }

        .link, .dropdown-toggle {
            color: #c8d8f5;
            text-decoration: none;
            font-size: clamp(16px, 1.5vw, 19px);
            letter-spacing: 0.2px;
            background: none;
            border: 0;
            cursor: pointer;
            padding: 10px 5px;
            white-space: nowrap;
        }

        .link:hover, .dropdown-toggle:hover { color: #f0f6ff; }

        .dropdown { position: relative; }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 264px;
            border: 1px solid var(--line);
            background: rgba(8, 14, 28, 0.96);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 12px;
            display: none;
            box-shadow: var(--shadow);
        }

        .dropdown.open .dropdown-menu { display: block; }

        .dropdown-item {
            display: block;
            color: #dbe6ff;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 18px;
        }

        .dropdown-item:hover { background: rgba(69, 208, 255, 0.13); }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
            min-width: 0;
        }

        .locale-switcher {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(17, 27, 48, 0.6);
        }

        .locale-switcher span {
            color: var(--muted);
            font-size: 12px;
            padding-left: 6px;
            white-space: nowrap;
        }

        .locale-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            padding: 8px 10px;
            border-radius: 999px;
            color: #d9e8ff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .locale-pill.active {
            color: #041220;
            background: linear-gradient(120deg, var(--primary), var(--primary-2));
        }

        .actions form { margin: 0; }

        .profile-menu { position: relative; }

        .profile-menu::after {
            content: "";
            position: absolute;
            right: -2px;
            bottom: 8px;
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
            width: 66px;
            height: 66px;
            border-radius: 50%;
            border: 2px solid rgba(124, 246, 214, 0.35);
            background: #0f172b;
            color: #f3f7ff;
            font-weight: 700;
            font-size: 19px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 0 0 4px rgba(69, 208, 255, 0.12);
            overflow: hidden;
            line-height: 1;
        }

        .profile-trigger:hover { border-color: rgba(124, 246, 214, 0.5); }

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
            min-width: 250px;
            border: 1px solid var(--line);
            background: rgba(8, 14, 28, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            padding: 8px;
            display: none;
            box-shadow: var(--shadow);
        }

        .profile-menu.open .profile-dropdown { display: block; }

        .profile-head {
            padding: 10px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 6px;
        }

        .profile-head strong {
            display: block;
            font-size: 16px;
            margin-bottom: 4px;
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
            font-size: 16px;
            display: block;
            cursor: pointer;
        }

        .profile-item:hover { background: rgba(69, 208, 255, 0.13); }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 600;
            font-size: clamp(16px, 1.45vw, 19px);
            padding: 12px 19px;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
            white-space: nowrap;
        }

        .btn-outline {
            color: #d9e8ff;
            border: 1px solid var(--line);
            background: rgba(17, 27, 48, 0.6);
        }

        .btn-outline:hover {
            transform: translateY(-1px);
            border-color: rgba(124, 246, 214, 0.5);
        }

        .btn-primary {
            color: #041220;
            background: linear-gradient(120deg, var(--primary), var(--primary-2));
            box-shadow: 0 12px 28px rgba(69, 208, 255, 0.26);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 34px rgba(69, 208, 255, 0.35);
        }

        .hero {
            padding: 36px 0 18px;
            position: relative;
            display: flex;
        }

        .hero-card {
            border: 1px solid var(--line);
            background: var(--panel);
            backdrop-filter: blur(12px);
            border-radius: 26px;
            padding: clamp(22px, 3.2vw, 38px);
            box-shadow: var(--shadow);
            width: 100%;
        }

        .tag {
            display: inline-flex;
            padding: 8px 13px;
            border: 1px solid rgba(124, 246, 214, 0.45);
            border-radius: 999px;
            font-size: 12px;
            color: #b8f9e8;
            letter-spacing: 0.2px;
            background: rgba(13, 40, 43, 0.45);
        }

        h1 {
            margin: 18px 0 12px;
            font-size: clamp(28px, 3.8vw, 56px);
            line-height: 1.06;
            letter-spacing: -0.8px;
            max-width: 13ch;
        }

        .subtitle {
            margin: 0;
            max-width: 52ch;
            color: var(--muted);
            font-size: clamp(14px, 1.5vw, 18px);
            line-height: 1.72;
        }

        .hero-actions {
            margin-top: 18px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .stats {
            margin-top: 22px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .stat {
            border: 1px solid var(--line);
            background: rgba(14, 21, 38, 0.67);
            border-radius: 14px;
            padding: 14px;
        }

        .stat strong {
            display: block;
            font-size: 20px;
            margin-bottom: 6px;
            color: #f4f8ff;
        }

        .stat span {
            color: #9bb0d7;
            font-size: 13px;
        }

        .story-grid {
            display: grid;
            gap: 18px;
            margin-top: 10px;
            padding-bottom: 10px;
        }

        .story-section {
            border: 1px solid var(--line);
            background: rgba(11, 18, 34, 0.74);
            border-radius: 24px;
            padding: clamp(22px, 3vw, 34px);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .section-head {
            max-width: 62ch;
            margin-bottom: 18px;
        }

        .section-head h2 {
            margin: 0 0 10px;
            font-size: clamp(24px, 3vw, 38px);
            line-height: 1.12;
            letter-spacing: -0.45px;
        }

        .section-head p {
            margin: 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .feature-grid,
        .audience-grid,
        .showcase-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .step-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .info-card {
            border: 1px solid var(--line);
            background: rgba(14, 21, 38, 0.62);
            border-radius: 18px;
            padding: 18px;
        }

        .info-card .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            color: #b8f9e8;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .info-card h3 {
            margin: 0 0 10px;
            font-size: 20px;
        }

        .info-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
            font-size: 14px;
        }

        .step-number {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: inline-grid;
            place-items: center;
            margin-bottom: 12px;
            background: linear-gradient(120deg, rgba(69, 208, 255, 0.18), rgba(124, 246, 214, 0.18));
            border: 1px solid rgba(124, 246, 214, 0.28);
            color: #dffcff;
            font-weight: 800;
            font-size: 13px;
        }

        .about-panel {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
            gap: 18px;
            align-items: start;
        }

        .bullet-list {
            display: grid;
            gap: 12px;
        }

        .bullet-item {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 14px 16px;
            background: rgba(14, 21, 38, 0.6);
            color: #dce8ff;
            line-height: 1.6;
            font-size: 14px;
        }

        .final-cta {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) auto;
            gap: 18px;
            align-items: center;
        }

        .final-cta p {
            margin: 8px 0 0;
            color: var(--muted);
            line-height: 1.75;
            font-size: 15px;
            max-width: 58ch;
        }

        .final-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .site-footer {
            border-top: 1px solid var(--line);
            margin-top: 24px;
            padding: 18px 0 28px;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 1180px) {
            .locale-switcher span { display: none; }
        }

        @media (max-width: 1040px) {
            .nav {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .nav-links {
                width: 100%;
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: 4px;
                flex-wrap: nowrap;
            }

            .actions {
                width: 100%;
                justify-content: flex-start;
            }

            .stats,
            .feature-grid,
            .audience-grid,
            .showcase-grid,
            .step-grid,
            .about-panel,
            .final-cta {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .actions { gap: 10px; }
            .btn { padding: 11px 16px; }
            .locale-switcher { order: -1; }
            .story-section { border-radius: 20px; }
        }
    </style>
</head>
<body>
    <div class="grid-overlay" aria-hidden="true"></div>

    <div class="container">
        <nav class="nav">
            <a class="brand" href="{{ route('landing') }}">
                <span class="brand-dot"></span>
                <span>Skillify</span>
            </a>

            <div class="nav-links">
                <a class="link" href="#top">{{ __('ui.landing.home') }}</a>

                <div class="dropdown" id="courseDropdown">
                    <button class="dropdown-toggle" type="button" aria-expanded="false" aria-controls="coursesMenu">{{ __('ui.landing.courses') }} v</button>
                    <div class="dropdown-menu" id="coursesMenu">
                        @forelse(($landingCourses ?? []) as $courseItem)
                            <a class="dropdown-item" href="{{ $courseItem['href'] }}">{{ $courseItem['title'] }}</a>
                        @empty
                            <span class="dropdown-item" style="opacity:.75; cursor:default;">{{ __('ui.landing.courses_empty') }}</span>
                        @endforelse
                    </div>
                </div>

                @auth
                    @if(auth()->user()->role === 'student')
                        <a class="link" href="{{ route('teach.entry') }}">{{ __('ui.landing.teach') }}</a>
                    @else
                        <a class="link" href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('dosen.dashboard') }}">{{ __('ui.landing.teach') }}</a>
                    @endif
                @else
                    <a class="link" href="{{ route('teach.entry') }}">{{ __('ui.landing.teach') }}</a>
                @endauth
                <a class="link" href="#platform-story">{{ __('ui.landing.about') }}</a>
            </div>

            <div class="actions">
                <div class="locale-switcher" aria-label="{{ __('ui.locale.switch') }}">
                    <span>{{ __('ui.locale.switch') }}</span>
                    <a class="locale-pill {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ route('locale.switch', 'en') }}">EN</a>
                    <a class="locale-pill {{ app()->getLocale() === 'id' ? 'active' : '' }}" href="{{ route('locale.switch', 'id') }}">ID</a>
                </div>
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a class="btn btn-outline" href="{{ route('admin.dashboard') }}">{{ __('ui.landing.dashboard') }}</a>
                    @elseif(auth()->user()->role === 'dosen')
                        <a class="btn btn-outline" href="{{ route('dosen.dashboard') }}">{{ __('ui.landing.dashboard') }}</a>
                    @else
                        <a class="btn btn-outline" href="{{ route('student.dashboard') }}">{{ __('ui.landing.dashboard') }}</a>
                    @endif

                    @php
                        $nameParts = preg_split('/\s+/', trim(auth()->user()->name));
                        $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1).substr($nameParts[1] ?? '', 0, 1));
                        $initials = $initials !== '' ? $initials : 'U';
                        $profileImageUrl = auth()->user()->profile_image ? asset('storage/'.auth()->user()->profile_image) : null;
                    @endphp
                    <div class="profile-menu" id="profileMenuLanding">
                        <a class="profile-trigger" href="{{ route('profile.show') }}" id="profileTriggerLanding" aria-expanded="false">
                            @if($profileImageUrl)
                                <img src="{{ $profileImageUrl }}" alt="Profile picture" class="profile-image">
                            @else
                                {{ $initials }}
                            @endif
                        </a>
                        <div class="profile-dropdown">
                            <div class="profile-head">
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ auth()->user()->email }}</span>
                            </div>
                            <a class="profile-item" href="{{ route('profile.show') }}">{{ __('ui.landing.profile_settings') }}</a>
                            <a class="profile-item" href="{{ route('login', ['switch' => 1]) }}">{{ __('ui.landing.switch_account') }}</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="profile-item">{{ __('ui.landing.logout') }}</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="btn btn-outline" href="{{ route('login') }}">{{ __('ui.landing.login') }}</a>
                    <a class="btn btn-primary" href="{{ route('register') }}">{{ __('ui.landing.sign_up') }}</a>
                @endauth
            </div>
        </nav>

        <section class="hero" id="top">
            <div class="hero-card">
                <span class="tag">{{ __('ui.landing.hero_tag') }}</span>
                <h1>{{ __('ui.landing.hero_title') }}</h1>
                <p class="subtitle">{{ __('ui.landing.hero_subtitle') }}</p>

                <div class="hero-actions">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">{{ __('ui.landing.open_dashboard') }}</a>
                        @elseif(auth()->user()->role === 'dosen')
                            <a class="btn btn-primary" href="{{ route('dosen.dashboard') }}">{{ __('ui.landing.open_dashboard') }}</a>
                        @else
                            <a class="btn btn-primary" href="{{ route('student.dashboard') }}">{{ __('ui.landing.continue_learning') }}</a>
                        @endif
                        <a class="btn btn-outline" href="{{ route('login', ['switch' => 1]) }}">{{ __('ui.landing.switch_account') }}</a>
                    @else
                        <a class="btn btn-primary" href="{{ route('register') }}">{{ __('ui.landing.create_account') }}</a>
                        <a class="btn btn-outline" href="{{ route('login') }}">{{ __('ui.landing.already_have_account') }}</a>
                    @endauth
                </div>

                <div class="stats">
                    <div class="stat">
                        <strong>{{ number_format((int) (($landingStats['total_courses'] ?? 0))) }}</strong>
                        <span>{{ __('ui.landing.available_courses') }}</span>
                    </div>
                    <div class="stat">
                        <strong>{{ number_format((int) (($landingStats['total_learners'] ?? 0))) }}</strong>
                        <span>{{ __('ui.landing.active_learners') }}</span>
                    </div>
                    <div class="stat">
                        <strong>{{ number_format((int) (($landingStats['total_mentors'] ?? 0))) }}</strong>
                        <span>{{ __('ui.landing.active_mentors') }}</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="story-grid">
            <section class="story-section" id="platform-story">
                <div class="section-head">
                    <h2>{{ __('ui.landing.section_platform') }}</h2>
                    <p>{{ __('ui.landing.section_platform_text') }}</p>
                </div>
                <div class="feature-grid">
                    <article class="info-card">
                        <span class="eyebrow">01</span>
                        <h3>{{ __('ui.landing.feature_1_title') }}</h3>
                        <p>{{ __('ui.landing.feature_1_text') }}</p>
                    </article>
                    <article class="info-card">
                        <span class="eyebrow">02</span>
                        <h3>{{ __('ui.landing.feature_2_title') }}</h3>
                        <p>{{ __('ui.landing.feature_2_text') }}</p>
                    </article>
                    <article class="info-card">
                        <span class="eyebrow">03</span>
                        <h3>{{ __('ui.landing.feature_3_title') }}</h3>
                        <p>{{ __('ui.landing.feature_3_text') }}</p>
                    </article>
                    <article class="info-card">
                        <span class="eyebrow">04</span>
                        <h3>{{ __('ui.landing.feature_4_title') }}</h3>
                        <p>{{ __('ui.landing.feature_4_text') }}</p>
                    </article>
                </div>
            </section>

            <section class="story-section">
                <div class="section-head">
                    <h2>{{ __('ui.landing.section_how_title') }}</h2>
                    <p>{{ __('ui.landing.section_how_text') }}</p>
                </div>
                <div class="step-grid">
                    <article class="info-card">
                        <div class="step-number">1</div>
                        <h3>{{ __('ui.landing.how_1_title') }}</h3>
                        <p>{{ __('ui.landing.how_1_text') }}</p>
                    </article>
                    <article class="info-card">
                        <div class="step-number">2</div>
                        <h3>{{ __('ui.landing.how_2_title') }}</h3>
                        <p>{{ __('ui.landing.how_2_text') }}</p>
                    </article>
                    <article class="info-card">
                        <div class="step-number">3</div>
                        <h3>{{ __('ui.landing.how_3_title') }}</h3>
                        <p>{{ __('ui.landing.how_3_text') }}</p>
                    </article>
                    <article class="info-card">
                        <div class="step-number">4</div>
                        <h3>{{ __('ui.landing.how_4_title') }}</h3>
                        <p>{{ __('ui.landing.how_4_text') }}</p>
                    </article>
                </div>
            </section>

            <section class="story-section">
                <div class="about-panel">
                    <div class="section-head" style="margin-bottom: 0;">
                        <h2>{{ __('ui.landing.section_about_title') }}</h2>
                        <p>{{ __('ui.landing.section_about_text') }}</p>
                    </div>
                    <div class="bullet-list">
                        <div class="bullet-item">{{ __('ui.landing.about_point_1') }}</div>
                        <div class="bullet-item">{{ __('ui.landing.about_point_2') }}</div>
                        <div class="bullet-item">{{ __('ui.landing.about_point_3') }}</div>
                    </div>
                </div>
            </section>

            <section class="story-section">
                <div class="section-head">
                    <h2>{{ __('ui.landing.section_showcase_title') }}</h2>
                    <p>{{ __('ui.landing.section_showcase_text') }}</p>
                </div>
                <div class="showcase-grid">
                    <article class="info-card">
                        <span class="eyebrow">Flow</span>
                        <h3>{{ __('ui.landing.showcase_1_title') }}</h3>
                        <p>{{ __('ui.landing.showcase_1_text') }}</p>
                    </article>
                    <article class="info-card">
                        <span class="eyebrow">AI</span>
                        <h3>{{ __('ui.landing.showcase_2_title') }}</h3>
                        <p>{{ __('ui.landing.showcase_2_text') }}</p>
                    </article>
                    <article class="info-card">
                        <span class="eyebrow">Quiz</span>
                        <h3>{{ __('ui.landing.showcase_3_title') }}</h3>
                        <p>{{ __('ui.landing.showcase_3_text') }}</p>
                    </article>
                    <article class="info-card">
                        <span class="eyebrow">Insights</span>
                        <h3>{{ __('ui.landing.showcase_4_title') }}</h3>
                        <p>{{ __('ui.landing.showcase_4_text') }}</p>
                    </article>
                </div>
            </section>

            <section class="story-section">
                <div class="section-head">
                    <h2>{{ __('ui.landing.section_audience_title') }}</h2>
                    <p>{{ __('ui.landing.section_audience_text') }}</p>
                </div>
                <div class="audience-grid">
                    <article class="info-card">
                        <span class="eyebrow">Learners</span>
                        <h3>{{ __('ui.landing.audience_1_title') }}</h3>
                        <p>{{ __('ui.landing.audience_1_text') }}</p>
                    </article>
                    <article class="info-card">
                        <span class="eyebrow">Mentors</span>
                        <h3>{{ __('ui.landing.audience_2_title') }}</h3>
                        <p>{{ __('ui.landing.audience_2_text') }}</p>
                    </article>
                    <article class="info-card">
                        <span class="eyebrow">Platform</span>
                        <h3>{{ __('ui.landing.audience_3_title') }}</h3>
                        <p>{{ __('ui.landing.audience_3_text') }}</p>
                    </article>
                </div>
            </section>

            <section class="story-section">
                <div class="final-cta">
                    <div>
                        <h2 style="margin:0;font-size:clamp(24px, 3vw, 40px);line-height:1.1;">{{ __('ui.landing.section_final_title') }}</h2>
                        <p>{{ __('ui.landing.section_final_text') }}</p>
                    </div>
                    <div class="final-actions">
                        <a class="btn btn-primary" href="{{ route('register') }}">{{ __('ui.landing.final_primary') }}</a>
                        <a class="btn btn-outline" href="#top">{{ __('ui.landing.final_secondary') }}</a>
                    </div>
                </div>
            </section>
        </div>

        <footer class="site-footer">
            <p>&copy; {{ date('Y') }} {{ __('ui.landing.footer') }}</p>
        </footer>
    </div>

    <script>
        const courseDropdown = document.getElementById('courseDropdown');
        const courseToggle = courseDropdown.querySelector('.dropdown-toggle');

        courseToggle.addEventListener('click', function () {
            const isOpen = courseDropdown.classList.toggle('open');
            courseToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        const profileMenu = document.getElementById('profileMenuLanding');
        const profileTrigger = document.getElementById('profileTriggerLanding');

        if (profileMenu && profileTrigger) {
            profileTrigger.addEventListener('click', function (event) {
                if (!profileMenu.classList.contains('open')) {
                    event.preventDefault();
                    profileMenu.classList.add('open');
                    profileTrigger.setAttribute('aria-expanded', 'true');
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (!courseDropdown.contains(event.target)) {
                courseDropdown.classList.remove('open');
                courseToggle.setAttribute('aria-expanded', 'false');
            }

            if (profileMenu && !profileMenu.contains(event.target)) {
                profileMenu.classList.remove('open');
                profileTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    </script>
</body>
</html>
