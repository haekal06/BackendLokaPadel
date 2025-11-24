<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Jika password diisi, password_confirmation harus sesuai, jika tidak, tidak perlu memeriksa password_confirmation
        $rules = [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->route('user')->id, // Cek email selain email yang sedang diedit
            'password' => 'nullable|string|min:8|confirmed',  // Password hanya wajib jika diubah
            'photo' => 'nullable|image|max:2048',
            'role' => 'required|string|in:admin,pelanggan', // Pastikan role valid
        ];

        return $rules;
    }
}
