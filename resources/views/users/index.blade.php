<x-layout title="Daftar Pengguna">
    <h1>Daftar Pengguna</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <a href="{{ route('users.create') }}">+ Tambah Pengguna</a>

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
</x-layout>