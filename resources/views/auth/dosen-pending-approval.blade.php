<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.auth.pending_title') }}</title>
    <style>
        :root {
            --bg: #070b14;
            --panel: rgba(12, 20, 37, 0.88);
            --line: rgba(151, 177, 226, 0.26);
            --text: #e7f0ff;
            --muted: #93a8ce;
            --primary: #45d0ff;
            --primary-2: #7cf6d6;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Segoe UI", "Inter", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(900px 460px at 18% -12%, rgba(69, 208, 255, 0.2), transparent 58%),
                radial-gradient(900px 460px at 82% -18%, rgba(124, 246, 214, 0.18), transparent 58%),
                linear-gradient(180deg, #050911 0%, #060a12 100%);
            padding: 20px;
        }

        .card {
            width: min(700px, 100%);
            border: 1px solid var(--line);
            background: var(--panel);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 26px 56px rgba(3, 8, 21, 0.45);
        }

        .shell {
            width: min(700px, 100%);
        }

        .auth-topbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 14px;
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

        h1 {
            margin: 0 0 10px;
            font-size: 30px;
            letter-spacing: -0.2px;
        }

        p {
            margin: 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .note {
            margin-top: 16px;
            border: 1px solid rgba(69, 208, 255, 0.35);
            border-radius: 12px;
            background: rgba(12, 30, 52, 0.5);
            color: #c9e7ff;
            padding: 12px 14px;
            font-size: 14px;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 14px;
        }

        .btn-outline {
            color: #d9e8ff;
            border: 1px solid var(--line);
            background: rgba(17, 27, 48, 0.6);
        }

        .btn-primary {
            color: #041220;
            background: linear-gradient(120deg, var(--primary), var(--primary-2));
        }

        @media (max-width: 560px) {
            body {
                place-items: start;
                padding: 16px;
            }

            .card {
                border-radius: 16px;
                padding: 22px 18px;
            }

            h1 {
                font-size: 24px;
            }

            p,
            .note {
                font-size: 14px;
            }

            .actions {
                flex-direction: column;
            }

            .btn,
            .actions form {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="shell">
<div class="auth-topbar">
    <div class="locale-switcher" aria-label="{{ __('ui.locale.switch') }}">
        <span>{{ __('ui.locale.switch') }}</span>
        <a class="locale-pill {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ route('locale.switch', 'en') }}">EN</a>
        <a class="locale-pill {{ app()->getLocale() === 'id' ? 'active' : '' }}" href="{{ route('locale.switch', 'id') }}">ID</a>
    </div>
</div>
<main class="card">
    @if(session('success'))
        <div class="note" style="margin-top: 0; margin-bottom: 14px; border-color: rgba(124, 246, 214, 0.4); color: #c8ffe8;">
            {{ session('success') }}
        </div>
    @endif
    <h1>{{ __('ui.auth.pending_heading') }}</h1>
    <p>{{ __('ui.auth.pending_intro') }}</p>
    <div class="note">
        {{ __('ui.auth.pending_note') }}
    </div>
    <div class="actions">
        <a class="btn btn-outline" href="{{ route('landing') }}">{{ __('ui.auth.back_to_landing') }}</a>
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
            @csrf
            <button class="btn btn-primary" type="submit" style="border:0;cursor:pointer;">{{ __('ui.auth.logout') }}</button>
        </form>
    </div>
</main>
</div>
</body>
</html>
