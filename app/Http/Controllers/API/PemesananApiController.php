<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Pemesanan;
use App\Models\Lapangan;
use App\Models\User;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;
use Carbon\Carbon;
use Exception;

class PemesananApiController extends Controller
{
    /**
     * Membuat pemesanan baru + generate Snap Token Midtrans.
     */
    public function store(Request $request)
    {
        try {
            // 1. Validasi input
            $validated = $request->validate([
                'lapangan_id' => 'required|exists:lapangans,id',
                'tanggal'     => 'required|date',
                'waktu'       => 'required|date_format:H:i',
                'durasi'      => 'required|numeric|min:1',
                'user_id'     => 'required|exists:users,id',
            ]);

            // 2. Ambil data lapangan
            $lapangan = Lapangan::find($validated['lapangan_id']);
            if (!$lapangan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lapangan tidak ditemukan.',
                ], 404);
            }

            // 3. Cek bentrok jadwal
            $isBentrok = Pemesanan::checkAvailability(
                $validated['lapangan_id'],
                $validated['tanggal'],
                $validated['waktu'],
                (int) $validated['durasi']
            );

            if ($isBentrok) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal sudah terisi, silakan pilih jam lain.',
                ], 422);
            }

            // 4. Hitung total harga di backend
            $hargaPerJam     = $lapangan->harga_per_jam ?? $lapangan->harga ?? 0;
            $finalTotalHarga = $hargaPerJam * (int) $validated['durasi'];

            if ($finalTotalHarga < 1000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total harga tidak valid.',
                ], 400);
            }

            // 5. Hitung waktu selesai
            $waktuParts = explode(':', $validated['waktu']);
            $jam   = (int) ($waktuParts[0] ?? 0);
            $menit = (int) ($waktuParts[1] ?? 0);

            $dt = new \DateTime();
            $dt->setTime($jam + (int) $validated['durasi'], $menit);
            $waktuSelesai = $dt->format('H:i');

            // 6. Hitung status_main (Mendatang / Berlangsung / Selesai)
            $start = Carbon::parse($validated['tanggal'] . ' ' . $validated['waktu']);
            $end   = Carbon::parse($validated['tanggal'] . ' ' . $waktuSelesai);
            $now   = Carbon::now();

            if ($now->lt($start)) {
                $statusMain = 'Mendatang';
            } elseif ($now->between($start, $end)) {
                $statusMain = 'Berlangsung';
            } else {
                $statusMain = 'Selesai';
            }

            // 7. Simpan pemesanan dulu dengan status pending + Midtrans
            $pemesananData = [
                'lapangan_id'       => $validated['lapangan_id'],
                'user_id'           => $validated['user_id'],
                'tanggal'           => $validated['tanggal'],
                'waktu'             => $validated['waktu'],
                'durasi'            => $validated['durasi'],
                'waktu_selesai'     => $waktuSelesai,
                'total_harga'       => $finalTotalHarga,

                'status'            => 'pending',   // teknis
                'status_pembayaran' => 'Pending',   // tampilan
                'metode_pembayaran' => 'Midtrans',  // pesanan ini lewat Midtrans
                'status_main'       => $statusMain,
            ];

            $pemesanan = Pemesanan::create($pemesananData);

            // 8. Konfigurasi Midtrans
            Config::$serverKey    = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production', false);
            Config::$isSanitized  = true;
            Config::$is3ds        = true;

            // 9. Data user untuk customer_details
            $user = User::find($validated['user_id']);

            // 10. order_id rapi: ORDER-{id pemesanan}
            $orderId = 'ORDER-' . $pemesanan->id;

            // 11. Parameter Midtrans
            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => (int) $finalTotalHarga,
                ],
                'customer_details' => [
                    'first_name' => $user->name ?? 'Customer',
                    'email'      => $user->email,
                ],
            ];

            // 12. Generate SnapToken
            $snapToken = Snap::getSnapToken($params);
            Log::info('Snap Token: ' . $snapToken);

            // 13. Update pemesanan dengan snap_token dan order_id
            $pemesanan->update([
                'snap_token' => $snapToken,
                'order_id'   => $orderId,
            ]);

            // 14. Buat Snap URL
            $snapBaseUrl = Config::$isProduction
                ? 'https://app.midtrans.com/snap/v2/vtweb/'
                : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

            $snapUrl = $snapBaseUrl . $snapToken;

            // 15. Response ke frontend
            return response()->json([
                'success'   => true,
                'message'   => 'Pemesanan berhasil dibuat.',
                'snapToken' => $snapToken,
                'snapUrl'   => $snapUrl,
                'data'      => $pemesanan->fresh(),
            ], 200);
        } catch (Exception $e) {
            Log::error('Error Pemesanan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Terjadi error saat membuat transaksi Midtrans: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cek status transaksi ke Midtrans berdasarkan order_id (manual / dari Flutter).
     */
    public function checkTransactionStatus($orderId)
    {
        try {
            // Bisa kirim ORDER-123 atau 123
            $id = (int) str_replace('ORDER-', '', $orderId);

            $pemesanan = Pemesanan::find($id);
            if (!$pemesanan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pemesanan / order tidak ditemukan.',
                ], 404);
            }

            // Konfigurasi Midtrans
            Config::$serverKey    = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production', false);
            Config::$isSanitized  = true;
            Config::$is3ds        = true;

            $midtransOrderId = $pemesanan->order_id ?: ('ORDER-' . $pemesanan->id);

            $status = \Midtrans\Transaction::status($midtransOrderId);

            Log::info('Midtrans status raw response', [
                'order_id' => $midtransOrderId,
                'status'   => $status,
            ]);

            $transactionStatus = null;

            if (is_object($status) && isset($status->transaction_status)) {
                $transactionStatus = $status->transaction_status;
            } elseif (is_array($status) && isset($status['transaction_status'])) {
                $transactionStatus = $status['transaction_status'];
            }

            $mapStatus = [
                'capture'    => 'paid',
                'settlement' => 'paid',
                'pending'    => 'pending',
                'cancel'     => 'failed',
                'deny'       => 'failed',
                'expire'     => 'expired',
            ];

            $localStatus = $pemesanan->status ?? 'pending';

            if ($transactionStatus && isset($mapStatus[$transactionStatus])) {
                $localStatus = $mapStatus[$transactionStatus];
            }

            // Sinkron status_pembayaran (Pending / Sukses / Batal)
            $statusPembayaran = $pemesanan->status_pembayaran;
            if ($localStatus === 'paid') {
                $statusPembayaran = 'Sukses';
            } elseif ($localStatus === 'pending') {
                $statusPembayaran = 'Pending';
            } elseif (in_array($localStatus, ['failed', 'expired'])) {
                $statusPembayaran = 'Batal';
            }

            // Hitung status_main lagi berdasarkan waktu sekarang
            $start = Carbon::parse($pemesanan->tanggal . ' ' . $pemesanan->waktu);
            $end   = Carbon::parse($pemesanan->tanggal . ' ' . $pemesanan->waktu_selesai);
            $now   = Carbon::now();

            if ($now->lt($start)) {
                $statusMain = 'Mendatang';
            } elseif ($now->between($start, $end)) {
                $statusMain = 'Berlangsung';
            } else {
                $statusMain = 'Selesai';
            }

            $pemesanan->update([
                'status'            => $localStatus,
                'status_pembayaran' => $statusPembayaran,
                'status_main'       => $statusMain,
            ]);

            return response()->json([
                'success'         => true,
                'order_id'        => $midtransOrderId,
                'midtrans_status' => $status,
                'pemesanan'       => $pemesanan->fresh(),
            ], 200);
        } catch (Exception $e) {
            Log::error('Error cek status Midtrans: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal cek status Midtrans: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook / Payment Notification dari Midtrans
     * URL ini dipanggil langsung oleh Midtrans ketika status pembayaran berubah.
     */

    public function midtransNotification(Request $request)
    {
        try {
            // Konfigurasi Midtrans
            Config::$serverKey    = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production', false);
            Config::$isSanitized  = true;
            Config::$is3ds        = true;

            Log::info('Midtrans Notification Payload', $request->all());

            // Verifikasi signature_key
            $serverKey = Config::$serverKey;

            $orderId      = $request->order_id;
            $statusCode   = $request->status_code;
            $grossAmount  = $request->gross_amount;
            $signatureKey = $request->signature_key;

            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($signatureKey !== $expectedSignature) {
                Log::warning('Midtrans signature not valid', [
                    'order_id' => $orderId,
                ]);

                return response()->json(['message' => 'Invalid signature'], 403);
            }

            // Ambil notifikasi versi SDK
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $paymentType       = $notification->payment_type;
            $fraudStatus       = $notification->fraud_status ?? null;
            $orderId           = $notification->order_id;

            // Cari pemesanan berdasarkan order_id
            $pemesanan = Pemesanan::where('order_id', $orderId)->first();

            if (!$pemesanan) {
                // fallback jika order_id tidak disimpan
                $idPemesanan = (int) str_replace('ORDER-', '', $orderId);
                $pemesanan   = Pemesanan::find($idPemesanan);
            }

            if (!$pemesanan) {
                Log::warning('Pemesanan not found for Midtrans order_id', [
                    'order_id' => $orderId,
                ]);

                return response()->json(['message' => 'Order not found'], 404);
            }

            // Mapping status Midtrans -> status lokal
            $localStatus = $pemesanan->status ?? 'pending';

            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'challenge') {
                        $localStatus = 'challenge';
                    } else if ($fraudStatus == 'accept') {
                        $localStatus = 'paid';
                    }
                } else {
                    $localStatus = 'paid';
                }
            } elseif ($transactionStatus == 'settlement') {
                $localStatus = 'paid';
            } elseif ($transactionStatus == 'pending') {
                $localStatus = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $localStatus = 'failed';
            } elseif (in_array($transactionStatus, ['refund', 'partial_refund'])) {
                $localStatus = 'refunded';
            }

            // Sinkron status_pembayaran (Pending / Sukses / Batal)
            $statusPembayaran = $pemesanan->status_pembayaran;
            if ($localStatus === 'paid') {
                $statusPembayaran = 'Sukses';
            } elseif ($localStatus === 'pending') {
                $statusPembayaran = 'Pending';
            } elseif (in_array($localStatus, ['failed', 'expired', 'cancelled'])) {
                $statusPembayaran = 'Batal';
            }

            // Hitung status_main berdasarkan waktu sekarang
            $start = Carbon::parse($pemesanan->tanggal . ' ' . $pemesanan->waktu);
            $end   = Carbon::parse($pemesanan->tanggal . ' ' . $pemesanan->waktu_selesai);
            $now   = Carbon::now();

            if ($now->lt($start)) {
                $statusMain = 'Mendatang';
            } elseif ($now->between($start, $end)) {
                $statusMain = 'Berlangsung';
            } else {
                $statusMain = 'Selesai';
            }

            $pemesanan->status            = $localStatus;
            $pemesanan->status_pembayaran = $statusPembayaran;
            $pemesanan->status_main       = $statusMain;
            $pemesanan->metode_pembayaran = 'Midtrans';
            $pemesanan->save();

            Log::info('Pemesanan updated from Midtrans notification', [
                'order_id'           => $orderId,
                'transaction_status' => $transactionStatus,
                'local_status'       => $localStatus,
            ]);

            return response()->json(['message' => 'OK']);
        } catch (Exception $e) {
            Log::error('Error in Midtrans notification handler: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error handling notification: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function riwayatByUser($userId)
    {
        $pemesanans = Pemesanan::with('lapangan')
            ->where('user_id', $userId)
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu', 'desc')
            ->get()
            ->map(function ($p) {
                // bangun URL gambar sama seperti di LapanganApiController
                $gambarUrl = null;
                if ($p->lapangan && $p->lapangan->foto) { // SESUAIKAN: 'foto' / 'gambar' / 'image_path'
                    $encodedPath = base64_encode($p->lapangan->foto);
                    $gambarUrl   = url('/api/lapangan/image/' . $encodedPath);
                }

                return [
                    'id'                 => $p->id,
                    'order_id'           => $p->order_id ?? ('ORDER-' . $p->id),
                    'lapangan_nama'      => $p->lapangan->nama ?? 'Lapangan',
                    'lapangan_gambar'    => $gambarUrl,
                    'tanggal'            => $p->tanggal,
                    'waktu'              => $p->waktu,
                    'waktu_selesai'      => $p->waktu_selesai,
                    'durasi'             => $p->durasi,
                    'total_harga'        => (int) $p->total_harga,
                    'status_main'        => $p->status_main ?? 'Mendatang',
                    'status_pembayaran'  => $p->status_pembayaran ?? 'Pending',
                    'metode_pembayaran'  => $p->metode_pembayaran ?? 'Midtrans',
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $pemesanans,
        ]);
    }
}
