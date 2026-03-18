<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Message extends Model
{
    protected $fillable = [
        'pickup_id',
        'sender_id',
        'message',
        'is_read',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function pickup()
    {
        return $this->belongsTo(Pickup::class);
    }
}