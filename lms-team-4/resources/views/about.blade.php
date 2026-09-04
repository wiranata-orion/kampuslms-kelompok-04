<x-layout title="Dashboard - LMS Kampus">
    <div class="card">
        <h1>Selamat Datang di LMS Kampus</h1>
        <p>
            Gunakan menu navigasi di atas untuk mengakses daftar mata kuliah
            beserta materi, tugas, pengumpulan, dan nilai.
        </p>
    </div>

    <div class="card">
        <h2>Mulai dari sini</h2>
        <div class="action-group">
            <a href="{{ route('courses.index') }}" class="btn">
                Lihat Daftar Mata Kuliah
            </a>
        </div>
    </div>
</x-layout>