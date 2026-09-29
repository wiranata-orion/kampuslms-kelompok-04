<x-layout title="Edit Nilai · Kampuskin">
	<section class="page-heading"><div><span class="eyebrow">Penilaian tugas</span><h1>Perbarui nilai</h1><p class="subtitle">{{ $grade->submission->student->name ?? 'Mahasiswa' }} · {{ $grade->submission->assignment->title }}</p></div></section>
	<section class="panel"><form action="{{ route('dosen.grade.update', $grade) }}" method="POST">@csrf @method('PUT')
		<div class="field"><label for="score">Nilai (maks. {{ $grade->submission->assignment->max_score }})</label><input id="score" type="number" name="score" min="0" max="{{ $grade->submission->assignment->max_score }}" step="0.01" value="{{ old('score', $grade->score) }}" required>@error('score')<p class="field-error">{{ $message }}</p>@enderror</div>
		<div class="field"><label for="feedback">Umpan balik</label><textarea id="feedback" name="feedback">{{ old('feedback', $grade->feedback) }}</textarea>@error('feedback')<p class="field-error">{{ $message }}</p>@enderror</div>
		<div class="actions"><button class="btn btn-pink" type="submit">Simpan perubahan</button><a class="btn btn-quiet" href="{{ route('dosen.submissions.show', $grade->submission) }}">Batal</a></div>
	</form></section>
</x-layout>
