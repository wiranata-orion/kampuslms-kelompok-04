<x-layout title="Detail Mata Kuliah">
    <h1>{{ $course->name }}</h1>
    <p>Kode: {{ $course->code }}</p>
    <p>SKS: {{ $course->sks }}</p>
    <p>Deskripsi: {{ $course->description }}</p>
    <p>Dosen: {{ $course->lecturer->name ?? '-' }}</p>
    <p>Status: {{ $course->status }}</p>

    <a href="{{ route('courses.edit', $course) }}">Edit</a>

    <form action="{{ route('courses.destroy', $course) }}" method="POST" style="display:inline">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('Yakin hapus mata kuliah ini?')">Hapus</button>
    </form>

    <a href="{{ route('courses.index') }}">Kembali ke daftar</a>
</x-layout>