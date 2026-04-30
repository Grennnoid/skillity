<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Skillify</title>
    <style>
        :root {
            --bg: #060a14;
            --panel: rgba(12, 20, 37, 0.86);
            --line: rgba(151, 177, 226, 0.24);
            --text: #e7f0ff;
            --muted: #93a8ce;
            --primary: #45d0ff;
            --primary-2: #7cf6d6;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(900px 460px at 18% -12%, rgba(69, 208, 255, 0.2), transparent 58%),
                radial-gradient(900px 460px at 82% -18%, rgba(124, 246, 214, 0.18), transparent 58%),
                linear-gradient(180deg, #050911 0%, #060a12 100%);
            min-height: 100vh;
            padding: 28px 14px;
        }

        .container {
            width: min(980px, 95vw);
            margin: 0 auto;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .top h1 {
            margin: 0;
            font-size: 30px;
            letter-spacing: -0.2px;
        }

        .top a {
            color: #9deeff;
            text-decoration: none;
            font-size: 14px;
        }

        .top a:hover { text-decoration: underline; }

        .grid {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 14px;
        }

        .card {
            border: 1px solid var(--line);
            background: var(--panel);
            border-radius: 18px;
            padding: 18px;
            backdrop-filter: blur(8px);
        }

        .profile-summary {
            text-align: center;
        }

        .avatar {
            width: 144px;
            height: 144px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 52px;
            font-weight: 700;
            color: #031222;
            background: linear-gradient(120deg, var(--primary), var(--primary-2));
            margin: 0 auto 12px;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(124, 246, 214, 0.45);
            box-shadow: 0 0 0 4px rgba(69, 208, 255, 0.14);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .avatar:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 0 5px rgba(69, 208, 255, 0.2);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .camera-overlay {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            background: rgba(6, 10, 20, 0.45);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .avatar:hover .camera-overlay {
            opacity: 1;
        }

        .camera-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(7, 14, 28, 0.8);
            color: #f5fbff;
            display: grid;
            place-items: center;
            font-size: 14px;
            line-height: 1;
        }

        .meta {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .profile-name {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 34px;
            line-height: 1.1;
        }

        .profile-role-status {
            color: #d8e7ff;
            font-size: 20px;
            line-height: 1.5;
            font-weight: 600;
            margin-top: 8px;
        }

        .favorite-courses {
            margin-top: 10px;
            text-align: left;
            border-top: 1px solid var(--line);
            padding-top: 10px;
        }

        .favorite-courses strong {
            display: block;
            margin-bottom: 6px;
            color: #dff1ff;
            font-size: 14px;
        }

        .favorite-courses ul {
            margin: 0;
            padding-left: 18px;
            display: grid;
            gap: 5px;
        }

        .favorite-courses a {
            color: #cce2ff;
            text-decoration: none;
            font-size: 13px;
        }

        .favorite-courses a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        h2 {
            margin: 0 0 10px;
            font-size: 18px;
        }

        .fields { display: grid; gap: 10px; }

        label {
            display: block;
            font-size: 13px;
            color: #c7d8f6;
            margin-bottom: 6px;
        }

        input, textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 12px;
            color: var(--text);
            background: rgba(7, 14, 28, 0.84);
            font-size: 14px;
            outline: none;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input:focus, textarea:focus {
            border-color: rgba(69, 208, 255, 0.72);
            box-shadow: 0 0 0 3px rgba(69, 208, 255, 0.15);
        }

        .btn {
            border: 0;
            border-radius: 12px;
            padding: 11px 13px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            color: #041220;
            background: linear-gradient(120deg, var(--primary), var(--primary-2));
        }

        .btn-danger {
            color: #fff;
            background: linear-gradient(120deg, #ff6b7d, #ff9068);
        }

        .flash {
            border: 1px solid rgba(73, 214, 139, 0.4);
            background: rgba(12, 42, 24, 0.5);
            color: #b4f2cc;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .error {
            border: 1px solid rgba(255, 107, 125, 0.45);
            background: rgba(72, 21, 33, 0.45);
            color: #ffc4cc;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .danger-card {
            border: 1px solid rgba(255, 107, 125, 0.45);
            background: rgba(72, 21, 33, 0.3);
        }

        .danger-note {
            color: #ffb7c0;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        @media (max-width: 860px) {
            .grid { grid-template-columns: 1fr; }
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
<div class="container">
    <div class="top">
        <h1>My Profile</h1>
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
        @elseif(auth()->user()->role === 'dosen')
            <a href="{{ route('dosen.dashboard') }}">Back to Dashboard</a>
        @else
            <a href="{{ route('student.dashboard') }}">Back to Dashboard</a>
        @endif
    </div>

    @if(session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <div class="grid">
        <aside class="card profile-summary">
            @php
                $profileImageUrl = auth()->user()->profile_image ? asset('storage/'.auth()->user()->profile_image) : null;
            @endphp

            <form id="profileImageForm" action="{{ route('profile.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" id="profileImageInput" name="profile_image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" style="display:none;">
                <div class="avatar" id="avatarTrigger" title="Ganti foto profil">
                    @if($profileImageUrl)
                        <img src="{{ $profileImageUrl }}" alt="Profile picture">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                    <div class="camera-overlay">
                        <span class="camera-icon">📷</span>
                    </div>
                </div>
            </form>

            <h2 class="profile-name">{{ auth()->user()->name }}</h2>
            <p class="meta">
                Email: {{ auth()->user()->email }}
            </p>
            <p class="profile-role-status">
                Role: {{ ucfirst(auth()->user()->role) }}<br>
                Status: {{ ucfirst(auth()->user()->account_status ?? 'active') }}
            </p>
            @if(!empty(auth()->user()->bio))
                <p class="meta" style="margin-top: 8px;">{{ auth()->user()->bio }}</p>
            @endif

            <div class="favorite-courses">
                <strong>{{ auth()->user()->role === 'dosen' ? 'Courses' : 'Favorite Courses' }}</strong>
                @if(auth()->user()->role === 'dosen')
                    @if(!empty($dosenCourses) && $dosenCourses->count() > 0)
                        <ul>
                            @foreach($dosenCourses as $course)
                                <li>
                                    <a href="{{ route('courses.quiz.show', ['quiz' => $course->id]) }}">{{ $course->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="meta" style="margin:0;">Belum ada course yang kamu buat.</p>
                    @endif
                @else
                    @if(!empty($favoriteCourses) && $favoriteCourses->count() > 0)
                        <ul>
                            @foreach($favoriteCourses as $course)
                                <li>
                                    <a href="{{ str_starts_with($course->course_slug, 'quiz-') ? route('courses.quiz.show', ['quiz' => (int) str_replace('quiz-', '', $course->course_slug)]) : route('courses.frontend-craft') }}">
                                        {{ $course->course_title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="meta" style="margin:0;">Belum ada favorite course.</p>
                    @endif
                @endif
            </div>
        </aside>

        <section style="display:grid; gap:14px;">
            <div class="card">
                <h2>Update Profile</h2>
                <form class="fields" action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                    </div>

                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                    </div>

                    <div>
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" placeholder="Tulis bio kamu...">{{ old('bio', auth()->user()->bio) }}</textarea>
                    </div>

                    <button class="btn btn-primary" type="submit">Save Profile</button>
                </form>
            </div>

            <div class="card">
                <h2>Change Password</h2>
                <form class="fields" action="{{ route('password.update') }}" method="POST">
                    @csrf

                    <div>
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div>
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>

                    <div>
                        <label for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>

                    <button class="btn btn-primary" type="submit">Update Password</button>
                </form>
            </div>

            <div class="card danger-card">
                <h2>Delete Account</h2>
                <p class="danger-note">Tindakan ini permanen dan tidak bisa dibatalkan. Semua akses akun akan hilang.</p>
                <form class="fields" action="{{ route('profile.destroy') }}" method="POST" onsubmit="return confirm('Yakin ingin hapus akun? Tindakan ini tidak bisa dibatalkan.');">
                    @csrf
                    @method('DELETE')

                    <div>
                        <label for="current_password_delete">Confirm Password</label>
                        <input type="password" id="current_password_delete" name="current_password_delete" required>
                    </div>

                    <button class="btn btn-danger" type="submit">Delete My Account</button>
                </form>
            </div>
        </section>
    </div>

    <footer class="site-footer">
        <p>&copy; {{ date('Y') }} Skillify</p>
    </footer>
</div>

<script>
    const avatarTrigger = document.getElementById('avatarTrigger');
    const profileImageInput = document.getElementById('profileImageInput');
    const profileImageForm = document.getElementById('profileImageForm');

    avatarTrigger.addEventListener('click', () => {
        profileImageInput.click();
    });

    profileImageInput.addEventListener('change', () => {
        if (profileImageInput.files.length > 0) {
            profileImageForm.submit();
        }
    });
</script>
</body>
</html>
