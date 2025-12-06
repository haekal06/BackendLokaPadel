<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bantuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deskripsi',
        'status',
        'catatan_admin', // <== penting
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
