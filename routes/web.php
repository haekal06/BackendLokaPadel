<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LapanganController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\BantuanController;

// Halaman login default
Route::get('/', function () {
    return view('auth.login');
});

// Rute login dan logout
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Rute register (khusus membuat admin)
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Rute yang bisa diakses oleh semua pengguna yang sudah login
Route::middleware('auth')->group(function () {

    // Rute untuk pelanggan (notifikasi / dashboard pelanggan)
    Route::get('/pelanggan/dashboard', function () {
        return view('pelanggan.dashboard'); // Halaman khusus untuk pelanggan
    })->name('pelanggan.dashboard');

    // Rute untuk backend admin (Hanya admin yang bisa mengakses user management)
    Route::middleware('can:admin')->group(function () {
        Route::resource('user', UserController::class);  // Akses hanya untuk admin
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');  // Admin Dashboard
        Route::resource('lapangan', LapanganController::class);
        Route::resource('pemesanan', PemesananController::class);
        Route::resource('bantuan', BantuanController::class);
    });

    // Rute yang bisa diakses oleh semua pengguna yang sudah login
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');  // Dashboard umum untuk admin dan pelanggan

    // Route untuk Lapangan dan Pemesanan (dapat diakses oleh semua pengguna yang login tapi dibatasi admin)
    Route::resource('lapangan', LapanganController::class)->middleware('can:admin');
    Route::resource('pemesanan', PemesananController::class)->middleware('can:admin');
});

// Form untuk Menambahkan User (ini sebenarnya sudah ter-cover oleh resource di atas,
// tapi aku biarkan karena sudah ada di kode awal)
Route::get('user/create', [UserController::class, 'create'])->name('user.create');
Route::post('user', [UserController::class, 'store'])->name('user.store');

// Form Edit User
Route::get('user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
Route::put('user/{user}', [UserController::class, 'update'])->name('user.update');
Route::delete('user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

Route::get('storage/{path}', [StorageController::class, 'show'])
    ->where('path', '.*');
