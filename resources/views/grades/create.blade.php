<x-layout title="Beri Nilai · Kampuskin">
	<section class="page-heading"><div><span class="eyebrow">Penilaian tugas</span><h1>Beri nilai</h1><p class="subtitle">{{ $submission->student->name ?? 'Mahasiswa' }} · {{ $submission->assignment->title }}</p></div></section>
	<section class="panel panel-tint"><a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($submission->file_path) }}" target="_blank" rel="noopener">Buka {{ $submission->original_name }}</a><p>Nilai maksimum: <strong>{{ $submission->assignment->max_score }}</strong></p><p class="field-hint">Catatan mahasiswa: {{ $submission->note ?: 'Tidak ada.' }}</p></section>
	<section class="panel"><form action="{{ route('dosen.submissions.grade.store', $submission) }}" method="POST">@csrf
		<div class="field"><label for="score">Nilai (maks. {{ $submission->assignment->max_score }})</label><input id="score" type="number" name="score" min="0" max="{{ $submission->assignment->max_score }}" step="0.01" value="{{ old('score') }}" required>@error('score')<p class="field-error">{{ $message }}</p>@enderror</div>
		<div class="field"><label for="feedback">Umpan balik <span style="font-weight:400">(opsional)</span></label><textarea id="feedback" name="feedback">{{ old('feedback') }}</textarea>@error('feedback')<p class="field-error">{{ $message }}</p>@enderror</div>
		<div class="actions"><button class="btn btn-pink" type="submit">Simpan nilai</button><a class="btn btn-quiet" href="{{ route('dosen.submissions.show', $submission) }}">Batal</a></div>
	</form></section>
</x-layout>
