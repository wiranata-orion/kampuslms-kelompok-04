<x-layout title="Tambah Mata Kuliah · Kampuskin">
    <section class="page-heading"><div><span class="eyebrow">Katalog kampus</span><h1>Buat mata kuliah</h1><p class="subtitle">Lengkapi identitas kelas dan tentukan dosen pengampunya.</p></div></section>
    <section class="panel"><form action="{{ route('admin.courses.store') }}" method="POST">@csrf
        <div class="form-grid">
            <div class="field"><label for="code">Kode mata kuliah</label><input id="code" name="code" value="{{ old('code') }}" required>@error('code')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="name">Nama mata kuliah</label><input id="name" name="name" value="{{ old('name') }}" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field span-all"><label for="description">Deskripsi</label><textarea id="description" name="description">{{ old('description') }}</textarea>@error('description')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="sks">Jumlah SKS</label><input id="sks" type="number" name="sks" min="1" max="20" value="{{ old('sks') }}" required>@error('sks')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="lecturer_id">Dosen pengampu</label><select id="lecturer_id" name="lecturer_id" required><option value="">Pilih dosen</option>@foreach ($lecturers as $lecturer)<option value="{{ $lecturer->id }}" @selected(old('lecturer_id') == $lecturer->id)>{{ $lecturer->name }}</option>@endforeach</select>@error('lecturer_id')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="status">Status kelas</label><select id="status" name="status" required><option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option><option value="active" @selected(old('status') === 'active')>Aktif</option><option value="archived" @selected(old('status') === 'archived')>Arsip</option></select>@error('status')<p class="field-error">{{ $message }}</p>@enderror</div>
        </div><div class="actions"><button class="btn btn-pink" type="submit">Simpan mata kuliah</button><a class="btn btn-quiet" href="{{ route('courses.index') }}">Batal</a></div>
    </form></section>
</x-layout>