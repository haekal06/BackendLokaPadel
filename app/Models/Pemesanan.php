<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property int $lapangan_id
 * @property string $tanggal
 * @property string $waktu
 * @property int $durasi
 * @property string $waktu_selesai
 * @property string $status_pembayaran
 * @property string $metode_pembayaran
 * @property string $total_harga
 * @property string|null $status
 * @property string|null $order_id
 * @property string|null $snap_token
 * @property string|null $status_main
 */
class Pemesanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lapangan_id',
        'tanggal',
        'waktu',
        'durasi',
        'total_harga',
        'waktu_selesai',

        // status & pembayaran
        'status',             // teknis: pending, paid, failed, dll
        'status_pembayaran',  // Pending, Sukses, Batal
        'metode_pembayaran',  // Belum Bayar, Transfer Bank, QRIS, Midtrans

        // Midtrans
        'order_id',
        'snap_token',
        'customer_first_name',
        'customer_last_name',
        'customer_email',

        // status main (Mendatang / Berlangsung / Selesai)
        'status_main',
    ];

    protected $casts = [
        'total_harga'   => 'decimal:2',
        'waktu_selesai' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    /**
     * Cek bentrok slot jadwal.
     * return true jika ADA bentrok.
     */
    public static function checkAvailability($lapanganId, $tanggal, $waktu, $durasi, $excludeId = null)
    {
        // Hitung waktu selesai
        $waktuParts   = explode(':', (string) $waktu);
        $jam          = isset($waktuParts[0]) ? (int) $waktuParts[0] : 0;
        $menit        = isset($waktuParts[1]) ? (int) $waktuParts[1] : 0;

        $dt = new \DateTime();
        $dt->setTime($jam + (int) $durasi, $menit);
        $waktuSelesai = $dt->format('H:i');

        // Bangun query
        $query = static::where('lapangan_id', $lapanganId)
            ->where('tanggal', $tanggal)
            ->where(function ($q) use ($waktu, $waktuSelesai) {
                $q->whereBetween('waktu', [$waktu, $waktuSelesai])
                    ->orWhere(function ($q2) use ($waktu, $waktuSelesai) {
                        $q2->where('waktu', '<', $waktu)
                            ->where('waktu_selesai', '>', $waktuSelesai);
                    });
            });

        if (!is_null($excludeId)) {
            $query->where('id', '<>', $excludeId);
        }

        return $query->exists();
    }
}
