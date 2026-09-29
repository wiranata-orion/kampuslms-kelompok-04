<x-layout title="Edit Pengumpulan · Kampuskin">
	<section class="page-heading"><div><span class="eyebrow">{{ $submission->assignment->course->code }} · Pengumpulan</span><h1>Perbarui tugas</h1><p class="subtitle">{{ $submission->assignment->title }} · batas {{ $submission->assignment->due_at?->format('d M Y, H:i') }}</p></div></section>
	<section class="panel"><div class="alert">File saat ini: <strong>{{ $submission->original_name }}</strong> · dikumpulkan {{ $submission->submitted_at?->format('d M Y, H:i') }}</div><form action="{{ route('mahasiswa.submissions.update', $submission) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
		<div class="field"><label for="file">Ganti file <span style="font-weight:400">(opsional)</span></label><input id="file" type="file" name="file"><p class="field-hint">Biarkan kosong untuk tetap menggunakan file saat ini. Maksimum 10 MB.</p>@error('file')<p class="field-error">{{ $message }}</p>@enderror</div>
		<div class="field"><label for="note">Catatan untuk dosen</label><textarea id="note" name="note">{{ old('note', $submission->note) }}</textarea>@error('note')<p class="field-error">{{ $message }}</p>@enderror</div>
		<div class="actions"><button class="btn btn-pink" type="submit">Simpan pengumpulan</button><a class="btn btn-quiet" href="{{ route('mahasiswa.submissions.show', $submission) }}">Batal</a></div>
	</form></section>
</x-layout>
