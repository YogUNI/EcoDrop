<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = [
        'name',
        'points_required',
        'description',
        'image',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Cek apakah reward masih bisa ditukar (aktif & ada stok).
     */
    public function isAvailable(): bool
    {
        if (!$this->is_active) return false;
        // null = unlimited, jika ada angka harus > 0
        if (!is_null($this->stock) && $this->stock <= 0) return false;
        return true;
    }
}
