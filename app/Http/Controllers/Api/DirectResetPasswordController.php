<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
class DirectResetPasswordController extends Controller
{
    public function checkUser(Request $request)
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['identifier'])
            ->orWhere('nim_nip', $validated['identifier'])
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'NIM atau Email tidak ditemukan.'
            ], 422);
        }

        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akun admin tidak dapat mengganti kata sandi melalui fitur ini.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengguna ditemukan.'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $validated['identifier'])
            ->orWhere('nim_nip', $validated['identifier'])
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.'
            ], 404);
        }

        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akun admin tidak dapat mengganti kata sandi melalui fitur ini.'
            ], 403);
        }

        $user->password = $validated['password'];
        $user->save();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui.'
        ], 200);
    }
}