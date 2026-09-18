<x-layout title="Daftar Pengguna">
    <h1>Daftar Pengguna</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <a href="{{ route('users.create') }}">+ Tambah Pengguna</a>

    <form action="{{ route('users.index') }}" method="GET" style="margin: 1rem 0; display: flex; gap: 0.5rem;">
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email...">

        <select name="role">
            <option value="">Semua Role</option>
            <option value="admin" @selected($role === 'admin')>Admin</option>
            <option value="dosen" @selected($role === 'dosen')>Dosen</option>
            <option value="mahasiswa" @selected($role === 'mahasiswa')>Mahasiswa</option>
        </select>

        <button type="submit" class="btn">Cari</button>
        <a href="{{ route('users.index') }}">Reset</a>
    </form>

    <table border="1">
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>NIM/NIP</th>
            <th>Aksi</th>
        </tr>
        @foreach ($users as $user)
            <tr>
                <td><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role }}</td>
                <td>{{ $user->nim_nip ?? '-' }}</td>
                <td>
                    <a href="{{ route('users.edit', $user) }}">Edit</a>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Yakin hapus pengguna ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>

    <x-pagination :paginator="$users" />
</x-layout>