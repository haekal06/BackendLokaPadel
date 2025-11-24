<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;  // Menambahkan trait HasApiTokens untuk token API

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;  // Pastikan HasApiTokens ada di sini

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'photo', // Menambahkan kolom 'photo'
        'role',  // Pastikan kolom 'role' ada di sini
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Set the password attribute to be hashed automatically.
     * Jika sudah dalam format bcrypt, jangan di-hash ulang.
     */
    public function setPasswordAttribute($value)
    {
        // Jika string sudah terlihat seperti hash bcrypt ($2y$... dan panjang 60),
        // kita anggap sudah di-hash dan langsung simpan.
        if (is_string($value) && strlen($value) === 60 && str_starts_with($value, '$2y$')) {
            $this->attributes['password'] = $value;
        } else {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * Cek apakah pengguna adalah admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin'; // Memastikan role admin
    }

    /**
     * Cek apakah pengguna adalah pelanggan.
     *
     * @return bool
     */
    public function isPelanggan()
    {
        return $this->role === 'pelanggan'; // Memastikan role pelanggan
    }

    /**
     * Mendapatkan nama lengkap pengguna.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->last_name}"; // Menggabungkan nama depan dan nama belakang
    }
}
