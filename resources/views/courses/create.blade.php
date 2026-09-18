<x-layout title="Tambah Mata Kuliah">
    <h1>Tambah Mata Kuliah</h1>

    <form action="{{ route('courses.store') }}" method="POST">
        @csrf

        <label>Kode Mata Kuliah</label>
        <input type="text" name="code" value="{{ old('code') }}">
        @error('code')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <label>Nama Mata Kuliah</label>
        <input type="text" name="name" value="{{ old('name') }}">
        @error('name')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <label>Deskripsi</label>
        <textarea name="description">{{ old('description') }}</textarea>
        @error('description')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <label>SKS</label>
        <input type="number" name="sks" value="{{ old('sks') }}">
        @error('sks')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <label>Dosen Pengampu</label>
        <select name="lecturer_id">
            <option value="">-- Pilih Dosen --</option>
            @foreach ($lecturers as $lecturer)
                <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                    {{ $lecturer->name }}
                </option>
            @endforeach
        </select>
        @error('lecturer_id')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <label>Status</label>
        <select name="status">
            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
        </select>
        @error('status')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <button type="submit">Simpan</button>
    </form>

    <a href="{{ route('courses.index') }}">Kembali ke daftar</a>
</x-layout>