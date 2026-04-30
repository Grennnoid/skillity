<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frontend Craft Chapter {{ $chapter }} | Skillify</title>
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

        .links {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .link {
            color: #d7e8ff;
            text-decoration: none;
            font-size: 14px;
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

        .muted {
            color: var(--muted);
            font-size: 13px;
            margin: 0;
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
            min-height: 420px;
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
            padding: 16px;
            text-align: center;
        }

        .desc {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
            color: #d8e6ff;
            line-height: 1.7;
            font-size: 14px;
        }

        .nav-chapters {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 12px;
            color: #d7e8ff;
            text-decoration: none;
            font-size: 13px;
            background: rgba(12, 19, 35, 0.72);
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
        <strong>Frontend Craft - Chapter {{ $chapter }}</strong>
        <div class="links">
            <a class="link" href="{{ route('courses.frontend-craft.roadmap') }}">Back To Roadmap</a>
            <a class="link" href="{{ route('student.dashboard') }}">Dashboard</a>
        </div>
    </div>

    <section class="card">
        <h1>{{ $lesson->title ?? "Chapter {$chapter}" }}</h1>
        <p class="muted">Materi chapter ini berasal dari Chapter Builder yang diatur oleh Admin/Dosen.</p>

        <div class="video-box">
            @if($lesson && $lesson->video_path)
                <video controls>
                    <source src="{{ asset('storage/' . $lesson->video_path) }}" type="video/mp4">
                </video>
            @elseif($lesson && $lesson->video_url)
                <iframe src="{{ $embedVideoUrl }}" allowfullscreen loading="lazy"></iframe>
            @else
                <div class="placeholder">Video chapter belum diunggah. Silakan lanjut ke chapter lain atau tunggu update mentor.</div>
            @endif
        </div>

        <div class="desc">
            {!! nl2br(e($lesson->description ?? 'Deskripsi chapter belum tersedia.')) !!}
        </div>

        <div class="nav-chapters">
            @if($hasPrevious)
                <a class="chip" href="{{ route('courses.frontend-craft.chapter', ['chapter' => $chapter - 1]) }}">&larr; Chapter {{ $chapter - 1 }}</a>
            @else
                <span></span>
            @endif

            <a class="chip" href="{{ route('courses.frontend-craft.roadmap') }}">All Chapters</a>

            @if($hasNext)
                <a class="chip" href="{{ route('courses.frontend-craft.chapter', ['chapter' => $chapter + 1]) }}">Chapter {{ $chapter + 1 }} &rarr;</a>
            @endif
        </div>
    </section>
</div>
</body>
</html>
