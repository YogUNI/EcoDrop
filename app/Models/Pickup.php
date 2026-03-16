<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    use HasFactory;

    // Buka gembok kolom biar bisa diisi
    protected $fillable = [
        'user_id',
        'type',
        'weight',
        'status',
        'points_earned',
        'pickup_date',
    ];

    // Relasi ke User (Syarat Dosen: Tabel dengan Relasi)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}