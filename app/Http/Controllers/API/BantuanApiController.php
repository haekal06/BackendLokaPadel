<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bantuan;
use Illuminate\Http\Request;

class BantuanApiController extends Controller
{
    // kirim keluhan (mungkin sudah kamu punya)
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'deskripsi' => 'required|string',
        ]);

        $bantuan = Bantuan::create([
            'user_id'       => $user->id,
            'deskripsi'     => $data['deskripsi'],
            'status'        => 'baru',
            'catatan_admin' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Keluhan berhasil dikirim.',
            'data'    => $bantuan,
        ], 201);
    }

    // riwayat keluhan milik user yang login
    public function indexByUser(Request $request)
    {
        $user = $request->user();

        $items = Bantuan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get([
                'id',
                'deskripsi',
                'status',
                'catatan_admin',
                'created_at',
                'updated_at',
            ]);

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }
}
