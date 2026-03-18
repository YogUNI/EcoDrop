<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Pickup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Ambil semua pesan untuk 1 setoran
    public function index($pickupId)
    {
        $pickup = Pickup::with('user')->findOrFail($pickupId);

        // Pastikan user hanya bisa lihat chat setorannya sendiri
        if (Auth::user()->role === 'user' && $pickup->user_id !== Auth::id()) {
            abort(403);
        }

        $messages = Message::with('sender')
            ->where('pickup_id', $pickupId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'pickup_id'    => $m->pickup_id,
                'sender_id'    => $m->sender_id,
                'sender_name'  => $m->sender->name,
                'sender_photo' => $m->sender->getPhotoUrl(),
                'sender_role'  => $m->sender->role,
                'message'      => $m->message,
                'created_at'   => $m->created_at->format('H:i'),
                'is_mine'      => $m->sender_id === Auth::id(),
            ]);

        // Mark as read
        Message::where('pickup_id', $pickupId)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    // Kirim pesan
    public function store(Request $request, $pickupId)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $pickup = Pickup::findOrFail($pickupId);

        // Pastikan user hanya bisa kirim ke setorannya sendiri
        if (Auth::user()->role === 'user' && $pickup->user_id !== Auth::id()) {
            abort(403);
        }

        $message = Message::create([
            'pickup_id' => $pickupId,
            'sender_id' => Auth::id(),
            'message'   => $request->message,
            'is_read'   => false,
        ]);

        // Broadcast ke channel
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'id'           => $message->id,
            'pickup_id'    => $message->pickup_id,
            'sender_id'    => $message->sender_id,
            'sender_name'  => Auth::user()->name,
            'sender_photo' => Auth::user()->getPhotoUrl(),
            'sender_role'  => Auth::user()->role,
            'message'      => $message->message,
            'created_at'   => $message->created_at->format('H:i'),
            'is_mine'      => true,
        ]);
    }

    // Jumlah pesan belum dibaca (untuk notif navbar)
    public function unreadCount()
    {
        $count = Message::whereHas('pickup', function($q) {
                if (Auth::user()->role === 'user') {
                    $q->where('user_id', Auth::id());
                }
            })
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}