<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LapanganController extends Controller
{
    public function index()
    {
        // Menampilkan daftar lapangan dengan pagination
        $lapangans = Lapangan::paginate(10);
        return view('lapangan.index', compact('lapangans'));
    }

    public function create()
    {
        // Menampilkan form untuk membuat lapangan baru
        return view('lapangan.create');
    }

    public function store(Request $request)
    {
        // Validasi data yang diterima dari form
        $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Membuat instansi baru model Lapangan
        $lapangan = new Lapangan();
        $lapangan->nama = $request->nama;
        $lapangan->harga = $request->harga;

        // Menangani upload foto (disimpan di storage/app/public/lapangan)
        if ($request->hasFile('foto')) {
            // Simpan ke disk "public", folder "lapangan"
            // hasil: lapangan/namafile.jpg
            $path = $request->file('foto')->store('lapangan', 'public');
            $lapangan->foto = $path; // simpan path relatif ke kolom "foto"
        }

        // Menyimpan data lapangan
        $lapangan->save();

        // Redirect ke halaman daftar lapangan dengan pesan sukses
        return redirect()->route('lapangan.index')->with('message', 'Lapangan created successfully!');
    }

    public function edit(Lapangan $lapangan)
    {
        // Menampilkan form edit untuk lapangan yang dipilih
        return view('lapangan.edit', compact('lapangan'));
    }

    public function update(Request $request, Lapangan $lapangan)
    {
        // Validasi data yang diterima dari form
        $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Mengupdate data nama dan harga lapangan
        $lapangan->nama = $request->nama;
        $lapangan->harga = $request->harga;

        // Jika ada foto baru yang di-upload, proses upload foto baru
        if ($request->hasFile('foto')) {
            // Menghapus foto lama jika ada di storage disk "public"
            if ($lapangan->foto && Storage::disk('public')->exists($lapangan->foto)) {
                Storage::disk('public')->delete($lapangan->foto);
            }

            // Menyimpan foto baru ke storage/app/public/lapangan
            $path = $request->file('foto')->store('lapangan', 'public');
            $lapangan->foto = $path;
        }

        // Menyimpan perubahan data lapangan
        $lapangan->save();

        // Redirect ke halaman daftar lapangan dengan pesan sukses
        return redirect()->route('lapangan.index')->with('message', 'Lapangan updated successfully!');
    }

    public function destroy(Lapangan $lapangan)
    {
        // Menghapus foto dari storage (jika ada)
        if ($lapangan->foto && Storage::disk('public')->exists($lapangan->foto)) {
            Storage::disk('public')->delete($lapangan->foto);
        }

        // Menghapus data lapangan dari database
        $lapangan->delete();

        // Redirect ke halaman daftar lapangan dengan pesan sukses
        return redirect()->route('lapangan.index')->with('message', 'Lapangan deleted successfully!');
    }
}
