<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $courseTitle }} Chapter {{ $chapter }} | Instructor Lesson</title>
    <style>
        :root {
            --bg: #050914;
            --line: rgba(154, 178, 225, 0.24);
            --text: #e9f1ff;
            --muted: #9db1d6;
            --accent: #45d0ff;
            --accent2: #7cf6d6;
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
        .wrap {
            width: min(1100px, 94vw);
            margin: 0 auto;
            padding: 18px 0 24px;
        }
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--line);
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .link {
            color: #d7e8ff;
            text-decoration: none;
            font-size: 14px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 14px;
        }
        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(11, 19, 35, 0.82);
            padding: 14px;
        }
        h1 {
            margin: 0 0 4px;
            font-size: 30px;
        }
        .muted { color: var(--muted); font-size: 13px; }
        .video-box {
            margin-top: 10px;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            background: rgba(7, 13, 26, 0.9);
        }
        video, iframe {
            width: 100%;
            min-height: 360px;
            border: 0;
            display: block;
            background: #04070f;
        }
        .placeholder {
            min-height: 360px;
            display: grid;
            place-items: center;
            color: var(--muted);
            font-size: 14px;
        }
        .desc {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
            color: #d8e6ff;
            line-height: 1.7;
            font-size: 14px;
        }
        .fields { display: grid; gap: 9px; }
        input, textarea, select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px;
            color: var(--text);
            background: rgba(8, 14, 27, 0.82);
            font-size: 13px;
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
        textarea { min-height: 130px; resize: vertical; }
        .btn {
            border: 0;
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            color: #041220;
            background: linear-gradient(120deg, var(--accent), var(--accent2));
        }
        .nav-chapters {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
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
        @media (max-width: 960px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="wrap">
    @php
        $embedVideoUrl = null;
        if (!empty($lesson?->video_url)) {
            $rawUrl = trim($lesson->video_url);
            $embedVideoUrl = $rawUrl;

            if (str_contains($rawUrl, 'youtube.com/watch?v=')) {
                $parts = parse_url($rawUrl);
                parse_str($parts['query'] ?? '', $query);
                if (!empty($query['v'])) {
                    $embedVideoUrl = 'https://www.youtube.com/embed/'.$query['v'];
                }
            } elseif (str_contains($rawUrl, 'youtu.be/')) {
                $path = trim((string) parse_url($rawUrl, PHP_URL_PATH), '/');
                $videoId = explode('/', $path)[0] ?? '';
                if ($videoId !== '') {
                    $embedVideoUrl = 'https://www.youtube.com/embed/'.$videoId;
                }
            } elseif (str_contains($rawUrl, 'youtube.com/shorts/')) {
                $path = trim((string) parse_url($rawUrl, PHP_URL_PATH), '/');
                $videoId = explode('/', str_replace('shorts/', '', $path))[0] ?? '';
                if ($videoId !== '') {
                    $embedVideoUrl = 'https://www.youtube.com/embed/'.$videoId;
                }
            }
        }
    @endphp
    <div class="top">
        <strong>{{ $courseTitle }} - Chapter {{ $chapter }}</strong>
        <a class="link" href="{{ route('instructor.courses.roadmap', ['course' => $courseSlug]) }}">Back To Chapter Circles</a>
    </div>

    @if(session('success'))
        <div style="margin-bottom:10px;border:1px solid rgba(73,214,139,.4);border-radius:10px;background:rgba(12,42,24,.5);color:#b4f2cc;padding:10px 12px;font-size:13px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div style="margin-bottom:10px;border:1px solid rgba(255,107,125,.45);border-radius:10px;background:rgba(72,21,33,.45);color:#ffc4cc;padding:10px 12px;font-size:13px;">{{ $errors->first() }}</div>
    @endif

    <div class="grid">
        <section class="card">
            <h1>{{ $lesson->title ?? "Chapter {$chapter}" }}</h1>
            <p class="muted">Halaman belajar actual untuk user. Admin/Dosen bisa update video dan deskripsi dari panel sebelah.</p>

            <div class="video-box">
                @if($lesson && $lesson->video_path)
                    <video controls>
                        <source src="{{ asset('storage/' . $lesson->video_path) }}" type="video/mp4">
                    </video>
                @elseif($lesson && $lesson->video_url)
                    <iframe src="{{ $embedVideoUrl }}" allowfullscreen loading="lazy"></iframe>
                @else
                    <div class="placeholder">Belum ada video. Tambahkan URL/file dari form di kanan.</div>
                @endif
            </div>

            <div class="desc">
                {!! nl2br(e($lesson->description ?? 'Deskripsi belum diisi. Tambahkan ringkasan materi, tujuan belajar, dan instruksi praktikum di form sebelah.')) !!}
            </div>

            <div class="nav-chapters">
                @if($chapter > 1)
                    <a class="chip" href="{{ route('instructor.courses.lesson', ['course' => $courseSlug, 'chapter' => $chapter - 1]) }}">← Chapter {{ $chapter - 1 }}</a>
                @else
                    <span></span>
                @endif
                <a class="chip" href="{{ route('instructor.courses.roadmap', ['course' => $courseSlug]) }}">All Chapters</a>
                @if($chapter < $chaptersCount)
                    <a class="chip" href="{{ route('instructor.courses.lesson', ['course' => $courseSlug, 'chapter' => $chapter + 1]) }}">Chapter {{ $chapter + 1 }} →</a>
                @endif
            </div>
        </section>

        <aside class="card">
            <h2 style="margin:0 0 10px;">Edit Chapter Content</h2>
            <form class="fields" action="{{ route('instructor.courses.lesson.save', ['course' => $courseSlug, 'chapter' => $chapter]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="text" name="title" placeholder="Judul chapter" value="{{ old('title', $lesson->title ?? "Chapter {$chapter}") }}" required>
                <textarea name="description" placeholder="Deskripsi materi, objective, atau catatan belajar...">{{ old('description', $lesson->description ?? '') }}</textarea>
                <select name="video_source" id="videoSource" required>
                    <option value="none">No Video</option>
                    <option value="url" @selected(old('video_source', $lesson && $lesson->video_url ? 'url' : '') === 'url')>Video URL</option>
                    <option value="file" @selected(old('video_source', $lesson && $lesson->video_path ? 'file' : '') === 'file')>Upload File</option>
                </select>
                <input type="url" name="video_url" id="videoUrlField" placeholder="https://youtube.com/embed/... atau link video" value="{{ old('video_url', $lesson->video_url ?? '') }}">
                <input type="file" name="video_file" id="videoFileField" accept="video/mp4,video/webm,video/quicktime,video/x-msvideo,video/x-matroska">
                <hr style="border:0;border-top:1px solid rgba(154,178,225,.24);margin:2px 0;">
                <input type="text" name="circle_small_text" placeholder="Circle small text (contoh: CHAPTER)" value="{{ old('circle_small_text', $lesson->circle_small_text ?? '') }}">
                <input type="text" name="circle_main_text" placeholder="Circle main text (contoh: 01 / START)" value="{{ old('circle_main_text', $lesson->circle_main_text ?? '') }}">
                <button class="btn" type="submit">Save Chapter</button>
            </form>
            <p class="muted" style="margin-top:8px;">Video maks: 500MB (mp4, webm, mov, avi, mkv). Circle text bisa disesuaikan.</p>
        </aside>
    </div>
</div>

<script>
    const sourceField = document.getElementById('videoSource');
    const urlField = document.getElementById('videoUrlField');
    const fileField = document.getElementById('videoFileField');

    function syncVideoFields() {
        const mode = sourceField.value;
        urlField.style.display = mode === 'url' ? 'block' : 'none';
        fileField.style.display = mode === 'file' ? 'block' : 'none';
    }

    sourceField.addEventListener('change', syncVideoFields);
    syncVideoFields();
</script>
</body>
</html>
