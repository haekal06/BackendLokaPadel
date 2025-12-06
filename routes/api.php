<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\LapanganApiController;
use App\Http\Controllers\Api\PemesananApiController;
use App\Http\Controllers\Api\BantuanApiController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register-pelanggan', [AuthApiController::class, 'registerPelanggan']);
Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthApiController::class, 'me']);
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/profile/update', [AuthApiController::class, 'updateProfile']);
    Route::post('/change-password', [AuthApiController::class, 'changePassword']);
    Route::post('/profile/photo', [AuthApiController::class, 'updateProfilePhoto']);
    Route::post('/bantuan', [BantuanApiController::class, 'store']);
    Route::get('/bantuan',  [BantuanApiController::class, 'indexByUser']);
});

Route::get('/profile/image/{encoded}', [AuthApiController::class, 'profileImage']);

// API Lapangan
Route::get('/lapangan', [LapanganApiController::class, 'index']);
Route::get('/lapangan/{id}', [LapanganApiController::class, 'show']);
Route::get('/lapangan/image/{encoded}', [LapanganApiController::class, 'image']);

// API Pemesanan
Route::post('/pemesanan', [PemesananApiController::class, 'store']);
Route::get('/pemesanan/status/{order_id}', [PemesananApiController::class, 'checkTransactionStatus']);
Route::get('/pemesanan/user/{user_id}', [PemesananApiController::class, 'riwayatByUser']);

// Webhook Midtrans (Payment Notification URL)
Route::post('/midtrans/notification', [PemesananApiController::class, 'midtransNotification']);
Route::get('/pemesanan/status/{order_id}', [PemesananApiController::class, 'checkTransactionStatus']);

// Cek jam tersedia
Route::get('/pemesanan/jam-tersedia', [PemesananApiController::class, 'jamTersedia']);
