<x-layout title="Tambah Pengguna">
    <h1>Tambah Pengguna</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name') }}">

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}">

        <label>Password</label>
        <input type="password" name="password">

        <label>Role</label>
        <select name="role">
            <option value="">-- Pilih Role --</option>
            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="dosen" {{ old('role') == 'dosen' ? 'selected' : '' }}>Dosen</option>
            <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
        </select>

        <label>NIM/NIP (opsional)</label>
        <input type="text" name="nim_nip" value="{{ old('nim_nip') }}">

        <button type="submit">Simpan</button>
    </form>

    <a href="{{ route('users.index') }}">Kembali ke daftar</a>
</x-layout>