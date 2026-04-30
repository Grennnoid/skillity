<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $courseTitle }} Roadmap | Instructor</title>
    <style>
        :root {
            --bg: #050914;
            --line: rgba(154, 178, 225, 0.24);
            --text: #e9f1ff;
            --muted: #9db1d6;
            --accent: #45d0ff;
            --accent2: #7cf6d6;
            --panel: rgba(11, 19, 35, 0.82);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(180deg, #050914 0%, #061226 100%);
            overflow-x: hidden;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(1200px 620px at 15% -18%, rgba(69, 208, 255, 0.18), transparent 60%),
                radial-gradient(900px 480px at 85% -20%, rgba(124, 246, 214, 0.12), transparent 56%);
            z-index: 0;
        }
        .wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            padding: 18px clamp(16px, 3vw, 36px) 24px;
            display: grid;
            grid-template-rows: auto auto auto auto;
            gap: 12px;
        }
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--line);
            padding-bottom: 12px;
        }
        .links { display: flex; gap: 14px; }
        .link {
            color: #d7e8ff;
            text-decoration: none;
            font-size: 14px;
        }
        .link:hover { color: #ffffff; }
        .hero {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel);
            padding: 16px;
            box-shadow: 0 14px 34px rgba(1, 7, 21, 0.34);
        }
        .hero h1 { margin: 0; font-size: 30px; }
        .hero p { margin: 6px 0 0; color: var(--muted); }

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .controls-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .title-form {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .title-input {
            width: min(360px, 52vw);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 9px 10px;
            color: var(--text);
            background: rgba(8, 14, 27, 0.82);
            font-size: 12px;
            outline: none;
        }

        .controls-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ctrl {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(12, 20, 37, 0.92);
            color: #e9f2ff;
            font-size: 16px;
            cursor: pointer;
        }

        .add-btn {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 9px 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            color: #041220;
            background: linear-gradient(120deg, var(--accent), var(--accent2));
        }

        .road-shell {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(7, 13, 26, 0.74);
            box-shadow: 0 18px 42px rgba(1, 7, 21, 0.4);
            overflow: hidden;
        }

        .road {
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: none;
            scroll-behavior: smooth;
            padding: 8px 0;
        }
        .road::-webkit-scrollbar { display: none; }

        .steps {
            min-width: max-content;
            display: flex;
            gap: 22px;
            align-items: center;
            padding: 26px max(24px, 2.2vw) 32px;
        }
        .step {
            width: 182px;
            text-decoration: none;
            color: var(--text);
            display: grid;
            gap: 10px;
            justify-items: center;
            transition: transform 0.2s ease;
        }
        .step:nth-child(odd) {
            transform: translateY(16px);
        }
        .step:nth-child(even) {
            transform: translateY(-16px);
        }
        .step:hover {
            transform: translateY(-4px);
        }
        .circle {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #ffffff 0%, #f7f7f7 100%);
            border: 5px solid var(--accent);
            box-shadow: 0 14px 22px rgba(4, 10, 22, 0.34);
            display: grid;
            place-items: center;
            text-align: center;
        }
        .circle small {
            display: block;
            color: #8f8793;
            font-size: 11px;
        }
        .circle strong {
            font-size: 44px;
            line-height: 1;
            color: #716b77;
        }
        .meta {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(12, 19, 35, 0.72);
            padding: 10px;
            text-align: center;
        }
        .meta h3 {
            margin: 0;
            font-size: 15px;
        }
        .meta p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }
        .tag {
            display: inline-flex;
            border-radius: 999px;
            padding: 4px 9px;
            border: 1px solid var(--line);
            font-size: 11px;
            margin-top: 6px;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <strong>Instructor Course Builder</strong>
        <div class="links">
            @if(auth()->user()->role === 'admin')
                <a class="link" href="{{ route('admin.dashboard') }}">Back To Admin Dashboard</a>
            @else
                <a class="link" href="{{ route('dosen.dashboard') }}">Back To Dosen Dashboard</a>
            @endif
            <a class="link" href="{{ route('landing') }}">Landing</a>
        </div>
    </div>

    @if(session('success'))
        <div style="margin-bottom:10px;border:1px solid rgba(73,214,139,.4);border-radius:10px;background:rgba(12,42,24,.5);color:#b4f2cc;padding:10px 12px;font-size:13px;">{{ session('success') }}</div>
    @endif

    <section class="hero">
        <h1>{{ $roadmapTitle }} - Chapter Flow</h1>
        <p>Klik circle chapter untuk masuk ke halaman belajar (video + deskripsi). Halaman ini khusus Admin dan Dosen.</p>
    </section>

    <div class="controls">
        <div class="controls-left">
            <form class="title-form" action="{{ route('instructor.courses.roadmap.title.save', ['course' => $courseSlug]) }}" method="POST" style="margin:0;">
                @csrf
                <input class="title-input" type="text" name="roadmap_title" value="{{ old('roadmap_title', $roadmapTitle) }}" placeholder="Roadmap title" required>
                <button class="add-btn" type="submit">Save Title</button>
            </form>
            <form action="{{ route('instructor.courses.chapters.add', ['course' => $courseSlug]) }}" method="POST" style="margin:0;">
                @csrf
                <button class="add-btn" type="submit">Add Chapter</button>
            </form>
        </div>
        <div class="controls-actions">
            <button class="ctrl" type="button" id="scrollLeftBtn" aria-label="Geser ke kiri">&larr;</button>
            <button class="ctrl" type="button" id="scrollRightBtn" aria-label="Geser ke kanan">&rarr;</button>
        </div>
    </div>

    <section class="road-shell">
        <div class="road" id="road">
            <div class="steps">
                @for($i = 1; $i <= $chaptersCount; $i++)
                    @php $lesson = $lessons->get($i); @endphp
                    <a class="step" href="{{ route('instructor.courses.lesson', ['course' => $courseSlug, 'chapter' => $i]) }}">
                        <div class="circle">
                            <div>
                                <small>{{ $lesson?->circle_small_text ?: 'CHAPTER' }}</small>
                                <strong>{{ $lesson?->circle_main_text ?: str_pad((string) $i, 2, '0', STR_PAD_LEFT) }}</strong>
                            </div>
                        </div>
                        <div class="meta">
                            <h3>{{ $lesson?->title ?? "Chapter {$i}" }}</h3>
                            <p>{{ $lesson?->description ? \Illuminate\Support\Str::limit($lesson->description, 60) : 'Belum ada konten.' }}</p>
                            <span class="tag">{{ $lesson && ($lesson->video_url || $lesson->video_path) ? 'Video Ready' : 'No Video Yet' }}</span>
                        </div>
                    </a>
                @endfor
            </div>
        </div>
    </section>
</div>
<script>
    const road = document.getElementById('road');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');

    scrollLeftBtn.addEventListener('click', () => {
        road.scrollBy({ left: -380, behavior: 'smooth' });
    });

    scrollRightBtn.addEventListener('click', () => {
        road.scrollBy({ left: 380, behavior: 'smooth' });
    });
</script>
</body>
</html>
