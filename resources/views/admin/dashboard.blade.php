<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.admin.title_page') }}</title>
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
            --danger: #ff6b7d;
            --ok: #49d68b;
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
            letter-spacing: 0.2px;
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

        .nav-btn:hover {
            border-color: rgba(69, 208, 255, 0.5);
        }

        .nav-btn.active {
            border-color: rgba(69, 208, 255, 0.75);
            background: linear-gradient(120deg, rgba(69, 208, 255, 0.25), rgba(124, 246, 214, 0.2));
            color: #f3f9ff;
        }

        .side-actions {
            display: grid;
            gap: 8px;
        }

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

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 24px;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
        }

        .panel {
            border: 1px solid var(--line);
            background: var(--panel);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 16px;
        }

        .panel h2 {
            margin: 0 0 6px;
            font-size: 19px;
        }

        .panel p { margin: 0 0 12px; }

        .view {
            display: none;
        }

        .view.active {
            display: block;
        }

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

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        table {
            width: 100%;
            min-width: 780px;
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

        .fields {
            display: grid;
            gap: 8px;
        }

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

        input:focus, select:focus, textarea:focus {
            border-color: rgba(69, 208, 255, 0.7);
            box-shadow: 0 0 0 3px rgba(69, 208, 255, 0.14);
        }

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

        .inline-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .inline-form select { min-width: 110px; }

        .flash {
            margin-bottom: 10px;
            border: 1px solid rgba(73, 214, 139, 0.4);
            border-radius: 10px;
            background: rgba(12, 42, 24, 0.5);
            color: #b4f2cc;
            padding: 10px 12px;
            font-size: 13px;
        }

        .error-box {
            margin-bottom: 10px;
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
            margin-bottom: 12px;
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
            font-size: 12px;
        }

        @media (max-width: 1180px) {
            .cards-3 { grid-template-columns: 1fr; }
            .cards-2 { grid-template-columns: 1fr; }
            .kpi { grid-template-columns: repeat(2, minmax(0, 1fr)); }
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
            .nav {
                grid-template-columns: 1fr 1fr;
            }
            .content {
                padding: 16px;
            }
            .side-actions {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .nav { grid-template-columns: 1fr; }
            .kpi { grid-template-columns: 1fr; }
            .inline-form { flex-direction: column; align-items: stretch; }
            .question-bank-summary { grid-template-columns: 1fr; }
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .sidebar {
                padding: 12px;
            }
            .content {
                padding: 12px 10px 16px;
            }
            .nav-btn,
            .btn {
                width: 100%;
            }
            .table-wrap {
                margin-left: -2px;
                margin-right: -2px;
            }
        }

        .site-footer {
            margin-top: 18px;
            border-top: 1px solid var(--line);
            padding-top: 14px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
@php($adminUi = __('ui.admin'))
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <span class="dot"></span>
            <span>{{ $adminUi['control_center'] }}</span>
        </div>

        <div class="locale-switcher">
            <span>{{ __('ui.locale.switch') }}</span>
            <a href="{{ route('locale.switch', ['locale' => 'en']) }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">{{ __('ui.locale.en') }}</a>
            <a href="{{ route('locale.switch', ['locale' => 'id']) }}" class="{{ app()->getLocale() === 'id' ? 'active' : '' }}">{{ __('ui.locale.id') }}</a>
        </div>

        <div class="side-meta">
            {{ $adminUi['login_as'] }} <strong>{{ auth()->user()->name }}</strong><br>
            {{ $adminUi['role'] }}: <strong>{{ strtoupper(auth()->user()->role) }}</strong>
        </div>

        <nav class="nav" id="sideNav">
            <button class="nav-btn active" data-target="user-management">{{ $adminUi['user_management'] }}</button>
            <button class="nav-btn" data-target="manage-courses">{{ $adminUi['manage_courses'] }}</button>
            <button class="nav-btn" data-target="manage-quiz">{{ $adminUi['manage_quiz'] }}</button>
            <button class="nav-btn" data-target="gradebook">{{ $adminUi['gradebook'] }}</button>
            <button class="nav-btn" data-target="ai-analytics">{{ $adminUi['ai_analytics'] }}</button>
            <button class="nav-btn" data-target="system-settings">{{ $adminUi['system_settings'] }}</button>
        </nav>

        <div class="side-actions">
            <a class="btn btn-ghost" href="{{ route('landing') }}">{{ $adminUi['back_to_landing'] }}</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger" type="submit" style="width: 100%;">{{ $adminUi['logout'] }}</button>
            </form>
        </div>
    </aside>

    <main class="content">
        <div class="topbar">
            <div>
                <h1>{{ $adminUi['dashboard'] }}</h1>
                <p class="muted">{{ $adminUi['dashboard_intro'] }}</p>
            </div>
        </div>

        @if(session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <section class="kpi">
            <article class="box"><strong>{{ $stats['total_users'] }}</strong><span class="muted">{{ $adminUi['total_users'] }}</span></article>
            <article class="box"><strong>{{ $stats['active_accounts'] }}</strong><span class="muted">{{ $adminUi['active_accounts'] }}</span></article>
            <article class="box"><strong>{{ $stats['total_quizzes'] }}</strong><span class="muted">{{ $adminUi['total_quizzes'] }}</span></article>
            <article class="box"><strong>{{ $stats['submissions'] }}</strong><span class="muted">{{ $adminUi['submissions'] }}</span></article>
        </section>

        <section class="panel view active" id="user-management">
            <h2>{{ $adminUi['user_management'] }}</h2>
            <p class="muted">{{ $adminUi['user_management_text'] }}</p>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>{{ $adminUi['name'] }}</th>
                        <th>{{ $adminUi['email'] }}</th>
                        <th>{{ $adminUi['role'] }}</th>
                        <th>{{ $adminUi['status'] }}</th>
                        <th>{{ $adminUi['role'] }} Switcher</th>
                        <th>Account {{ $adminUi['status'] }}</th>
                        <th>{{ $adminUi['delete'] }} Account</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td><span class="status {{ $user->account_status }}">{{ ucfirst($user->account_status) }}</span></td>
                            <td>
                                <form class="inline-form" action="{{ route('admin.users.role', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role">
                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                        <option value="dosen" @selected($user->role === 'dosen')>Dosen</option>
                                        <option value="student" @selected($user->role === 'student')>Student</option>
                                    </select>
                                    <button class="btn btn-primary" type="submit">{{ $adminUi['save'] }}</button>
                                </form>
                            </td>
                            <td>
                                <form class="inline-form" action="{{ route('admin.users.status', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="account_status">
                                        <option value="active" @selected($user->account_status === 'active')>Active</option>
                                        <option value="suspended" @selected($user->account_status === 'suspended')>Suspended</option>
                                    </select>
                                    <button class="btn btn-ghost" type="submit">{{ $adminUi['update'] }}</button>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Delete account for {{ $user->name }}? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">{{ $adminUi['delete'] }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">{{ $adminUi['no_users'] }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card" style="margin-top:12px;">
                <h3>{{ $adminUi['lecturer_requests'] }}</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>{{ $adminUi['name'] }}</th>
                            <th>{{ $adminUi['email'] }}</th>
                            <th>{{ $adminUi['requested_role'] }}</th>
                            <th>{{ $adminUi['requested_at'] }}</th>
                            <th>{{ $adminUi['action'] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($dosenRequests as $requestUser)
                            <tr>
                                <td>{{ $requestUser->name }}</td>
                                <td>{{ $requestUser->email }}</td>
                                <td>{{ ucfirst($requestUser->requested_role ?? 'dosen') }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($requestUser->created_at)->format('d M Y H:i') }}</td>
                                <td>
                                    <form class="inline-form" action="{{ route('admin.users.dosen-request', $requestUser->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="approve">
                                        <button class="btn btn-primary" type="submit">{{ $adminUi['approve'] }}</button>
                                    </form>
                                    <form class="inline-form" action="{{ route('admin.users.dosen-request', $requestUser->id) }}" method="POST" style="margin-top:6px;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="reject">
                                        <button class="btn btn-danger" type="submit">{{ $adminUi['reject'] }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">{{ $adminUi['no_lecturer_requests'] }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="panel view" id="manage-courses">
            <h2>{{ $adminUi['manage_courses'] }}</h2>
            <p class="muted">{{ $adminUi['manage_courses_text'] }}</p>
            <div style="margin-bottom: 10px;">
                <button class="btn btn-primary" type="button" id="jumpAddCourseBtnAdmin">{{ $adminUi['add_new_course'] }}</button>
            </div>

            <div class="cards-2">
                <article class="card" id="add-course-form-admin">
                    <h3>{{ $adminUi['create_new_course'] }}</h3>
                    <form class="fields" action="{{ route('admin.courses.store') }}" method="POST">
                        @csrf
                        <input type="text" name="title" placeholder="{{ $adminUi['course_title_placeholder'] }}" required>
                        <input type="text" name="category" placeholder="{{ $adminUi['course_category_placeholder'] }}" required>
                        <select name="difficulty" required>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                        <button class="btn btn-primary" type="submit">{{ $adminUi['create_course'] }}</button>
                    </form>
                </article>

                <article class="card">
                    <h3>{{ $adminUi['all_courses'] }}</h3>
                    <div class="table-wrap">
                        <table style="min-width:100%;">
                            <thead>
                            <tr><th>{{ $adminUi['title'] }}</th><th>{{ $adminUi['category'] }}</th><th>{{ $adminUi['difficulty'] }}</th><th>{{ $adminUi['owner'] }}</th><th>{{ $adminUi['action'] }}</th></tr>
                            </thead>
                            <tbody>
                            @forelse($manageableCourses as $course)
                                <tr>
                                    <td>{{ $course->title }}</td>
                                    <td>{{ $course->category }}</td>
                                    <td>{{ ucfirst($course->difficulty) }}</td>
                                    <td>{{ $course->owner_name }}</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-ghost js-edit-course-admin"
                                            data-course-key="{{ $course->key }}"
                                            style="padding:6px 10px;font-size:11px;"
                                        >
                                            {{ $adminUi['edit_info'] }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">{{ $adminUi['no_courses'] }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            <div class="card" style="margin-top: 12px;">
                <h3>{{ $adminUi['course_info_editor'] }}</h3>
                <p class="muted">{{ $adminUi['course_info_text'] }}</p>
                <form class="fields" id="courseInfoFormAdmin" action="{{ route('admin.courses.info.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <select id="courseInfoSelectorAdmin" required>
                        <option value="">{{ $adminUi['choose_course'] }}</option>
                        @foreach($manageableCourses as $courseOption)
                            <option value="{{ $courseOption->key }}">{{ $courseOption->title }}</option>
                        @endforeach
                    </select>
                    <a
                        id="chapterBuilderLinkAdmin"
                        class="btn btn-ghost"
                        href="{{ route('instructor.courses.roadmap', ['course' => 'frontend-craft']) }}"
                        style="display:none; width: fit-content;"
                    >
                        {{ $adminUi['open_chapter_builder'] }}
                    </a>
                    <input type="hidden" name="quiz_id" id="courseInfoQuizIdAdmin">
                    <input type="text" name="hero_title" id="courseInfoHeroTitleAdmin" placeholder="{{ $adminUi['hero_title_placeholder'] }}">
                    <input type="url" name="hero_background_url" id="courseInfoHeroBackgroundUrlAdmin" placeholder="{{ $adminUi['hero_background_placeholder'] }}">
                    <input type="file" name="hero_background_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <p class="muted" style="margin:0;">{{ $adminUi['hero_background_note'] }}</p>
                    <input type="text" name="tagline" id="courseInfoTaglineAdmin" placeholder="{{ $adminUi['tagline_placeholder'] }}">
                    <input type="text" name="instructor_name" id="courseInfoInstructorNameAdmin" placeholder="{{ $adminUi['instructor_name_placeholder'] }}">
                    <input type="url" name="instructor_photo_url" id="courseInfoInstructorPhotoAdmin" placeholder="{{ $adminUi['instructor_photo_placeholder'] }}">
                    <textarea name="about" id="courseInfoAboutAdmin" placeholder="{{ $adminUi['about_placeholder'] }}"></textarea>
                    <input type="text" name="target_audience" id="courseInfoTargetAudienceAdmin" placeholder="{{ $adminUi['target_audience_placeholder'] }}">
                    <input type="text" name="duration_text" id="courseInfoDurationTextAdmin" placeholder="{{ $adminUi['duration_text_placeholder'] }}">
                    <textarea name="syllabus_lines" id="courseInfoSyllabusLinesAdmin" placeholder="{{ $adminUi['syllabus_placeholder'] }}"></textarea>
                    <textarea name="learning_outcomes" id="courseInfoLearningOutcomesAdmin" placeholder="{{ $adminUi['learning_outcomes_placeholder'] }}"></textarea>
                    <input type="url" name="trailer_url" id="courseInfoTrailerUrlAdmin" placeholder="{{ $adminUi['trailer_url_placeholder'] }}">
                    <input type="url" name="trailer_poster_url" id="courseInfoTrailerPosterUrlAdmin" placeholder="{{ $adminUi['trailer_poster_placeholder'] }}">
                    <button class="btn btn-primary" type="submit">{{ $adminUi['save_course_info'] }}</button>
                </form>

                <div class="table-wrap">
                    <table style="min-width:100%;">
                        <thead>
                        <tr>
                            <th>{{ $adminUi['course'] }}</th>
                            <th>Tagline</th>
                            <th>{{ $adminUi['audience'] }}</th>
                            <th>{{ $adminUi['updated'] }}</th>
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
                            <tr><td colspan="4">{{ $adminUi['no_custom_course_info'] }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="panel view" id="manage-quiz">
            <h2>{{ $adminUi['manage_quiz'] }}</h2>
            <p class="muted">{{ $adminUi['manage_quiz_text'] }}</p>
            @php($adminAiPreview = session('admin_ai_question_preview'))

            <div class="cards-2">
                <article class="card">
                    <h3>{{ $adminUi['ai_quiz_generator'] }}</h3>
                    <form class="fields" action="{{ route('admin.questions.ai.preview') }}" method="POST">
                        @csrf
                        <select name="course_key" required>
                            <option value="">{{ $adminUi['choose_target_course'] }}</option>
                            @foreach($manageableCourses as $course)
                                <option value="{{ $course->key }}" @selected(old('course_key', $adminAiPreview['course_key'] ?? '') == $course->key)>
                                    {{ $course->title }} ({{ ucfirst($course->difficulty) }})
                                </option>
                            @endforeach
                        </select>
                        <textarea name="generation_notes" placeholder="{{ $adminUi['generation_notes_placeholder'] }}" required>{{ old('generation_notes', $adminAiPreview['generation_notes'] ?? '') }}</textarea>
                        <select name="difficulty" required>
                            <option value="beginner" @selected(old('difficulty', $adminAiPreview['difficulty'] ?? '') === 'beginner')>Beginner</option>
                            <option value="intermediate" @selected(old('difficulty', $adminAiPreview['difficulty'] ?? '') === 'intermediate')>Intermediate</option>
                            <option value="advanced" @selected(old('difficulty', $adminAiPreview['difficulty'] ?? '') === 'advanced')>Advanced</option>
                        </select>
                        <input type="number" name="question_count" min="1" max="10" value="{{ old('question_count', $adminAiPreview['question_count'] ?? 5) }}" placeholder="{{ $adminUi['question_count_placeholder'] }}">
                        <select name="question_type_mode" required>
                            <option value="mcq" @selected(old('question_type_mode', $adminAiPreview['question_type_mode'] ?? '') === 'mcq')>MCQ Only</option>
                            <option value="essay" @selected(old('question_type_mode', $adminAiPreview['question_type_mode'] ?? '') === 'essay')>Essay Only</option>
                            <option value="true_false" @selected(old('question_type_mode', $adminAiPreview['question_type_mode'] ?? '') === 'true_false')>True / False Only</option>
                            <option value="mixed_mcq_essay" @selected(old('question_type_mode', $adminAiPreview['question_type_mode'] ?? '') === 'mixed_mcq_essay')>Mixed MCQ + Essay</option>
                            <option value="mixed_all" @selected(old('question_type_mode', $adminAiPreview['question_type_mode'] ?? '') === 'mixed_all')>Mixed All Types</option>
                        </select>
                        <input type="number" name="placement_after_chapter" min="1" max="40" value="{{ old('placement_after_chapter', $adminAiPreview['placement_after_chapter'] ?? '') }}" placeholder="{{ $adminUi['insert_after_chapter_placeholder'] }}">
                        <p class="muted" style="margin:0;">{{ $adminUi['auto_pop_quiz_note'] }}</p>
                        <button class="btn btn-primary" type="submit">{{ $adminUi['preview_questions'] }}</button>
                    </form>
                </article>

                <article class="card">
                    <h3>{{ $adminUi['manual_question'] }}</h3>
                    <form class="fields" action="{{ route('admin.questions.store') }}" method="POST">
                        @csrf
                        <select name="course_key" required>
                            <option value="">{{ $adminUi['choose_course_or_quiz'] }}</option>
                            @foreach($manageableCourses as $course)
                                <option value="{{ $course->key }}">{{ $course->title }} ({{ ucfirst($course->difficulty) }})</option>
                            @endforeach
                        </select>
                        <textarea name="question_text" placeholder="{{ $adminUi['question_placeholder'] }}" required></textarea>
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
                        <input type="text" name="category" placeholder="{{ $adminUi['category'] }}">
                        <input type="number" name="placement_after_chapter" min="1" max="40" placeholder="{{ $adminUi['insert_after_chapter_placeholder'] }}">
                        <p class="muted" style="margin:0;">{{ $adminUi['auto_pop_quiz_note'] }}</p>
                        <input type="text" name="correct_answer" placeholder="{{ $adminUi['correct_answer_placeholder'] }}">
                        <textarea name="options_json" placeholder="{{ $adminUi['options_placeholder'] }}"></textarea>
                        <button class="btn btn-primary" type="submit">{{ $adminUi['save_question'] }}</button>
                    </form>
                </article>
            </div>

            @if(is_array($adminAiPreview) && !empty($adminAiPreview['questions']))
                <div class="card" style="margin-top:12px;">
                    <h3>{{ __('ui.admin.ai_preview', ['course' => $adminAiPreview['course_title']]) }}</h3>
                    <p class="muted">{{ $adminUi['placement'] }}:
                        @if(!empty($adminAiPreview['placement_after_chapter']))
                            {{ __('ui.admin.after_chapter', ['chapter' => $adminAiPreview['placement_after_chapter']]) }}
                        @else
                            {{ $adminUi['no_pop_quiz_placement'] }}
                        @endif
                        &bull; {{ $adminUi['type_mode'] }}: {{ str_replace('_', ' ', $adminAiPreview['question_type_mode']) }}
                    </p>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ $adminUi['question'] }}</th>
                                <th>{{ $adminUi['type'] }}</th>
                                <th>{{ $adminUi['difficulty'] }}</th>
                                <th>{{ $adminUi['answer_options'] }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($adminAiPreview['questions'] as $index => $question)
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
                    <form action="{{ route('admin.questions.ai.save') }}" method="POST" style="margin-top:10px;">
                        @csrf
                        <button class="btn btn-primary" type="submit">{{ $adminUi['save_preview_to_bank'] }}</button>
                    </form>
                </div>
            @endif

            <div class="card" style="margin-top: 12px;">
                <h3>{{ $adminUi['stored_question_bank'] }}</h3>
                <p class="muted">{{ $adminUi['stored_question_bank_text'] }}</p>
                <div class="question-bank-summary">
                    <article class="question-bank-metric">
                        <strong>{{ $questionBankPresentation['summary']['total'] ?? 0 }}</strong>
                        <span class="muted">{{ $adminUi['stored_total'] }}</span>
                    </article>
                    <article class="question-bank-metric">
                        <strong>{{ $questionBankPresentation['summary']['pop_quiz'] ?? 0 }}</strong>
                        <span class="muted">{{ $adminUi['stored_pop_quiz'] }}</span>
                    </article>
                    <article class="question-bank-metric">
                        <strong>{{ $questionBankPresentation['summary']['ai'] ?? 0 }}</strong>
                        <span class="muted">{{ $adminUi['stored_ai'] }}</span>
                    </article>
                    <article class="question-bank-metric">
                        <strong>{{ $questionBankPresentation['summary']['manual'] ?? 0 }}</strong>
                        <span class="muted">{{ $adminUi['stored_manual'] }}</span>
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
                                                            <form action="{{ route('admin.questions.delete', $row->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-danger" type="submit">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <p class="question-bank-text">{{ \Illuminate\Support\Str::limit($row->question_text, 180) }}</p>
                                                    <div class="question-bank-meta">
                                                        <span class="muted">Created by {{ $row->creator_name ?? '-' }}</span>
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
                        <div class="question-bank-empty">Question bank masih kosong. Tambah soal manual atau generate dari AI dulu, nanti semuanya akan terkumpul rapi di sini.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="panel view" id="gradebook">
            <h2>{{ $adminUi['scores_monitoring'] }}</h2>
            <p class="muted">{{ $adminUi['scores_monitoring_text'] }}</p>
            <div style="margin-bottom: 10px;">
                <a class="btn btn-ghost" href="{{ route('admin.grades.export') }}">Export Scores (CSV)</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>{{ $adminUi['student'] }}</th>
                        <th>Quiz</th>
                        <th>Auto Score</th>
                        <th>Manual Score</th>
                        <th>{{ $adminUi['status'] }}</th>
                        <th>Submitted</th>
                        <th>Manual Grading</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($submissions as $submission)
                        <tr>
                            <td>{{ $submission->student_name ?? '-' }}</td>
                            <td>{{ $submission->quiz_title ?? '-' }}</td>
                            <td>{{ $submission->score ?? '-' }}</td>
                            <td>{{ $submission->manual_score ?? '-' }}</td>
                            <td>{{ ucfirst($submission->status) }}</td>
                            <td>{{ $submission->submitted_at ? \Illuminate\Support\Carbon::parse($submission->submitted_at)->format('d M Y H:i') : '-' }}</td>
                            <td>
                                <form class="fields" action="{{ route('admin.submissions.grade', $submission->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" min="0" max="100" step="0.01" name="manual_score" placeholder="0-100" required>
                                    <input type="text" name="remarks" placeholder="Catatan grading (opsional)">
                                    <button class="btn btn-primary" type="submit">{{ $adminUi['save'] }} Grade</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No submissions yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card" style="margin-top: 12px;">
                <h3>{{ $adminUi['attendance_overview'] }}</h3>
                <div class="cards-3">
                    <article class="card">
                        <strong style="display:block;font-size:22px;">{{ $attendanceStats['total_sessions'] ?? 0 }}</strong>
                        <span class="muted">Total attendance sessions</span>
                    </article>
                    <article class="card">
                        <strong style="display:block;font-size:22px;">{{ $attendanceStats['attended_sessions'] ?? 0 }}</strong>
                        <span class="muted">{{ $adminUi['attendance_counted'] }}</span>
                    </article>
                    <article class="card">
                        <strong style="display:block;font-size:22px;">{{ $attendanceStats['active_consistent_students'] ?? 0 }}</strong>
                        <span class="muted">Students in consistent mode</span>
                    </article>
                </div>

                <div class="table-wrap" style="margin-top:10px;">
                    <table>
                        <thead>
                        <tr>
                            <th>{{ $adminUi['student'] }}</th>
                            <th>{{ $adminUi['course'] }}</th>
                            <th>{{ $adminUi['date'] }}</th>
                            <th>{{ $adminUi['progress'] }}</th>
                            <th>{{ $adminUi['status'] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($attendanceOverview as $attendanceItem)
                            <tr>
                                <td>{{ $attendanceItem->student_name ?? 'Student' }}</td>
                                <td>{{ $attendanceItem->course_title }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($attendanceItem->attendance_date)->format('d M Y') }}</td>
                                <td>{{ $attendanceItem->chapters_completed }}/{{ $attendanceItem->target_chapters }} chapters</td>
                                <td>
                                    <span class="status {{ $attendanceItem->is_attended ? 'active' : 'suspended' }}">
                                        {{ $attendanceItem->is_attended ? $adminUi['counted'] : $adminUi['in_progress'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">{{ $adminUi['no_attendance'] }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="panel view" id="ai-analytics">
            <h2>{{ $adminUi['ai_analytics'] }}</h2>
            <p class="muted">{{ $adminUi['ai_analytics_text'] }}</p>

            <div class="cards-3">
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $platformOverview['active_learners'] ?? 0 }}</strong>
                    <span class="muted">{{ $adminUi['active_learners'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $platformOverview['consistent_rate'] ?? 0 }}%</strong>
                    <span class="muted">{{ $adminUi['consistent_adoption'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $platformOverview['avg_progress'] ?? 0 }}%</strong>
                    <span class="muted">{{ $adminUi['avg_progress'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $platformOverview['attendance_rate'] ?? 0 }}%</strong>
                    <span class="muted">{{ $adminUi['attendance_rate'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $platformOverview['pop_quiz_mastery'] ?? 0 }}%</strong>
                    <span class="muted">{{ $adminUi['pop_quiz_mastery'] }}</span>
                </article>
                <article class="card">
                    <strong style="display:block;font-size:22px;">{{ $platformOverview['qa_answer_rate'] ?? 0 }}%</strong>
                    <span class="muted">{{ $adminUi['qa_coverage'] }}</span>
                </article>
            </div>

            <div class="cards-2" style="margin-top:12px;">
                <article class="card">
                    <h3>{{ $adminUi['seven_day_momentum'] }}</h3>
                    <p class="muted">{{ $adminUi['seven_day_momentum_text'] }}</p>
                    <div class="analytics-bars">
                        @if(!empty($platformWeeklyRows))
                            @foreach($platformWeeklyRows as $row)
                                <div class="bar">
                                    <div style="width: {{ max(14, $row['width']) }}%;">
                                        {{ $row['label'] }} - {{ $row['completions'] }} chapter selesai / {{ $row['attendance'] }} attendance counted
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="muted">{{ $adminUi['no_momentum'] }}</p>
                        @endif
                    </div>
                </article>

                <article class="card">
                    <h3>{{ $adminUi['category_snapshot'] }}</h3>
                    <p class="muted">{{ $adminUi['category_snapshot_text'] }}</p>
                    <div class="analytics-bars">
                        @forelse($learningAnalytics as $row)
                            <div class="bar">
                                <div style="width: {{ min(100, $row->total * 10) }}%;">{{ $row->category }} - {{ $row->total }} {{ $adminUi['activities'] }}</div>
                            </div>
                        @empty
                            <p class="muted">{{ $adminUi['no_category_activity'] }}</p>
                        @endforelse
                    </div>
                </article>
            </div>

            <div class="card" style="margin-top:12px;">
                <h3>{{ $adminUi['course_health'] }}</h3>
                <p class="muted">{{ $adminUi['course_health_text'] }}</p>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>{{ $adminUi['course'] }}</th>
                            <th>{{ $adminUi['mentor'] }}</th>
                            <th>{{ $adminUi['students'] }}</th>
                            <th>{{ $adminUi['avg_progress'] }}</th>
                            <th>{{ $adminUi['attendance_rate'] }}</th>
                            <th>{{ $adminUi['pop_quiz_mastery'] }}</th>
                            <th>{{ $adminUi['open_qa'] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!empty($platformCourseHealthRows))
                            @foreach($platformCourseHealthRows as $row)
                                <tr>
                                    <td>{{ $row['course_title'] }}</td>
                                    <td>{{ $row['mentor_name'] }}</td>
                                    <td>{{ $row['enrolled_students'] }}</td>
                                    <td>{{ $row['avg_progress'] }}%</td>
                                    <td>{{ $row['attendance_rate'] }}%</td>
                                    <td>{{ $row['pop_quiz_mastery'] }}%</td>
                                    <td>{{ $row['open_questions'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="7">{{ $adminUi['no_course_health'] }}</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="margin-top:12px;">
                <h3>{{ $adminUi['dosen_analytics'] }}</h3>
                <p class="muted">{{ $adminUi['dosen_analytics_text'] }}</p>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>{{ $adminUi['mentor'] }}</th>
                            <th>{{ $adminUi['courses'] }}</th>
                            <th>{{ $adminUi['active_learners'] }}</th>
                            <th>{{ $adminUi['avg_progress'] }}</th>
                            <th>{{ $adminUi['attendance_rate'] }}</th>
                            <th>{{ $adminUi['qa_coverage'] }}</th>
                            <th>{{ $adminUi['pop_quiz_mastery'] }}</th>
                            <th>{{ $adminUi['needs_attention'] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!empty($platformDosenRows))
                            @foreach($platformDosenRows as $row)
                                <tr>
                                    <td>{{ $row['mentor_name'] }}</td>
                                    <td>{{ $row['course_count'] }}</td>
                                    <td>{{ $row['active_learners'] }}</td>
                                    <td>{{ $row['avg_progress'] }}%</td>
                                    <td>{{ $row['attendance_rate'] }}%</td>
                                    <td>{{ $row['qa_answer_rate'] }}%</td>
                                    <td>{{ $row['pop_quiz_mastery'] }}%</td>
                                    <td>{{ $row['needs_attention'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="8">{{ $adminUi['no_dosen_analytics'] }}</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cards-2" style="margin-top:12px;">
                <article class="card">
                    <h3>{{ $adminUi['token_monitor'] }}</h3>
                    <p class="muted">{{ $adminUi['total_tokens'] }}: {{ number_format($tokenSummary->total_tokens ?? 0) }}</p>
                    <p class="muted">{{ $adminUi['total_cost'] }}: ${{ number_format((float)($tokenSummary->total_cost ?? 0), 4) }}</p>
                    <div class="table-wrap">
                        <table style="min-width: 100%;">
                            <thead><tr><th>{{ $adminUi['provider'] }}</th><th>{{ $adminUi['model'] }}</th><th>{{ $adminUi['token'] }}</th></tr></thead>
                            <tbody>
                            @forelse($tokenLogs as $log)
                                <tr>
                                    <td>{{ $log->provider }}</td>
                                    <td>{{ $log->model }}</td>
                                    <td>{{ number_format($log->token_count) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">{{ $adminUi['no_token_logs'] }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="card">
                    <h3>{{ $adminUi['feedback_logs'] }}</h3>
                    <p class="muted">{{ $adminUi['feedback_logs_text'] }}</p>
                    <div class="table-wrap">
                        <table style="min-width: 100%;">
                            <thead><tr><th>{{ $adminUi['summary'] }}</th><th>{{ $adminUi['topic'] }}</th><th>{{ $adminUi['sentiment'] }}</th></tr></thead>
                            <tbody>
                            @forelse($feedbackLogs as $item)
                                <tr>
                                    <td>{{ \Illuminate\Support\Str::limit($item->prompt_summary, 70) }}</td>
                                    <td>{{ $item->detected_topic ?? '-' }}</td>
                                    <td>{{ $item->sentiment ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">{{ $adminUi['no_feedback_logs'] }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
        </section>

        <section class="panel view" id="system-settings">
            <h2>{{ $adminUi['system_settings'] }}</h2>
            <p class="muted">{{ $adminUi['system_settings_text'] }}</p>
            <div class="cards-3">
                <article class="card">
                    <h3>{{ $adminUi['maintenance_mode'] }}</h3>
                    <form class="fields" action="{{ route('admin.settings.maintenance') }}" method="POST">
                        @csrf
                        <select name="mode" required>
                            <option value="on">{{ $adminUi['enable_maintenance'] }}</option>
                            <option value="off">{{ $adminUi['disable_maintenance'] }}</option>
                        </select>
                        <button class="btn btn-primary" type="submit">{{ $adminUi['apply'] }}</button>
                    </form>
                </article>

                <article class="card">
                    <h3>{{ $adminUi['site_identity'] }}</h3>
                    <form class="fields" action="{{ route('admin.settings.identity') }}" method="POST">
                        @csrf
                        <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'Skillify' }}" required>
                        <input type="text" name="logo_url" value="{{ $settings['logo_url'] ?? '' }}" placeholder="{{ $adminUi['logo_url_placeholder'] }}">
                        <input type="text" name="theme_color" value="{{ $settings['theme_color'] ?? '#45d0ff' }}" required placeholder="{{ $adminUi['theme_color_placeholder'] }}">
                        <button class="btn btn-primary" type="submit">{{ $adminUi['save_identity'] }}</button>
                    </form>
                </article>

                <article class="card">
                    <h3>{{ $adminUi['update_password'] }}</h3>
                    <form class="fields" action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="password" name="current_password" placeholder="{{ $adminUi['current_password_placeholder'] }}" required>
                        <input type="password" name="new_password" placeholder="{{ $adminUi['new_password_placeholder'] }}" required>
                        <input type="password" name="new_password_confirmation" placeholder="{{ $adminUi['confirm_new_password_placeholder'] }}" required>
                        <button class="btn btn-ghost" type="submit">{{ $adminUi['update_password_button'] }}</button>
                    </form>
                </article>
            </div>

            <div class="cards-2" style="margin-top: 12px;">
                <article class="card">
                    <h3>{{ $adminUi['chatbot_personality'] }}</h3>
                    <form class="fields" action="{{ route('admin.settings.chatbot') }}" method="POST">
                        @csrf
                        <input type="text" name="chatbot_name" value="{{ $settings['chatbot_name'] ?? 'Skillify AI' }}" placeholder="{{ $adminUi['chatbot_name_placeholder'] }}" required>
                        <input type="text" name="chatbot_welcome" value="{{ $settings['chatbot_welcome'] ?? 'Hi, I am here to help with your courses, roadmap, and study questions.' }}" placeholder="{{ $adminUi['chatbot_welcome_placeholder'] }}" required>
                        <input type="text" name="chatbot_placeholder" value="{{ $settings['chatbot_placeholder'] ?? 'Ask about this course, chapter, or your study plan...' }}" placeholder="{{ $adminUi['chatbot_input_placeholder'] }}" required>
                        <textarea name="chatbot_personality" placeholder="{{ $adminUi['chatbot_personality_placeholder'] }}" required>{{ $settings['chatbot_personality'] ?? "You are Skillify AI, a warm and capable learning assistant inside a digital skills platform. Help students understand lessons, stay motivated, break down concepts clearly, and suggest practical next steps. Keep answers supportive, concise, and easy to follow. Do not claim to have accessed grades or hidden platform data unless the user explicitly provides it in the chat." }}</textarea>
                        <button class="btn btn-primary" type="submit">{{ $adminUi['save_chatbot'] }}</button>
                    </form>
                </article>

                <article class="card">
                    <h3>{{ $adminUi['deepseek_setup'] }}</h3>
                    <p class="muted">{{ $adminUi['deepseek_note_1'] }}</p>
                    <p class="muted">{{ $adminUi['deepseek_note_2'] }}</p>
                    <p class="muted">{{ $adminUi['deepseek_note_3'] }}</p>
                </article>
            </div>
        </section>

        <footer class="site-footer">
            <p>&copy; {{ date('Y') }} {{ $adminUi['footer'] }}</p>
        </footer>
    </main>
</div>

<script>
    const nav = document.getElementById('sideNav');
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

    const courseInfoForm = document.getElementById('courseInfoFormAdmin');
    const courseSelector = document.getElementById('courseInfoSelectorAdmin');
    const quizIdHidden = document.getElementById('courseInfoQuizIdAdmin');
    const editButtons = Array.from(document.querySelectorAll('.js-edit-course-admin'));
    const jumpAddCourseBtn = document.getElementById('jumpAddCourseBtnAdmin');
    const addCourseForm = document.getElementById('add-course-form-admin');
    const chapterBuilderLink = document.getElementById('chapterBuilderLinkAdmin');
    const chapterBuilderBaseUrl = @json(url('/instructor/courses'));
    const quizInfoUpdateUrl = "{{ route('admin.courses.info.update') }}";
    const frontendCraftUpdateUrl = "{{ route('admin.courses.frontend-craft.page.update') }}";
    const learningOutcomesField = document.getElementById('courseInfoLearningOutcomesAdmin');

    function fillCourseInfoForm(courseKey) {
        const data = courseInfoData[String(courseKey)] || {};
        document.getElementById('courseInfoHeroTitleAdmin').value = data.hero_title || '';
        document.getElementById('courseInfoHeroBackgroundUrlAdmin').value = data.hero_background_url || '';
        document.getElementById('courseInfoTaglineAdmin').value = data.tagline || '';
        document.getElementById('courseInfoInstructorNameAdmin').value = data.instructor_name || '';
        document.getElementById('courseInfoInstructorPhotoAdmin').value = data.instructor_photo_url || '';
        document.getElementById('courseInfoAboutAdmin').value = data.about || '';
        document.getElementById('courseInfoTargetAudienceAdmin').value = data.target_audience || '';
        document.getElementById('courseInfoDurationTextAdmin').value = data.duration_text || '';
        document.getElementById('courseInfoSyllabusLinesAdmin').value = data.syllabus_lines || '';
        document.getElementById('courseInfoLearningOutcomesAdmin').value = data.learning_outcomes || '';
        document.getElementById('courseInfoTrailerUrlAdmin').value = data.trailer_url || '';
        document.getElementById('courseInfoTrailerPosterUrlAdmin').value = data.trailer_poster_url || '';

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
