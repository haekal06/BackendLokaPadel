<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use Illuminate\Http\Request;

class LapanganApiController extends Controller
{
    /**
     * GET /api/lapangan
     * Ambil list semua lapangan (untuk Flutter).
     */
    public function index()
    {
        $lapangan = Lapangan::all()->map(function ($item) {
            return [
                'id'            => $item->id,
                'nama'          => $item->nama ?? '',
                'harga_per_jam' => $item->harga ?? 0,
                'gambar_url'    => $this->buildImageUrl($item->foto),
            ];
        });

        return response()->json($lapangan, 200);
    }

    /**
     * GET /api/lapangan/{id}
     * Detail 1 lapangan.
     */
    public function show($id)
    {
        $item = Lapangan::findOrFail($id);

        return response()->json([
            'id'            => $item->id,
            'nama'          => $item->nama ?? '',
            'harga_per_jam' => $item->harga ?? 0,
            'gambar_url'    => $this->buildImageUrl($item->foto),
        ], 200);
    }

    /**
     * ENDPOINT BARU:
     * GET /api/lapangan/image/{encoded}
     * Menyajikan file gambar dengan header CORS.
     *
     * {encoded} = base64_encode('lapangan/nama_file.jpg')
     */
    public function image($encoded)
    {
        // Decode path dari base64
        $path = base64_decode($encoded);

        if (!$path) {
            abort(404);
        }

        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        $mime = mime_content_type($fullPath);

        return response()->file($fullPath, [
            'Content-Type'                => $mime,
            'Access-Control-Allow-Origin' => '*', // <-- CORS di sini
        ]);
    }

    /**
     * Helper untuk membangun URL gambar yang lewat endpoint CORS,
     * bukan langsung ke /storage/...
     */
    private function buildImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Kalau sudah URL full, langsung kembalikan
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        // Simpan di DB sebagai "lapangan/xxx.jpg"
        // Kita encode base64 supaya aman dibawa di URL
        $encoded = base64_encode($path);

        // Hasil akhir jadi: http://127.0.0.1:8000/api/lapangan/image/XXXX
        return url('api/lapangan/image/' . $encoded);
    }
}
