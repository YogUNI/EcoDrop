<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    use HasFactory;

        protected $fillable = [
        'user_id',
        'type',
        'weight',
        'pickup_date',
        'address',
        'phone',
        'status',
        'points_earned',
        'handled_by',
        'latitude',
        'longitude',
        'photo',
        'notes',
    ];

    // Relasi ke User (pemilik setoran)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke admin yang handle
    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    // Relasi ke activity logs
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function messages()
    {
    return $this->hasMany(Message::class);
    }
}