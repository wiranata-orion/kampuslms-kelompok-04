<x-layout title="Edit Mata Kuliah">
    <h1>Edit Mata Kuliah</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('courses.update', $course) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Kode Mata Kuliah</label>
        <input type="text" name="code" value="{{ old('code', $course->code) }}">

        <label>Nama Mata Kuliah</label>
        <input type="text" name="name" value="{{ old('name', $course->name) }}">

        <label>Deskripsi</label>
        <textarea name="description">{{ old('description', $course->description) }}</textarea>

        <label>SKS</label>
        <input type="number" name="sks" value="{{ old('sks', $course->sks) }}">

        <label>Dosen Pengampu</label>
        <select name="lecturer_id">
            <option value="">-- Pilih Dosen --</option>
            @foreach ($lecturers as $lecturer)
                <option value="{{ $lecturer->id }}" {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}>
                    {{ $lecturer->name }}
                </option>
            @endforeach
        </select>

        <label>Status</label>
        <select name="status">
            <option value="draft" {{ old('status', $course->status) == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="active" {{ old('status', $course->status) == 'active' ? 'selected' : '' }}>Active</option>
            <option value="archived" {{ old('status', $course->status) == 'archived' ? 'selected' : '' }}>Archived</option>
        </select>

        <button type="submit">Perbarui</button>
    </form>

    <a href="{{ route('courses.index') }}">Kembali ke daftar</a>
</x-layout>