<x-layout title="404 - Halaman Tidak Ditemukan">
    <div class="card" style="text-align:center;">
        <h1>404</h1>
        <p>
            {{ $exception->getMessage() ?: 'Halaman yang kamu cari tidak ditemukan.' }}
        </p>
        <a href="{{ route('dashboard') }}" class="btn">
            Kembali ke Dashboard
        </a>
    </div>
</x-layout>