<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('users.index', [
            'title' => 'Daftar Pengguna',
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        return view('users.show', [
            'title' => 'Detail Pengguna',
            'user' => $user,
        ]);
    }

    public function create()
    {
        return view('users.create', [
            'title' => 'Tambah Pengguna',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,dosen,mahasiswa',
            'nim_nip' => 'nullable|string|unique:users,nim_nip',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = bcrypt($validated['password']);
        $user->role = $validated['role'];
        $user->nim_nip = $validated['nim_nip'] ?? null;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'title' => 'Edit Pengguna',
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,dosen,mahasiswa',
            'nim_nip' => 'nullable|string|unique:users,nim_nip,' . $user->id,
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->nim_nip = $validated['nim_nip'] ?? null;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}