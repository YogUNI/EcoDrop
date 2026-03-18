<?php

namespace App\Events;

use App\Models\ConversationMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ConversationMessage $message;

    public function __construct(ConversationMessage $message)
    {
        $this->message = $message->load('sender', 'pickup');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('conversation.' . $this->message->conversation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $msg = $this->message;
        $sender = $msg->sender;

        $data = [
            'id'              => $msg->id,
            'conversation_id' => $msg->conversation_id,
            'sender_id'       => $msg->sender_id,
            'sender_name'     => $sender->name,
            'sender_photo'    => $sender->getPhotoUrl(),
            'sender_role'     => $sender->role,
            'type'            => $msg->type,
            'content'         => $msg->content,
            'pickup_id'       => $msg->pickup_id,
            'is_read'         => $msg->is_read,
            'created_at'      => $msg->created_at->format('H:i'),
        ];

        // Kalau tipe pickup_card, sertakan data setoran
        if ($msg->type === 'pickup_card' && $msg->pickup) {
            $data['pickup'] = [
                'id'          => $msg->pickup->id,
                'type'        => $msg->pickup->type,
                'weight'      => $msg->pickup->weight,
                'status'      => $msg->pickup->status,
                'pickup_date' => $msg->pickup->pickup_date,
            ];
        }

        return $data;
    }
}