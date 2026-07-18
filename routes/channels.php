<?php

use Illuminate\Support\Facades\Broadcast;

// ─── Default notification channel ─────────────────────────────────────────────
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// ─── Private conversation channel ─────────────────────────────────────────────
// Authorized users:
//   - The user who owns the conversation
//   - Any admin (regular admin sees only unhandled or assigned-to-them conversations)
//   - SuperAdmin (sees everything)
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conv = \App\Models\Conversation::find($conversationId);
    if (!$conv) return false;

    // Owner of the conversation
    if ($user->role === 'user') {
        return (int) $conv->user_id === (int) $user->id;
    }

    // SuperAdmin: access all
    if ($user->role === 'super_admin') {
        return true;
    }

    // Admin: can access if conversation is unhandled OR assigned to them
    if ($user->role === 'admin') {
        return !$conv->is_handled || (int) $conv->assigned_admin_id === (int) $user->id;
    }

    return false;
});

// ─── Private user channel (for conversation list updates) ─────────────────────
// Only the authenticated user can subscribe to their own private channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
