<x-layout title="Kumpulkan Tugas · Kampuskin">
	<section class="page-heading"><div><span class="eyebrow">{{ $assignment->course->code }} · Pengumpulan</span><h1>{{ $assignment->title }}</h1><p class="subtitle">Batas pengumpulan {{ $assignment->due_at?->format('d M Y, H:i') }}.</p></div></section>
	<section class="panel panel-tint"><h2>Instruksi tugas</h2><p style="white-space:pre-line">{{ $assignment->instructions }}</p><div class="actions"><span class="pill pill-blue">Maks. {{ $assignment->max_score }} poin</span><span class="pill {{ $assignment->allow_late ? 'pill-pink' : 'pill-muted' }}">{{ $assignment->allow_late ? 'Terlambat diizinkan' : 'Tepat waktu' }}</span></div></section>
	<section class="panel"><span class="eyebrow">Kirim pekerjaan</span><h2>Unggah hasil tugas</h2><form action="{{ route('mahasiswa.assignments.submissions.store', $assignment) }}" method="POST" enctype="multipart/form-data">@csrf
		<div class="field"><label for="file">File tugas</label><input id="file" type="file" name="file" required><p class="field-hint">Ukuran maksimum file 10 MB.</p>@error('file')<p class="field-error">{{ $message }}</p>@enderror</div>
		<div class="field"><label for="note">Catatan untuk dosen <span style="font-weight:400">(opsional)</span></label><textarea id="note" name="note">{{ old('note') }}</textarea>@error('note')<p class="field-error">{{ $message }}</p>@enderror</div>
		<div class="actions"><button class="btn btn-pink" type="submit">Kumpulkan tugas</button><a class="btn btn-quiet" href="{{ route('mahasiswa.courses.index') }}">Batal</a></div>
	</form></section>
</x-layout>
