<x-layout>
    <x-slot:title>
        {{ $title }}
    </x-slot:title>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">{{ $title }}</h2>
        <a href="{{ route('courses.create') }}" class="btn btn-success">+ Tambah Mata Kuliah</a>
    </div>

    @forelse ($courses as $course)
        <div class="card">
            <h3 style="margin-top: 0; margin-bottom: 0.5rem;">
                {{ $course['nama'] }} ({{ $course['kode'] }})
            </h3>

            <p style="margin-bottom: 0.75rem;">
                <span class="badge">{{ $course['sks'] }} SKS</span>
                <span class="badge">Semester {{ $course['semester'] }}</span>
            </p>

            <p style="margin-bottom: 1rem; color: #475569;">
                Dosen Pengampu: <strong>{{ $course['dosen'] }}</strong>
            </p>

            <div class="action-group">
                {{-- Rute courses.show --}}
                <a href="{{ route('courses.show', ['id' => $course['id']]) }}" class="btn">
                    Lihat Detail
                </a>

                {{-- Rute courses.edit --}}
                <a href="{{ route('courses.edit', ['id' => $course['id']]) }}" class="btn btn-warning">
                    Edit
                </a>

                {{-- Form DELETE untuk rute courses.destroy --}}
                <form action="{{ route('courses.destroy', ['id' => $course['id']]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card">
            <p style="color: #64748b; margin: 0;">Belum ada mata kuliah yang tersedia.</p>
        </div>
    @endforelse 
</x-layout>