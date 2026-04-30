<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.student.mentors_title') }}</title>
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
            width: min(1180px, 94vw);
            margin: 0 auto;
            padding: 24px 0 36px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .brand {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .back {
            color: #d4e3ff;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 8px 12px;
            background: rgba(8, 14, 28, 0.75);
        }

        .back:hover { color: #fff; border-color: rgba(121, 240, 212, 0.58); }

        .top-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .hero {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
            background: var(--panel);
            margin-bottom: 16px;
        }

        .hero h1 {
            margin: 0 0 6px;
            font-size: clamp(26px, 4.5vw, 38px);
            font-weight: 600;
        }

        .hero p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .search {
            margin-top: 14px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search input {
            flex: 1 1 280px;
            min-width: 220px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(8, 14, 28, 0.92);
            color: var(--text);
            padding: 11px 12px;
            font-size: 14px;
        }

        .search button {
            border: 0;
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 14px;
            font-weight: 600;
            color: #041220;
            background: linear-gradient(120deg, #67d7ff, #79f0d4);
            cursor: pointer;
        }

        .grid {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 14px;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(10, 18, 34, 0.86);
            box-shadow: var(--shadow);
            padding: 14px;
            display: grid;
            grid-template-columns: 82px 1fr;
            gap: 12px;
            align-items: start;
        }

        .avatar {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            border: 2px solid rgba(121, 240, 212, 0.4);
            background: #0f172b;
            color: #f3f7ff;
            font-weight: 700;
            display: grid;
            place-items: center;
            overflow: hidden;
            font-size: 20px;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .meta h3 {
            margin: 0 0 4px;
            font-size: 20px;
        }

        .meta .count {
            color: #bdefff;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .meta p {
            margin: 0 0 10px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
            min-height: 60px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 12px;
            color: #041220;
            background: linear-gradient(120deg, #67d7ff, #79f0d4);
        }

        .empty {
            margin-top: 16px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--panel);
            padding: 14px;
            color: var(--muted);
        }

        @media (max-width: 760px) {
            .container {
                width: min(100%, calc(100vw - 28px));
                padding: 18px 0 24px;
            }

            .topbar {
                flex-direction: column;
                align-items: stretch;
                margin-bottom: 16px;
            }

            .brand {
                font-size: 20px;
            }

            .back {
                width: 100%;
                text-align: center;
            }

            .top-actions {
                justify-content: stretch;
            }

            .hero {
                padding: 16px;
                border-radius: 16px;
            }

            .search {
                flex-direction: column;
            }

            .search input {
                min-width: 0;
            }

            .search button {
                width: 100%;
            }

            .grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .card {
                grid-template-columns: 1fr;
                justify-items: start;
            }

            .meta p {
                min-height: 0;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .container {
                width: min(100%, calc(100vw - 20px));
                padding-top: 14px;
            }

            .hero h1 {
                font-size: 24px;
            }

            .avatar {
                width: 74px;
                height: 74px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <div class="brand">{{ __('ui.student.mentors_brand') }}</div>
        <div class="top-actions">
            <a class="back" href="{{ route('student.pathfinder') }}">{{ __('ui.pathfinder.nav_label') }}</a>
            <a class="back" href="{{ route('student.dashboard') }}">{{ __('ui.student.back_to_dashboard') }}</a>
        </div>
    </div>

    <section class="hero">
        <h1>{{ __('ui.student.discover_mentors') }}</h1>
        <p>{{ __('ui.student.discover_mentors_text') }}</p>
        <form class="search" method="GET" action="{{ route('student.mentors') }}">
            <input id="searchInput" type="text" name="q" value="{{ $search }}" placeholder="{{ __('ui.student.search_mentors') }}">
            <button type="submit">{{ __('ui.student.search') }}</button>
        </form>
    </section>

    @if(($mentors ?? collect())->isEmpty())
        <div class="empty">{{ __('ui.student.no_matching_mentors') }}</div>
    @else
        <section class="grid" id="mentorsGrid">
            @foreach($mentors as $mentor)
                @php
                    $nameParts = preg_split('/\s+/', trim($mentor->name));
                    $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1).substr($nameParts[1] ?? '', 0, 1));
                    $initials = $initials !== '' ? $initials : 'M';
                    $bio = \Illuminate\Support\Str::limit(trim((string) ($mentor->bio ?? __('ui.student.mentor_fallback_bio'))), 130);
                @endphp
                <article class="card" data-search="{{ strtolower($mentor->name.' '.$bio) }}">
                    <div class="avatar">
                        @if(!empty($mentor->profile_image))
                            <img src="{{ asset('storage/'.$mentor->profile_image) }}" alt="{{ $mentor->name }}">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <div class="meta">
                        <h3>{{ $mentor->name }}</h3>
                        <div class="count">{{ __('ui.student.courses_created', ['count' => (int) $mentor->courses_count]) }}</div>
                        <p>{{ $bio }}</p>
                        <a class="btn" href="{{ route('mentors.show', ['mentor' => $mentor->id]) }}">{{ __('ui.student.view_mentor_profile') }}</a>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</div>

<script>
    const input = document.getElementById('searchInput');
    const cards = Array.from(document.querySelectorAll('#mentorsGrid .card'));

    function normalized(text) {
        return (text || '').toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
    }

    function looksClose(haystack, needle) {
        if (!needle) return true;
        if (haystack.includes(needle)) return true;
        const tokens = haystack.split(' ');
        if (tokens.some(token => token.startsWith(needle.slice(0, Math.min(3, needle.length))))) return true;
        let i = 0;
        for (const ch of haystack) {
            if (i < needle.length && ch === needle[i]) i++;
        }
        return needle.length > 0 && (i / needle.length) >= 0.75;
    }

    if (input && cards.length) {
        input.addEventListener('input', () => {
            const q = normalized(input.value);
            cards.forEach((card) => {
                const searchable = normalized(card.dataset.search);
                card.style.display = looksClose(searchable, q) ? '' : 'none';
            });
        });
    }
</script>
@include('partials.student-chatbot')
</body>
</html>
