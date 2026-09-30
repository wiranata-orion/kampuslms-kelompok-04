<x-layout title="Peserta · {{ $course->name }}">
	<section class="page-heading"><div><span class="eyebrow">{{ $course->code }} · Administrasi kelas</span><h1>Peserta mata kuliah</h1><p class="subtitle">{{ $course->name }} · {{ $students->total() }} mahasiswa terdaftar.</p></div><a class="btn btn-pink" href="{{ route('admin.courses.enrollments.create', $course) }}">Tambah peserta</a></section>
	<section class="panel">@if ($students->isEmpty()) <div class="empty-state"><span class="empty-mark">0</span><h2>Belum ada peserta</h2><p>Daftarkan mahasiswa ke kelas ini untuk memulai.</p><a class="btn" href="{{ route('admin.courses.enrollments.create', $course) }}">Daftarkan mahasiswa</a></div>
		@else <div class="table-wrap"><table><thead><tr><th>Mahasiswa</th><th>Email</th><th>NIM</th><th>Terdaftar</th></tr></thead><tbody>@foreach ($students as $student)<tr><td class="table-primary">{{ $student->name }}</td><td>{{ $student->email }}</td><td>{{ $student->nim_nip ?: '—' }}</td><td>{{ $student->pivot->enrolled_at ? \Illuminate\Support\Carbon::parse($student->pivot->enrolled_at)->format('d M Y') : '—' }}</td></tr>@endforeach</tbody></table></div> @endif
		<x-pagination :paginator="$students" />
	</section>
</x-layout>
