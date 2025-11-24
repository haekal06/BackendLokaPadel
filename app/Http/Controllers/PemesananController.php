<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use App\Models\User;
use App\Models\Lapangan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PemesananController extends Controller
{
    public function index()
    {
        $pemesanans = Pemesanan::with(['lapangan', 'user'])->paginate(10);

        $now = Carbon::now();

        foreach ($pemesanans as $pemesanan) {
            $start = Carbon::parse($pemesanan->tanggal . ' ' . $pemesanan->waktu);
            $end   = Carbon::parse($pemesanan->tanggal . ' ' . $pemesanan->waktu_selesai);

            $statusMain = 'Mendatang';
            if ($now->lt($start)) {
                $statusMain = 'Mendatang';
            } elseif ($now->between($start, $end)) {
                $statusMain = 'Berlangsung';
            } else {
                $statusMain = 'Selesai';
            }

            $pemesanan->status_main = $statusMain;
            // Kalau mau disimpan ke DB:
            // $pemesanan->saveQuietly();
        }

        return view('pemesanan.index', compact('pemesanans'));
    }

    public function create()
    {
        $users = User::all();
        $lapangans = Lapangan::all();
        return view('pemesanan.create', compact('users', 'lapangans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'lapangan_id'       => 'required|exists:lapangans,id',
            'tanggal'           => 'required|date',
            'waktu'             => 'required',
            'durasi'            => 'required|numeric|min:1',
            'status_pembayaran' => 'nullable|in:Pending,Sukses,Batal',
            'metode_pembayaran' => 'nullable|in:Belum Bayar,Transfer Bank,QRIS',
        ]);

        $isBentrok = Pemesanan::checkAvailability(
            $request->input('lapangan_id'),
            $request->input('tanggal'),
            $request->input('waktu'),
            (int) $request->input('durasi')
        );

        if ($isBentrok) {
            return back()->with('error', 'Slot waktu yang dipilih sudah terpesan.')->withInput();
        }

        $lapangan   = Lapangan::findOrFail($request->input('lapangan_id'));
        $totalHarga = $lapangan->harga * (int) $request->input('durasi');

        // Hitung waktu selesai
        $waktuParts   = explode(':', (string) $request->input('waktu'));
        $jam          = isset($waktuParts[0]) ? (int) $waktuParts[0] : 0;
        $menit        = isset($waktuParts[1]) ? (int) $waktuParts[1] : 0;
        $dt           = new \DateTime();
        $dt->setTime($jam + (int) $request->input('durasi'), $menit);
        $waktuSelesai = $dt->format('H:i');

        // Aturan metode/status
        $metodePembayaran = $request->input('metode_pembayaran', 'Belum Bayar');
        $statusPembayaran = $request->input('status_pembayaran', 'Pending');
        if ($metodePembayaran === 'Belum Bayar') {
            $statusPembayaran = 'Pending';
        }

        // Mapping status_pembayaran → status teknis
        $status = 'pending';
        if ($statusPembayaran === 'Sukses') {
            $status = 'paid';
        } elseif ($statusPembayaran === 'Batal') {
            $status = 'failed';
        }

        // Status main default: Mendatang (karena booking biasanya untuk depan)
        $statusMain = 'Mendatang';

        Pemesanan::create([
            'user_id'           => $request->input('user_id'),
            'lapangan_id'       => $request->input('lapangan_id'),
            'tanggal'           => $request->input('tanggal'),
            'waktu'             => $request->input('waktu'),
            'durasi'            => (int) $request->input('durasi'),
            'total_harga'       => $totalHarga,
            'waktu_selesai'     => $waktuSelesai,
            'status'            => $status,
            'status_main'       => $statusMain,
            'status_pembayaran' => $statusPembayaran,
            'metode_pembayaran' => $metodePembayaran,
        ]);

        return redirect()->route('pemesanan.index')->with('message', 'Pemesanan berhasil dibuat!');
    }

    public function edit(Pemesanan $pemesanan)
    {
        $users = User::all();
        $lapangans = Lapangan::all();
        return view('pemesanan.edit', compact('pemesanan', 'users', 'lapangans'));
    }

    public function update(Request $request, Pemesanan $pemesanan)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'lapangan_id'       => 'required|exists:lapangans,id',
            'tanggal'           => 'required|date',
            'waktu'             => 'required',
            'durasi'            => 'required|numeric|min:1',
            'status_pembayaran' => 'nullable|in:Pending,Sukses,Batal',
            'metode_pembayaran' => 'nullable|in:Belum Bayar,Transfer Bank,QRIS,Midtrans',
        ]);

        $isBentrok = Pemesanan::checkAvailability(
            $request->input('lapangan_id'),
            $request->input('tanggal'),
            $request->input('waktu'),
            (int) $request->input('durasi'),
            $pemesanan->id
        );

        if ($isBentrok) {
            return back()->with('error', 'Slot waktu yang dipilih sudah terpesan.')->withInput();
        }

        $lapangan   = Lapangan::findOrFail($request->input('lapangan_id'));
        $totalHarga = $lapangan->harga * (int) $request->input('durasi');

        $waktuParts   = explode(':', (string) $request->input('waktu'));
        $jam          = isset($waktuParts[0]) ? (int) $waktuParts[0] : 0;
        $menit        = isset($waktuParts[1]) ? (int) $waktuParts[1] : 0;
        $dt           = new \DateTime();
        $dt->setTime($jam + (int) $request->input('durasi'), $menit);
        $waktuSelesai = $dt->format('H:i');

        $metodePembayaran = $request->input('metode_pembayaran', $pemesanan->metode_pembayaran ?? 'Belum Bayar');
        $statusPembayaran = $request->input('status_pembayaran', $pemesanan->status_pembayaran ?? 'Pending');

        if ($metodePembayaran === 'Belum Bayar') {
            $statusPembayaran = 'Pending';
        }

        $status = $pemesanan->status ?? 'pending';
        if ($statusPembayaran === 'Sukses') {
            $status = 'paid';
        } elseif ($statusPembayaran === 'Pending') {
            $status = 'pending';
        } elseif ($statusPembayaran === 'Batal') {
            $status = 'failed';
        }

        // Hitung status_main lagi
        $start = Carbon::parse($request->input('tanggal') . ' ' . $request->input('waktu'));
        $end   = Carbon::parse($request->input('tanggal') . ' ' . $waktuSelesai);
        $now   = Carbon::now();

        if ($now->lt($start)) {
            $statusMain = 'Mendatang';
        } elseif ($now->between($start, $end)) {
            $statusMain = 'Berlangsung';
        } else {
            $statusMain = 'Selesai';
        }

        $pemesanan->update([
            'user_id'           => $request->input('user_id'),
            'lapangan_id'       => $request->input('lapangan_id'),
            'tanggal'           => $request->input('tanggal'),
            'waktu'             => $request->input('waktu'),
            'durasi'            => (int) $request->input('durasi'),
            'total_harga'       => $totalHarga,
            'waktu_selesai'     => $waktuSelesai,
            'status'            => $status,
            'status_main'       => $statusMain,
            'status_pembayaran' => $statusPembayaran,
            'metode_pembayaran' => $metodePembayaran,
        ]);

        if ($statusPembayaran === 'Batal') {
            return redirect()->route('pemesanan.index')->with('message', 'Pemesanan Berhasil Dibatalkan !');
        }

        return redirect()->route('pemesanan.index')->with('message', 'Pemesanan berhasil diupdate!');
    }

    public function destroy(Pemesanan $pemesanan)
    {
        try {
            $pemesanan->delete();
            return redirect()->route('pemesanan.index')->with('message', 'Pemesanan berhasil dihapus!');
        } catch (\Throwable $e) {
            return redirect()->route('pemesanan.index')->with('error', 'Pemesanan tidak dapat dihapus: ' . $e->getMessage());
        }
    }
}
