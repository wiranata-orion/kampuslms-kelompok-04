<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $role = $request->query('role', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, ['admin', 'dosen', 'mahasiswa'], true), function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'title' => 'Daftar Pengguna',
            'users' => $users,
            'search' => $search,
            'role' => $role,
        ]);
    }

    public function show(User $user): View
    {
        return view('users.show', [
            'title' => 'Detail Pengguna',
            'user' => $user,
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'title' => 'Tambah Pengguna',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,dosen,mahasiswa'],
            'nim_nip' => ['nullable', 'string', 'unique:users,nim_nip'],
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        // TIDAK perlu bcrypt()/Hash::make() manual di sini — model User
        // sudah punya cast 'password' => 'hashed', jadi otomatis di-hash
        // saat disimpan. Memanggil bcrypt() manual (seperti versi lama
        // controller ini) jadi redundan.
        $user->password = $validated['password'];
        $user->role = $validated['role'];
        $user->nim_nip = $validated['nim_nip'] ?? null;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'title' => 'Edit Pengguna',
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,dosen,mahasiswa'],
            'nim_nip' => ['nullable', 'string', Rule::unique('users', 'nim_nip')->ignore($user->id)],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->nim_nip = $validated['nim_nip'] ?? null;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Titik rawan yang gampang terlewat: admin bisa saja tidak
        // sengaja menghapus akunnya sendiri dari tabel index. Dicegah
        // eksplisit di sini.
        abort_if(
            $user->id === auth()->id,
            403,
            'Kamu tidak bisa menghapus akunmu sendiri.'
        );

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}