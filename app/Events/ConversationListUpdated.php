<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Notifies connected clients that a conversation list needs updating.
 * Uses PUBLIC channels to avoid needing /broadcasting/auth complexity.
 *
 * Channels:
 *   - user-updates.{userId}     → user's own devices
 *   - chat-admin-updates        → all admins/superadmins
 */
class ConversationListUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int    $userId;
    public int    $conversationId;
    public string $action;
    public ?int   $assignedAdminId;

    public function __construct(int $userId, int $conversationId, string $action, ?int $assignedAdminId = null)
    {
        $this->userId          = $userId;
        $this->conversationId  = $conversationId;
        $this->action          = $action;
        $this->assignedAdminId = $assignedAdminId;
    }

    public function broadcastOn(): array
    {
        return [
            // Public channels — no auth required, simpler and reliable
            new Channel('user-updates.' . $this->userId),
            new Channel('chat-admin-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id'   => $this->conversationId,
            'user_id'           => $this->userId,
            'action'            => $this->action,
            'assigned_admin_id' => $this->assignedAdminId,
        ];
    }
}
