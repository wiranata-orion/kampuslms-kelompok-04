<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'LMS Kampus' }}</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; margin: 0; background: #f8fafc; color: #1e293b; line-height: 1.5; }
        header { background: #0f172a; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        header a { color: white; text-decoration: none; font-weight: 600; margin-left: 1.5rem; }
        header a.active { text-decoration: underline; }
        main { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .badge { display: inline-block; background: #e0e7ff; color: #3730a3; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; margin-right: 0.35rem; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 500; border: none; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .btn-success { background: #16a34a; }
        .btn-success:hover { background: #15803d; }
        .btn-warning { background: #d97706; }
        .btn-warning:hover { background: #b45309; }
        .btn-danger { background: #dc2626; }
        .btn-danger:hover { background: #b91c1c; }
        .action-group { display: flex; gap: 0.5rem; align-items: center; margin-top: 1rem; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #2563eb; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <header>
        <div><strong>LMS Kampus</strong></div>
        <nav>
            <a href="{{ route('dashboard') }}"
               class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('courses.index') }}"
               class="{{ request()->routeIs('courses.*') ? 'active' : '' }}">
                Mata Kuliah
            </a>

            {{-- Tombol tambah, hanya tampil jika route courses.create sudah didaftarkan --}}
            @if (Route::has('courses.create'))
                <a href="{{ route('courses.create') }}">+ Tambah Mata Kuliah</a>
            @endif
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>
</body>
</html>