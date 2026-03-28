<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username / Password salah'
            ], 401);
        }

        // OPTIONAL: hapus token lama biar tidak numpuk
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'role' => $user->role
        ]);
    }
    public function register(Request $request)
{
    $request->validate([
        'username' => 'required|unique:users,username',
        'password' => 'required|min:6'
    ]);

    $user = User::create([
        'username' => $request->username,
        'password' => Hash::make($request->password),
        'role' => 'customer'
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Registrasi berhasil'
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}