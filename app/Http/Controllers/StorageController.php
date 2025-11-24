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
        $filePath = storage_path('app/public/' . $path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $mime = mime_content_type($filePath);

        return response()->file($filePath, [
            'Content-Type'                => $mime,
            'Access-Control-Allow-Origin' => '*',  // <-- kunci CORS
        ]);
    }
}
