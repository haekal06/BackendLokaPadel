<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    /**
     * REGISTER PELANGGAN via API (dipakai Flutter).
     */
    public function registerPelanggan(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Buat user baru dengan role pelanggan
        $user = User::create([
            'name'      => $validated['name'],
            'last_name' => '',              // bisa nanti kamu isi dari form lain
            'email'     => $validated['email'],
            'password'  => $validated['password'], // di-hash otomatis oleh mutator
            'role'      => 'pelanggan',
        ]);

        // Optional: langsung buat token supaya user auto login setelah register
        $token = $user->createToken('loka_padel_pelanggan')->plainTextToken;

        return response()->json([
            'message' => 'Pendaftaran pelanggan berhasil.',
            'user'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'last_name' => $user->last_name,
                'email'     => $user->email,
                'role'      => $user->role,
            ],
            'token'   => $token,
        ], 201);
    }

    /**
     * LOGIN PELANGGAN via API (dipakai Flutter).
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Cek kredensial
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Batasi HANYA pelanggan
        if (!$user->isPelanggan()) {
            return response()->json([
                'message' => 'Akses hanya untuk pelanggan.',
            ], 403);
        }

        // Buat token API (Laravel Sanctum)
        $token = $user->createToken('loka_padel_pelanggan')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'last_name' => $user->last_name,
                'email'     => $user->email,
                'role'      => $user->role,
            ],
        ], 200);
    }

    /**
     * Profil user yang sedang login (cek token).
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout: hapus token yang sedang dipakai.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
