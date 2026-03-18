<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'pickup_id',
        'user_name',
        'waste_type',
        'waste_weight',
        'points_given',
    ];

    // Relasi ke admin yang melakukan aksi
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Relasi ke pickup (nullable karena bisa dihapus)
    public function pickup()
    {
        return $this->belongsTo(Pickup::class);
    }
}