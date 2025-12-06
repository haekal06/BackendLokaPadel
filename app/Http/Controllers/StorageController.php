<?php

namespace App\Http\Controllers;

class StorageController extends Controller
{
    /**
     * Menyajikan file dari storage/app/public dengan header CORS.
     *
     * Contoh URL:
     *   GET /storage/lapangan/nama_file.jpg
     */
    public function show($path)
    {
        // path relatif yg dikirim adalah "profile_photos/xxx.jpg"
        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        $mime = mime_content_type($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mime,

            // Header CORS sederhana (membantu terutama di Flutter Web)
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        ]);
    }
}
