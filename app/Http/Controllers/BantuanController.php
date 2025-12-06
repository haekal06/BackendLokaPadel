<?php

namespace App\Http\Controllers;

use App\Models\Bantuan;
use Illuminate\Http\Request;

class BantuanController extends Controller
{
    public function __construct()
    {
        // pastikan hanya admin yang bisa akses (sesuai @can('admin') di blade)
        $this->middleware('can:admin');
    }

    public function index()
    {
        $bantuans = Bantuan::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bantuan.index', [
            'title'    => 'Daftar Bantuan',
            'bantuans' => $bantuans,
        ]);
    }

    public function edit($id)
    {
        $bantuan = Bantuan::with('user')->findOrFail($id);

        return view('bantuan.edit', [
            'title'   => 'Detail & Edit Bantuan',
            'bantuan' => $bantuan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $bantuan = Bantuan::findOrFail($id);

        $data = $request->validate([
            'status'        => 'required|in:baru,diproses,selesai',
            'catatan_admin' => 'nullable|string',
        ]);

        $bantuan->update($data);

        return redirect()
            ->route('bantuan.index')
            ->with('message', 'Data bantuan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $bantuan = Bantuan::findOrFail($id);
        $bantuan->delete();

        return redirect()
            ->route('bantuan.index')
            ->with('message', 'Data bantuan berhasil dihapus.');
    }
}
