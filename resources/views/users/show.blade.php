<x-layout title="Detail Pengguna">
    <h1>{{ $user->name }}</h1>
    <p>Email: {{ $user->email }}</p>
    <p>Role: {{ $user->role }}</p>
    <p>NIM/NIP: {{ $user->nim_nip ?? '-' }}</p>

    <a href="{{ route('users.edit', $user) }}">Edit</a>

<dialog id="modal-hapus-{{ $user->id }}">
    <p>Yakin nih mau dihapus? <strong>{{ $user->name }}</strong></p>
    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">
        @csrf
        @method('DELETE')
        <button type="button" onclick="document.getElementById('modal-hapus-{{ $user->id }}').showModal()">Hapus</button>
        <button type="submit">hapus aja udah</button>
    </form>

    <a href="{{ route('users.index') }}">Kembali ke daftar</a>
</x-layout>