<?php

namespace App\Http\Controllers;

use App\Events\ConversationMessageSent;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Pickup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    // Ambil atau buat conversation untuk user yang login
    public function getOrCreate()
    {
        $user = Auth::user();

        // User hanya punya 1 conversation
        if ($user->role === 'user') {
            $conv = Conversation::firstOrCreate(
                ['user_id' => $user->id],
                ['is_handled' => false]
            );
            return response()->json($this->formatConversation($conv, $user->id));
        }

        // Admin/SuperAdmin: return semua conversation
        $conversations = Conversation::with(['user', 'lastMessage', 'assignedAdmin'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn($c) => $this->formatConversation($c, $user->id));

        return response()->json($conversations);
    }

    // Ambil messages dari 1 conversation
    public function messages($conversationId)
    {
        $conv = Conversation::findOrFail($conversationId);
        $user = Auth::user();

        // User hanya bisa akses conversation miliknya
        if ($user->role === 'user' && $conv->user_id !== $user->id) {
            abort(403);
        }

        // Mark as read
        ConversationMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ConversationMessage::with(['sender', 'pickup'])
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => $this->formatMessage($m, $user->id));

        return response()->json($messages);
    }

    // Kirim pesan
    public function send(Request $request, $conversationId)
    {
        $request->validate(['content' => 'required|string|max:2000']);

        $conv = Conversation::findOrFail($conversationId);
        $user = Auth::user();

        if ($user->role === 'user' && $conv->user_id !== $user->id) {
            abort(403);
        }

        $message = ConversationMessage::create([
            'conversation_id' => $conversationId,
            'sender_id'       => $user->id,
            'type'            => 'text',
            'content'         => $request->content,
            'is_read'         => false,
        ]);

        $conv->update(['last_message_at' => now()]);

        broadcast(new ConversationMessageSent($message));

        return response()->json($this->formatMessage($message->fresh(['sender']), $user->id));
    }

    // Admin handle conversation (assign + kirim welcome message)
    public function handle($conversationId)
    {
        $conv  = Conversation::findOrFail($conversationId);
        $admin = Auth::user();

        if (!in_array($admin->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        // Assign admin ke conversation ini
        $conv->update([
            'assigned_admin_id' => $admin->id,
            'is_handled'        => true,
        ]);

        // Kirim welcome message otomatis
        $welcomeText = "Halo! 👋 Saya *{$admin->name}* dari tim Admin EcoDrop.\n\nSaya siap membantu kamu. Ada yang bisa saya bantu terkait setoran sampah kamu? 😊";

        $message = ConversationMessage::create([
            'conversation_id' => $conversationId,
            'sender_id'       => $admin->id,
            'type'            => 'text',
            'content'         => $welcomeText,
            'is_read'         => false,
        ]);

        $conv->update(['last_message_at' => now()]);

        broadcast(new ConversationMessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message->fresh(['sender']), $admin->id),
            'conversation' => $this->formatConversation($conv->fresh(['user', 'assignedAdmin']), $admin->id),
        ]);
    }

    // Kirim pickup card otomatis saat user buat setoran
    public function sendPickupCard($conversationId, $pickupId)
    {
        $conv   = Conversation::findOrFail($conversationId);
        $pickup = Pickup::findOrFail($pickupId);
        $user   = Auth::user();

        $message = ConversationMessage::create([
            'conversation_id' => $conversationId,
            'sender_id'       => $user->id,
            'type'            => 'pickup_card',
            'content'         => "Saya baru mengajukan setoran sampah baru! 📦",
            'pickup_id'       => $pickupId,
            'is_read'         => false,
        ]);

        $conv->update(['last_message_at' => now()]);

        broadcast(new ConversationMessageSent($message))->toOthers();

        return response()->json($this->formatMessage($message->fresh(['sender', 'pickup']), $user->id));
    }

    // Unread count
    public function unreadCount()
    {
        $user  = Auth::user();
        $count = 0;

        if ($user->role === 'user') {
            $conv = Conversation::where('user_id', $user->id)->first();
            if ($conv) {
                $count = ConversationMessage::where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->count();
            }
        } else {
            $count = ConversationMessage::whereHas('conversation')
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
        }

        return response()->json(['count' => $count]);
    }

    // ─── Helpers ─────────────────────────────────────────────
    private function formatConversation(Conversation $conv, int $myId): array
    {
        $last = $conv->lastMessage;
        $unread = ConversationMessage::where('conversation_id', $conv->id)
            ->where('sender_id', '!=', $myId)
            ->where('is_read', false)
            ->count();

        return [
            'id'             => $conv->id,
            'user_id'        => $conv->user_id,
            'user_name'      => $conv->user->name ?? '',
            'user_photo'     => $conv->user->getPhotoUrl() ?? '',
            'is_handled'     => $conv->is_handled,
            'assigned_admin' => $conv->assignedAdmin?->name,
            'last_message'   => $last?->content,
            'last_time'      => $last?->created_at->format('H:i'),
            'unread'         => $unread,
        ];
    }

    private function formatMessage(ConversationMessage $msg, int $myId): array
    {
        $data = [
            'id'              => $msg->id,
            'conversation_id' => $msg->conversation_id,
            'sender_id'       => $msg->sender_id,
            'sender_name'     => $msg->sender->name,
            'sender_photo'    => $msg->sender->getPhotoUrl(),
            'sender_role'     => $msg->sender->role,
            'type'            => $msg->type,
            'content'         => $msg->content,
            'pickup_id'       => $msg->pickup_id,
            'is_read'         => $msg->is_read,
            'is_mine'         => $msg->sender_id === $myId,
            'created_at'      => $msg->created_at->format('H:i'),
        ];

        if ($msg->type === 'pickup_card' && $msg->pickup) {
            $data['pickup'] = [
                'id'          => $msg->pickup->id,
                'type'        => $msg->pickup->type,
                'weight'      => $msg->pickup->weight,
                'status'      => $msg->pickup->status,
                'pickup_date' => \Carbon\Carbon::parse($msg->pickup->pickup_date)->format('d M Y'),
            ];
        }

        return $data;
    }
}