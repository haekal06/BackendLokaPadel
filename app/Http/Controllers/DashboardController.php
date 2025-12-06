<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\Bantuan;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Hanya user yang sudah login yang boleh akses
        $this->middleware('auth');
    }

    public function index()
    {
        // Total semua pesanan
        $totalPesanan = Pemesanan::count();

        // Total pendapatan dari pesanan dengan pembayaran sukses
        $totalPendapatan = Pemesanan::where('status_pembayaran', 'Sukses')
            ->sum('total_harga');

        // Total keluhan yang masuk
        $totalKeluhan = Bantuan::count();

        // Opsional: keluhan yang masih status "baru"
        $keluhanBaru = Bantuan::where('status', 'baru')->count();

        return view('dashboard', [
            'title'           => 'Dashboard',
            'totalPesanan'    => $totalPesanan,
            'totalPendapatan' => $totalPendapatan,
            'totalKeluhan'    => $totalKeluhan,
            'keluhanBaru'     => $keluhanBaru,
        ]);
    }
}
