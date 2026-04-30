<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.auth.register_title') }}</title>
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
            overflow-x: hidden;
        }

        .auth-shell {
            width: min(500px, 100%);
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

        .card {
            width: 100%;
            border: 1px solid var(--line);
            background: var(--panel);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 26px 56px rgba(3, 8, 21, 0.45);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
            letter-spacing: -0.2px;
        }

        p {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .field { margin-bottom: 16px; }

        label {
            display: block;
            margin-bottom: 7px;
            color: #c7d8f6;
            font-size: 13px;
        }

        input, select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 14px;
            background: rgba(7, 14, 28, 0.84);
            color: var(--text);
            outline: none;
        }

        select {
            appearance: none;
            padding-right: 38px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23d4e3ff' d='M6 8 0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 12px 8px;
        }

        input:focus, select:focus {
            border-color: rgba(69, 208, 255, 0.72);
            box-shadow: 0 0 0 3px rgba(69, 208, 255, 0.15);
        }

        .error {
            margin-bottom: 14px;
            border: 1px solid rgba(251, 113, 133, 0.4);
            border-radius: 12px;
            background: rgba(80, 21, 33, 0.5);
            color: #ffd5db;
            padding: 11px 12px;
            font-size: 13px;
        }

        .btn {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #041220;
            background: linear-gradient(120deg, var(--primary), var(--primary-2));
            cursor: pointer;
        }

        .btn:hover { filter: brightness(1.05); }

        .foot {
            margin-top: 15px;
            font-size: 13px;
            color: var(--muted);
            text-align: center;
        }

        a {
            color: #9deeff;
            text-decoration: none;
        }

        a:hover { text-decoration: underline; }

        .site-footer {
            margin-top: 18px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 560px) {
            body {
                place-items: start center;
                padding: 16px;
            }

            .auth-shell {
                width: min(100%, calc(100vw - 32px));
            }

            .card {
                border-radius: 16px;
                padding: 22px 18px;
            }

            h1 {
                font-size: 24px;
            }

            p {
                margin-bottom: 18px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
    <div class="auth-topbar">
        <div class="locale-switcher" aria-label="{{ __('ui.locale.switch') }}">
            <span>{{ __('ui.locale.switch') }}</span>
            <a class="locale-pill {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ route('locale.switch', 'en') }}">EN</a>
            <a class="locale-pill {{ app()->getLocale() === 'id' ? 'active' : '' }}" href="{{ route('locale.switch', 'id') }}">ID</a>
        </div>
    </div>
    <main class="card">
        <h1>{{ __('ui.auth.register_heading') }}</h1>
        <p>{{ __('ui.auth.register_subtitle') }}</p>

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="field">
                <label for="name">{{ __('ui.auth.full_name') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="field">
                <label for="email">{{ __('ui.auth.email') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="field">
                <label for="password">{{ __('ui.auth.password') }}</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('ui.auth.confirm_password') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn">{{ __('ui.auth.sign_up') }}</button>
        </form>

        <div class="foot">
            {{ __('ui.auth.already_have_account') }} <a href="{{ route('login') }}">{{ __('ui.auth.login') }}</a>
            <br>
            <a href="{{ route('landing') }}">{{ __('ui.auth.back_landing') }}</a>
        </div>
    </main>
    <footer class="site-footer">
        <p>&copy; {{ date('Y') }} Skillify</p>
    </footer>
    </div>
</body>
</html>
