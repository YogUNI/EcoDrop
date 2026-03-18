<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('sender');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('pickup.' . $this->message->pickup_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'pickup_id'   => $this->message->pickup_id,
            'sender_id'   => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'sender_photo'=> $this->message->sender->getPhotoUrl(),
            'sender_role' => $this->message->sender->role,
            'message'     => $this->message->message,
            'created_at'  => $this->message->created_at->format('H:i'),
        ];
    }
}