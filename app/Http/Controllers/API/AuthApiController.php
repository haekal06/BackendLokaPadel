<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            'last_name' => '',
            'email'     => $validated['email'],
            'password'  => $validated['password'], // di-hash otomatis oleh mutator
            'role'      => 'pelanggan',
        ]);

        $token = $user->createToken('loka_padel_pelanggan')->plainTextToken;

        $profilePhotoUrl = null;
        if ($user->profile_photo) {
            $encoded = base64_encode($user->profile_photo);
            $profilePhotoUrl = url('/api/profile/image/' . $encoded);
        }

        return response()->json([
            'message' => 'Pendaftaran pelanggan berhasil.',
            'user'    => [
                'id'                => $user->id,
                'name'              => $user->name,
                'last_name'         => $user->last_name,
                'email'             => $user->email,
                'role'              => $user->role,
                'profile_photo_url' => $profilePhotoUrl,
            ],
            'token'   => $token,
        ], 201);
    }

    /**
     * LOGIN PELANGGAN via API (dipakai Flutter).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user->isPelanggan()) {
            return response()->json([
                'message' => 'Akses hanya untuk pelanggan.',
            ], 403);
        }

        $token = $user->createToken('loka_padel_pelanggan')->plainTextToken;

        // === Tambahkan URL foto profil di sini ===
        $profilePhotoUrl = null;
        if ($user->profile_photo) {
            $encoded = base64_encode($user->profile_photo);
            $profilePhotoUrl = url('/api/profile/image/' . $encoded);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'id'                => $user->id,
                'name'              => $user->name,
                'last_name'         => $user->last_name,
                'email'             => $user->email,
                'role'              => $user->role,
                'profile_photo_url' => $profilePhotoUrl,
            ],
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ], 200);
    }

    public function updateProfilePhoto(Request $request)
    {
        $user = $request->user(); // dari sanctum

        $request->validate([
            'photo' => 'required|image|max:2048', // max 2MB
        ]);

        // hapus foto lama kalau ada
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('photo')->store('profile_photos', 'public');

        $user->profile_photo = $path;
        $user->save();

        // Samakan pola dengan lapangan: pakai endpoint image
        $encoded   = base64_encode($path);
        $photoUrl  = url('/api/profile/image/' . $encoded);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'data'    => [
                'profile_photo_url' => $photoUrl,
            ],
        ]);
    }

    /**
     * Endpoint untuk mengambil file foto profil (dipanggil dari Flutter).
     */
    public function profileImage($encoded)
    {
        $path = base64_decode($encoded);
        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        $mime = mime_content_type($fullPath);

        return response()->file($fullPath, [
            'Content-Type'                 => $mime,
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        ]);
    }

    /**
     * Profil user yang sedang login (cek token).
     */
    public function me(Request $request)
    {
        $user = $request->user();

        $profilePhotoUrl = null;
        if ($user->profile_photo) {
            $encoded = base64_encode($user->profile_photo);
            $profilePhotoUrl = url('/api/profile/image/' . $encoded);
        }

        return response()->json([
            'id'                => $user->id,
            'name'              => $user->name,
            'last_name'         => $user->last_name,
            'email'             => $user->email,
            'role'              => $user->role,
            'profile_photo_url' => $profilePhotoUrl,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($data);

        $profilePhotoUrl = null;
        if ($user->profile_photo) {
            $encoded = base64_encode($user->profile_photo);
            $profilePhotoUrl = url('/api/profile/image/' . $encoded);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => [
                'id'                => $user->id,
                'name'              => $user->name,
                'last_name'         => $user->last_name,
                'email'             => $user->email,
                'role'              => $user->role,
                'profile_photo_url' => $profilePhotoUrl,
            ],
        ], 200);
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
