<x-layout title="Mata Kuliah · Kampuskin">
    <section class="page-heading"><div><span class="eyebrow">Katalog kampus</span><h1>Jelajahi mata kuliah</h1><p class="subtitle">Cari kelas dan temukan ruang untuk belajar hal baru.</p></div>
        @if (auth()->user()->role === 'admin') <a class="btn btn-pink" href="{{ route('admin.courses.create') }}">Tambah mata kuliah</a> @endif
    </section>
    <section class="panel">
        <div class="toolbar"><form action="{{ route('courses.index') }}" method="GET">
            <input class="field-grow" type="search" name="search" value="{{ $search }}" placeholder="Cari kode atau nama kelas..." aria-label="Cari mata kuliah">
            <select name="status" aria-label="Filter status"><option value="">Semua status</option><option value="draft" @selected($status === 'draft')>Draft</option><option value="active" @selected($status === 'active')>Aktif</option><option value="archived" @selected($status === 'archived')>Arsip</option></select>
            <button class="btn" type="submit">Cari</button><a class="btn btn-quiet" href="{{ route('courses.index') }}">Atur ulang</a>
        </form></div>
        @if ($courses->isEmpty())
            <div class="empty-state"><span class="empty-mark">+</span><h2>Belum ada mata kuliah</h2><p>Coba ubah kata kunci atau filter yang dipilih.</p></div>
        @else
            <div class="table-wrap"><table><thead><tr><th>Kode</th><th>Mata kuliah</th><th>SKS</th><th>Dosen</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                @foreach ($courses as $course)
                    <tr><td><span class="pill pill-blue">{{ $course->code }}</span></td><td><a class="table-primary" href="{{ route('courses.show', $course) }}">{{ $course->name }}</a></td><td>{{ $course->sks }}</td><td>{{ $course->lecturer->name ?? 'Belum ditentukan' }}</td><td><span class="pill {{ $course->status === 'active' ? 'pill-green' : ($course->status === 'archived' ? 'pill-muted' : 'pill-pink') }}">{{ $course->status }}</span></td><td><div class="actions" style="margin:0">
                        <a class="btn btn-quiet btn-small" href="{{ route('courses.show', $course) }}">Detail</a>
                        @if (auth()->user()->role === 'admin') <a class="btn btn-quiet btn-small" href="{{ route('admin.courses.edit', $course) }}">Edit</a><form class="inline-form" action="{{ route('admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Hapus mata kuliah ini?')">@csrf @method('DELETE')<button class="btn btn-danger btn-small" type="submit">Hapus</button></form> @endif
                    </div></td></tr>
                @endforeach
            </tbody></table></div>
        @endif
        <x-pagination :paginator="$courses" />
    </section>
</x-layout>