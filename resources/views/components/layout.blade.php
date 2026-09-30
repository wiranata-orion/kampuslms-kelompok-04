<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#fbf8ff">
    <title>{{ $title ?? 'Kampuskin' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme: light; --ink: #292442; --muted: #77728e; --line: #e9e4f2; --paper: #fff; --canvas: #fbf9ff; --pink: #ef69a7; --pink-soft: #fff0f7; --violet: #8069d8; --violet-soft: #f1edff; --blue: #4d8de8; --blue-soft: #edf5ff; --green: #308c70; --red: #c84d70; --shadow: 0 12px 32px rgba(65, 48, 107, .07); }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; color: var(--ink); background: var(--canvas); background-image: radial-gradient(#d9d1ee 0.7px, transparent .7px); background-size: 20px 20px; font: 15px/1.6 'DM Sans', sans-serif; }
        a { color: var(--violet); text-decoration: none; }
        a:hover { color: #5f49b8; }
        h1, h2, h3, .brand-name { margin: 0; font-family: 'Space Grotesk', 'DM Sans', sans-serif; letter-spacing: 0; line-height: 1.2; }
        h1 { font-size: clamp(1.65rem, 4vw, 2.35rem); }
        h2 { font-size: 1.25rem; }
        p { color: var(--muted); }
        .app-shell { min-height: 100vh; }
        .topbar { position: sticky; z-index: 10; top: 0; display: flex; align-items: center; justify-content: space-between; gap: 24px; min-height: 76px; padding: 12px max(24px, calc((100vw - 1180px) / 2)); border-bottom: 1px solid rgba(226, 218, 241, .9); background: rgba(255, 255, 255, .9); backdrop-filter: blur(18px); }
        .brand { display: inline-flex; align-items: center; gap: 11px; color: var(--ink); }
        .brand-mark { display: grid; width: 40px; aspect-ratio: 1; place-items: center; border-radius: 14px; background: linear-gradient(145deg, #fba7ce, #c2b5ff 65%, #9bd2ff); box-shadow: 0 5px 14px rgba(128, 105, 216, .2); }
        .brand-mark::before { width: 13px; height: 13px; border: 3px solid #fff; border-radius: 50%; content: ''; }
        .brand-name { font-size: 1.08rem; font-weight: 700; }
        .brand-caption { display: block; color: var(--muted); font-size: .69rem; line-height: 1.1; }
        .nav-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 6px; }
        .nav-link { padding: 8px 12px; border: 1px solid transparent; border-radius: 999px; color: #68627e; font-size: .88rem; font-weight: 600; transition: .18s ease; }
        .nav-link:hover, .nav-link.active { border-color: #eee7fb; background: var(--violet-soft); color: #604bb4; }
        .logout-form { margin: 0 0 0 6px; }
        .page-wrap { width: min(1180px, calc(100% - 40px)); margin: 38px auto 72px; }
        .guest-main { display: grid; min-height: 100vh; place-items: center; padding: 28px 18px; }
        .page-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; margin-bottom: 24px; }
        .eyebrow { display: block; margin-bottom: 7px; color: var(--pink); font-size: .72rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .subtitle { max-width: 680px; margin: 8px 0 0; }
        .panel { margin-bottom: 18px; padding: 22px; border: 1px solid var(--line); border-radius: 8px; background: rgba(255,255,255,.94); box-shadow: var(--shadow); }
        .panel-tint { background: linear-gradient(120deg, #fff 4%, #fff6fb 100%); }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin: 24px 0; }
        .stat { position: relative; overflow: hidden; min-height: 126px; padding: 19px; border: 1px solid var(--line); border-radius: 8px; background: #fff; box-shadow: var(--shadow); transition: transform .2s ease, box-shadow .2s ease; }
        .stat:hover, .panel:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(65,48,107,.1); }
        .stat::after { position: absolute; right: -16px; bottom: -30px; width: 100px; height: 100px; border: 16px solid rgba(239,105,167,.09); border-radius: 50%; content: ''; }
        .stat-label { color: var(--muted); font-size: .82rem; font-weight: 600; }
        .stat-value { display: block; margin-top: 7px; font: 700 2rem/1 'Space Grotesk', sans-serif; }
        .toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 18px 0; }
        .toolbar form { display: flex; flex: 1 1 390px; flex-wrap: wrap; gap: 9px; margin: 0; }
        .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 8px; background: #fff; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 13px 15px; border-bottom: 1px solid #f0edf5; vertical-align: middle; }
        th { background: #faf8ff; color: #726b89; font-size: .74rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; white-space: nowrap; }
        tr:last-child td { border-bottom: 0; }
        tbody tr { transition: background .16s ease; }
        tbody tr:hover { background: #fff9fc; }
        .table-primary { color: var(--ink); font-weight: 700; }
        .table-meta { display: block; color: var(--muted); font-size: .82rem; font-weight: 400; }
        label { display: block; margin: 0 0 6px; color: #514b68; font-size: .84rem; font-weight: 700; }
        input, select, textarea { width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid #ded8eb; border-radius: 7px; outline: none; background: #fff; color: var(--ink); font: inherit; transition: border-color .18s, box-shadow .18s; }
        input:focus, select:focus, textarea:focus { border-color: var(--violet); box-shadow: 0 0 0 3px rgba(128,105,216,.14); }
        textarea { min-height: 130px; resize: vertical; }
        input[type=checkbox] { width: 17px; min-height: 17px; accent-color: var(--violet); }
        input[type=file] { padding: 8px; background: #fbf9ff; }
        .field { margin-bottom: 17px; }
        .field-hint { margin: 5px 0 0; color: var(--muted); font-size: .78rem; }
        .field-error { margin: 5px 0 0; color: var(--red); font-size: .82rem; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 16px; }
        .span-all { grid-column: 1 / -1; }
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: 9px; margin-top: 20px; }
        .btn { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 7px; padding: 9px 15px; border: 1px solid transparent; border-radius: 999px; background: var(--violet); color: #fff; cursor: pointer; font: 700 .86rem 'DM Sans', sans-serif; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
        .btn:hover { transform: translateY(-2px); background: #6e58c6; box-shadow: 0 7px 16px rgba(128,105,216,.24); color: #fff; }
        .btn-pink { background: var(--pink); }
        .btn-pink:hover { background: #dc528f; box-shadow: 0 7px 16px rgba(239,105,167,.24); }
        .btn-blue { background: var(--blue); }
        .btn-quiet { border-color: var(--line); background: #fff; color: #625b79; }
        .btn-quiet:hover { background: var(--violet-soft); color: #604bb4; }
        .btn-danger { background: var(--red); }
        .btn-small { min-height: 32px; padding: 6px 11px; font-size: .78rem; }
        .inline-form { display: inline-flex; margin: 0; }
        .pill { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; background: var(--violet-soft); color: #6954bd; font-size: .74rem; font-weight: 700; text-transform: capitalize; white-space: nowrap; }
        .pill-pink { background: var(--pink-soft); color: #c74d87; }
        .pill-blue { background: var(--blue-soft); color: #3977c6; }
        .pill-green { background: #e8f7f1; color: #2c8064; }
        .pill-muted { background: #f1eff5; color: #77728e; }
        .alert { margin: 0 0 16px; padding: 12px 15px; border: 1px solid #c9e9da; border-radius: 8px; background: #edfaf3; color: #27684f; }
        .alert-error { border-color: #f2ccda; background: #fff1f5; color: #a63d60; }
        .empty-state { padding: 36px 18px; color: var(--muted); text-align: center; }
        .empty-mark { display: grid; width: 48px; height: 48px; place-items: center; margin: 0 auto 12px; border-radius: 17px; background: var(--pink-soft); color: var(--pink); font: 700 1.2rem 'Space Grotesk', sans-serif; }
        .detail-list { display: grid; grid-template-columns: minmax(130px, .35fr) 1fr; gap: 10px 22px; margin: 18px 0 0; }
        .detail-list dt { color: var(--muted); font-size: .85rem; }
        .detail-list dd { margin: 0; font-weight: 600; overflow-wrap: anywhere; }
        .notice-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; padding: 16px 0; border-bottom: 1px solid var(--line); }
        .notice-row:last-child { border-bottom: 0; }
        .notice-row.unread { border-left: 3px solid var(--pink); padding-left: 14px; }
        .login-card { display: grid; grid-template-columns: 1fr 1fr; width: min(850px, 100%); overflow: hidden; border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: 0 22px 60px rgba(65,48,107,.13); }
        .login-art { display: flex; min-height: 500px; flex-direction: column; justify-content: space-between; padding: 36px; background: linear-gradient(145deg, #fff1f8, #f0edff 58%, #eaf5ff); }
        .login-art h1 { max-width: 340px; font-size: 2.6rem; }
        .orbit-mark { display: grid; width: 148px; aspect-ratio: 1; place-items: center; align-self: center; border: 1px solid rgba(128,105,216,.25); border-radius: 50%; background: rgba(255,255,255,.55); box-shadow: 0 0 0 16px rgba(255,255,255,.35), 0 0 0 34px rgba(255,255,255,.22); }
        .orbit-mark span { display: block; width: 62px; height: 62px; border: 12px solid white; border-radius: 20px; background: linear-gradient(145deg, var(--pink), var(--violet), var(--blue)); transform: rotate(-12deg); }
        .login-form-side { display: flex; flex-direction: column; justify-content: center; padding: 42px; }
        .login-form-side h2 { margin-bottom: 8px; font-size: 1.7rem; }
        .login-form-side .actions .btn { width: 100%; }
        .pagination-row { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 18px; color: var(--muted); font-size: .84rem; }
        .pagination-row .total { margin-left: auto; }
        @media (max-width: 760px) { .topbar { position: static; align-items: flex-start; flex-direction: column; gap: 12px; padding: 14px 20px; } .nav-row { justify-content: flex-start; } .page-wrap { width: min(100% - 28px, 1180px); margin-top: 26px; } .page-heading { align-items: flex-start; flex-direction: column; } .login-card { grid-template-columns: 1fr; } .login-art { min-height: 260px; padding: 24px; } .login-art h1 { font-size: 2rem; } .orbit-mark { display: none; } .login-form-side { padding: 28px 24px; } }
        @media (max-width: 520px) { .form-grid { grid-template-columns: 1fr; } .span-all { grid-column: auto; } .panel { padding: 17px; } .detail-list { grid-template-columns: 1fr; gap: 3px; } .detail-list dd { margin-bottom: 10px; } .toolbar form { flex-basis: 100%; } .toolbar form .field-grow { flex: 1 1 100%; } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body>
    @auth
        <div class="app-shell">
            <header class="topbar">
                <a class="brand" href="{{ route('dashboard') }}">
                    <span class="brand-mark" aria-hidden="true"></span>
                    <span><span class="brand-name">Kampuskin</span><span class="brand-caption">ruang belajar digital</span></span>
                </a>
                <nav class="nav-row" aria-label="Navigasi utama">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Beranda</a>
                    @if (auth()->user()->role === 'admin')
                        <a class="nav-link {{ request()->routeIs('courses.*', 'admin.courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">Mata kuliah</a>
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Pengguna</a>
                    @elseif (auth()->user()->role === 'dosen')
                        <a class="nav-link {{ request()->routeIs('dosen.courses.*') ? 'active' : '' }}" href="{{ route('dosen.courses.index') }}">Kelas saya</a>
                    @else
                        <a class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">Katalog</a>
                        <a class="nav-link {{ request()->routeIs('mahasiswa.courses.*') ? 'active' : '' }}" href="{{ route('mahasiswa.courses.index') }}">Kelas saya</a>
                    @endif
                    @if (Route::has('notifications.index')) <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">Notifikasi</a> @endif
                    <form class="logout-form" action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-quiet btn-small" type="submit">Keluar</button></form>
                </nav>
            </header>
            <main class="page-wrap">
                @if (session('success')) <div class="alert" role="status">{{ session('success') }}</div> @endif
                @if (session('error')) <div class="alert alert-error" role="alert">{{ session('error') }}</div> @endif
                {{ $slot }}
            </main>
        </div>
    @else
        <main class="guest-main">
            @if (session('success')) <div class="alert">{{ session('success') }}</div> @endif
            {{ $slot }}
        </main>
    @endauth
</body>
</html>