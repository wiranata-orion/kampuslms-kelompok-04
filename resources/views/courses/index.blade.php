<x-layout title="Daftar Mata Kuliah">
    <h1>Daftar Mata Kuliah</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <a href="{{ route('courses.create') }}">+ Tambah Mata Kuliah</a>

    <form action="{{ route('courses.index') }}" method="GET" style="margin: 1rem 0; display: flex; gap: 0.5rem;">
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode atau nama...">

        <select name="status">
            <option value="">Semua Status</option>
            <option value="draft" @selected($status === 'draft')>Draft</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="archived" @selected($status === 'archived')>Archived</option>
        </select>

        <button type="submit" class="btn">Cari</button>
        <a href="{{ route('courses.index') }}">Reset</a>
    </form>

    <table border="1">
        <tr>
            <th>Kode</th>
            <th>Nama</th>
            <th>SKS</th>
            <th>Dosen</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        @foreach ($courses as $course)
            <tr>
                <td>{{ $course->code }}</td>
                <td><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a></td>
                <td>{{ $course->sks }}</td>
                <td>{{ $course->lecturer->name ?? '-' }}</td>
                <td>{{ $course->status }}</td>
                <td>
                    <a href="{{ route('courses.edit', $course) }}">Edit</a>
                    <form action="{{ route('courses.destroy', $course) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Yakin hapus mata kuliah ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>

    <div style="margin-top: 1rem;">
        {{ $courses->links() }}
    </div>
</x-layout>