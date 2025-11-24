<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddUserRequest;
use App\Http\Requests\EditUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        // Hanya admin yang bisa mengakses backend
        $this->middleware('auth');
        $this->middleware('can:admin'); // Membatasi akses hanya untuk admin
    }

    public function index()
    {
        // Ambil data pelanggan dan paginasi
        $users = User::paginate(10);
        return view('user.index', compact('users'));
    }

    public function create()
    {
        return view('user.create', [
            'title' => 'New Pelanggan', // Ubah judul menjadi "New Pelanggan"
        ]);
    }

    public function store(AddUserRequest $request)
    {
        $data = [
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',  // Set role default menjadi pelanggan
        ];

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('users', 'public');
            $data['photo'] = $photo;
        }

        User::create($data);

        return redirect()->route('user.index')->with('message', 'Pelanggan added successfully!');
    }
    // Menambahkan metode register untuk menangani pendaftaran pengguna melalui API

    public function edit(User $user)
    {
        return view('user.edit', [
            'title' => 'Edit Pelanggan', // Ubah judul menjadi "Edit Pelanggan"
            'user' => $user
        ]);
    }

    public function update(EditUserRequest $request, User $user)
    {
        // Mengupdate data tanpa wajib mengubah email
        $user->name = $request->name;
        $user->last_name = $request->last_name;

        if ($request->filled('email') && $user->email !== $request->email) {
            $user->email = $request->email; // Hanya update email jika diubah
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $photo = $request->file('photo')->store('users', 'public');
            $user->photo = $photo;
        }

        // Update role jika admin ingin mengubahnya
        if (Auth::user()->isAdmin() && $request->role) {
            $user->role = $request->role;
        }

        $user->save();

        return redirect()->route('user.index')->with('message', 'Pelanggan updated successfully!');
    }

    public function destroy(User $user)
    {
        if (Auth::id() == $user->getKey()) {
            return redirect()->route('user.index')->with('warning', 'Cannot delete yourself!');
        }

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return redirect()->route('user.index')->with('message', 'Pelanggan deleted successfully!');
    }
}
