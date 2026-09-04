<x-layout>
    <x-slot:title>
        {{ $title }} - {{ $course['nama'] }}
    </x-slot:title>

    <a href="{{ route('courses.index') }}" class="back-link">&larr; Kembali ke Daftar Mata Kuliah</a>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <h2 style="margin-top: 0; margin-bottom: 0.5rem;">{{ $course['nama'] }}</h2>
            <a href="{{ route('courses.edit', ['id' => $course['id']]) }}" class="btn btn-warning">
                Edit Data
            </a>
        </div>

        <p style="margin-bottom: 1.5rem;">
            <span class="badge">{{ $course['kode'] }}</span>
            <span class="badge">{{ $course['sks'] }} SKS</span>
            <span class="badge">Semester {{ $course['semester'] }}</span>
        </p>

        <p><strong>Dosen Pengampu:</strong> {{ $course['dosen'] }}</p>

        <h4 style="margin-bottom: 0.5rem; margin-top: 1.5rem;">Deskripsi Mata Kuliah</h4>
        <p style="color: #334155; margin-top: 0;">{{ $course['deskripsi'] }}</p>
    </div>
</x-layout> 