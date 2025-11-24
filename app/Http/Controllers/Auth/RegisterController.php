<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function __construct()
    {
        // Hanya guest (yang belum login) yang boleh mengakses halaman register
        $this->middleware('guest');
    }

    /**
     * Tampilkan form register.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Proses register admin baru.
     */
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'last_name'             => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Buat user baru dengan role admin
        $user = User::create([
            'name'      => $request->name,
            'last_name' => $request->last_name,
            'email'     => $request->email,
            // password akan di-hash otomatis oleh mutator setPasswordAttribute
            'password'  => $request->password,
            'role'      => 'admin',
        ]);

        // Login otomatis setelah register
        Auth::login($user);

        return redirect()
            ->route('dashboard')
            ->with('message', 'Registrasi berhasil. Anda login sebagai admin.');
    }
}
