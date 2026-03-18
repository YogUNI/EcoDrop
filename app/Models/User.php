<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'points',
        'is_verified',
        'last_seen_at',
        'is_banned',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_verified'       => 'boolean',
            'is_banned'         => 'boolean',
            'last_seen_at'      => 'datetime',
        ];
    }

    // Relasi ke pickups
    public function pickups()
    {
        return $this->hasMany(Pickup::class);
    }

    // Online = last_seen_at tidak null DAN dalam 5 menit terakhir
    public function isOnline(): bool
    {
        if (!$this->last_seen_at) return false;
        return $this->last_seen_at->diffInMinutes(now()) < 5;
    }

    public function getPhotoUrl(): string
    {
    if ($this->profile_photo) {
        return asset('storage/' . $this->profile_photo);
    }
    // Default avatar pakai UI Avatars
    return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=10b981&color=fff&bold=true&size=128';
    }
}