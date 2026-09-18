<x-layout title="Edit Pengguna">
    <h1>Edit Pengguna</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}">

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}">

        <label>Role</label>
        <select name="role">
            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="dosen" {{ old('role', $user->role) == 'dosen' ? 'selected' : '' }}>Dosen</option>
            <option value="mahasiswa" {{ old('role', $user->role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
        </select>

        <label>NIM/NIP (opsional)</label>
        <input type="text" name="nim_nip" value="{{ old('nim_nip', $user->nim_nip) }}">

        <button type="submit">Perbarui</button>
    </form>

    <a href="{{ route('users.index') }}">Kembali ke daftar</a>
</x-layout>