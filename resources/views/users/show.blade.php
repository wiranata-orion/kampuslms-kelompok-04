<x-layout title="Detail Pengguna">
    <h1>{{ $user->name }}</h1>
    <p>Email: {{ $user->email }}</p>
    <p>Role: {{ $user->role }}</p>
    <p>NIM/NIP: {{ $user->nim_nip ?? '-' }}</p>

    <a href="{{ route('users.edit', $user) }}">Edit</a>

    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('Yakin hapus pengguna ini?')">Hapus</button>
    </form>

    <a href="{{ route('users.index') }}">Kembali ke daftar</a>
</x-layout>