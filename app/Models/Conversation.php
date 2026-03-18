<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'assigned_admin_id',
        'is_handled',
        'is_closed',
        'closed_at',
        'last_message_at',
    ];

    protected $casts = [
        'is_handled'      => 'boolean',
        'is_closed'       => 'boolean',
        'last_message_at' => 'datetime',
        'closed_at'       => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(ConversationMessage::class)->latestOfMany();
    }

    public function unreadFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }
}