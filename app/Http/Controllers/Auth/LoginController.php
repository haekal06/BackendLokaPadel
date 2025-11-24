<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    // Menampilkan form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Memproses login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Validasi email dan password
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        // Jika kredensial valid, login pengguna
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Cek apakah role pengguna adalah 'pelanggan'
            if ($user->isPelanggan()) {
                // Arahkan pelanggan ke halaman dengan notifikasi
                return redirect()->route('pelanggan.dashboard')->with('message', 'You are logged in as a pelanggan and cannot access this page.');
            }

            // Jika admin, arahkan ke backend atau dashboard admin
            if ($user->isAdmin()) {
                return redirect()->intended('/dashboard');  // Ganti dengan rute yang sesuai untuk admin
            }
        }

        return Redirect::back()->with('error', 'Invalid credentials.');
    }

    public function logout(Request $request)
    {
        Auth::logout();  // Log out the user

        $request->session()->invalidate();  // Invalidate the session

        $request->session()->regenerateToken();  // Regenerate CSRF token

        return redirect('/');  // Redirect to login page or any other page
    }
}
