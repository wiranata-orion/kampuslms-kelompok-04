<x-layout title="Tambah Peserta · Kampuskin">
	<section class="page-heading"><div><span class="eyebrow">{{ $course->code }} · {{ $course->name }}</span><h1>Daftarkan mahasiswa</h1><p class="subtitle">Pilih mahasiswa untuk ditambahkan ke kelas ini.</p></div></section>
	<section class="panel"><form action="{{ route('admin.courses.enrollments.store', $course) }}" method="POST">@csrf
		<div class="field"><label for="user_id">Mahasiswa</label><select id="user_id" name="user_id" required><option value="">Pilih mahasiswa</option>@foreach ($candidates as $candidate)<option value="{{ $candidate->id }}" @selected(old('user_id') == $candidate->id)>{{ $candidate->name }} · {{ $candidate->nim_nip ?: $candidate->email }}</option>@endforeach</select>@error('user_id')<p class="field-error">{{ $message }}</p>@enderror</div>
		@if ($candidates->isEmpty()) <div class="alert">Semua mahasiswa sudah terdaftar di kelas ini.</div> @endif
		<div class="actions"><button class="btn btn-pink" type="submit" @disabled($candidates->isEmpty())>Daftarkan mahasiswa</button><a class="btn btn-quiet" href="{{ route('admin.courses.enrollments.index', $course) }}">Batal</a></div>
	</form></section>
</x-layout>
