<x-layout title="Beranda · Kampuskin">
    <section class="page-heading"><div><span class="eyebrow">Ruang belajarmu</span><h1>Halo, {{ auth()->user()->name }}!</h1><p class="subtitle">Siap lanjut tumbuh hari ini? Ini ringkasan aktivitas kampusmu.</p></div><span class="pill pill-pink">{{ ucfirst(auth()->user()->role) }}</span></section>
    <section class="stat-grid" aria-label="Ringkasan aktivitas">
        @if (auth()->user()->role === 'admin')
            <article class="stat"><span class="stat-label">Total pengguna</span><strong class="stat-value">{{ $stats['total_users'] ?? 0 }}</strong></article><article class="stat"><span class="stat-label">Mata kuliah</span><strong class="stat-value">{{ $stats['total_courses'] ?? 0 }}</strong></article>
        @elseif (auth()->user()->role === 'dosen')
            <article class="stat"><span class="stat-label">Kelas diampu</span><strong class="stat-value">{{ $stats['total_courses'] ?? 0 }}</strong></article><article class="stat"><span class="stat-label">Tugas aktif &amp; draft</span><strong class="stat-value">{{ $stats['total_assignments'] ?? 0 }}</strong></article>
        @else
            <article class="stat"><span class="stat-label">Kelas diikuti</span><strong class="stat-value">{{ $stats['total_courses'] ?? 0 }}</strong></article><article class="stat"><span class="stat-label">Tugas terkumpul</span><strong class="stat-value">{{ $stats['total_submissions'] ?? 0 }}</strong></article>
        @endif
    </section>
    <section class="panel panel-tint"><span class="eyebrow">Mulai dari sini</span><h2>Temukan langkah berikutnya</h2><p>Lihat kelas, materi, dan tugas yang menunggumu di Kampuskin.</p><div class="actions">
        @if (auth()->user()->role === 'admin') <a class="btn" href="{{ route('courses.index') }}">Jelajahi mata kuliah</a><a class="btn btn-quiet" href="{{ route('admin.users.index') }}">Kelola pengguna</a>
        @elseif (auth()->user()->role === 'dosen') <a class="btn" href="{{ route('dosen.courses.index') }}">Buka kelas saya</a>
        @else <a class="btn" href="{{ route('mahasiswa.courses.index') }}">Buka kelas saya</a><a class="btn btn-quiet" href="{{ route('courses.index') }}">Jelajahi katalog</a> @endif
    </div></section>
</x-layout>