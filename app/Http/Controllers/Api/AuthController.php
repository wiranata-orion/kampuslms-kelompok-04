<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login()
    {
        return response()->json([
            'message' => 'Login endpoint',
        ]);
    }

    public function logout()
    {
        return response()->json([
            'message' => 'Logout endpoint',
        ]);
    }
}