<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',  // Pastikan email unik
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|max:2048',
            'role' => 'required|string|in:admin,pelanggan', // Mengganti role user menjadi pelanggan
        ];
    }
}
