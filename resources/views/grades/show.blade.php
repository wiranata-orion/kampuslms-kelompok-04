<x-layout title="Nilai Saya · Kampuskin">
	<section class="page-heading"><div><span class="eyebrow">Hasil tugas</span><h1>{{ $submission->assignment->title }}</h1><p class="subtitle">{{ $submission->assignment->course->name }}</p></div><a class="btn btn-quiet" href="{{ route('mahasiswa.submissions.show', $submission) }}">Kembali ke pengumpulan</a></section>
	@if ($submission->grade)
		<section class="panel panel-tint"><span class="eyebrow">Nilai akhir</span><div class="stat-value">{{ $submission->grade->score }} <span style="color:var(--muted);font-size:1rem;font-weight:500">/ {{ $submission->assignment->max_score }}</span></div><p>Dinilai {{ $submission->grade->graded_at?->format('d M Y, H:i') }} oleh {{ $submission->grade->grader->name ?? 'Dosen' }}.</p><hr style="border:0;border-top:1px solid var(--line);margin:22px 0"><h2>Umpan balik</h2><p style="white-space:pre-line">{{ $submission->grade->feedback ?: 'Belum ada umpan balik.' }}</p></section>
	@else
		<section class="panel"><div class="empty-state"><span class="empty-mark">...</span><h2>Nilai belum tersedia</h2><p>Dosen belum memberikan penilaian untuk tugas ini.</p></div></section>
	@endif
</x-layout>
