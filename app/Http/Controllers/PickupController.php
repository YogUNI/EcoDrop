<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\ActivityLog;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Events\ConversationMessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PickupController extends Controller
{
    public function userDashboard()
    {
        $pickups = Pickup::with(['handledBy', 'messages'])->where('user_id', Auth::id())->latest()->get();
        return view('dashboard', compact('pickups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:Plastik,Kertas,Logam,Kaca,Organik,Elektronik,Lainnya',
            'weight'      => 'required|numeric|min:0.1',
            'pickup_date' => 'required|date',
            'address'     => 'required|string|min:10',
            'phone'       => ['required', 'regex:/^(\+62|0)[0-9]{9,12}$/'],
            'photo'       => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes'       => 'nullable|string|max:500',
        ], [
            'type.required'        => 'Jenis sampah harus dipilih',
            'type.in'              => 'Jenis sampah tidak valid',
            'weight.required'      => 'Berat sampah harus diisi',
            'weight.min'           => 'Berat minimum 0.1 kg',
            'pickup_date.required' => 'Tanggal penjemputan harus dipilih',
            'address.required'     => 'Alamat penjemputan harus diisi',
            'address.min'          => 'Alamat minimal 10 karakter',
            'phone.required'       => 'Nomor telepon harus diisi',
            'phone.regex'          => 'Format nomor telepon tidak valid (contoh: 0812345678)',
            'photo.required'       => 'Foto sampah wajib diupload',
            'photo.image'          => 'File harus berupa gambar',
            'photo.mimes'          => 'Format foto harus jpg, jpeg, png, atau webp',
            'photo.max'            => 'Ukuran foto maksimal 5MB',
        ]);

        // Upload foto sampah
        $photoPath = $request->file('photo')->store('pickup-photos', 'public');

        $pickup = Pickup::create([
            'user_id'     => Auth::id(),
            'type'        => $request->type,
            'weight'      => $request->weight,
            'pickup_date' => $request->pickup_date,
            'address'     => $request->address,
            'phone'       => $request->phone,
            'status'      => 'pending',
            'latitude'    => $request->latitude ?: null,
            'longitude'   => $request->longitude ?: null,
            'photo'       => $photoPath,
            'notes'       => $request->notes ?: null,
        ]);

        // ─── Auto kirim pickup card ke conversation ──────────
        $conversation = Conversation::firstOrCreate(
            ['user_id' => Auth::id()],
            ['is_handled' => false]
        );

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => Auth::id(),
            'type'            => 'pickup_card',
            'content'         => "⏳ Saya mengajukan setoran sampah baru!",
            'pickup_id'       => $pickup->id,
            'is_read'         => false,
        ]);

        $conversation->update(['last_message_at' => now()]);
        broadcast(new ConversationMessageSent($message->load('sender', 'pickup')));
        // ─────────────────────────────────────────────────────

        return redirect()->route('user.dashboard')
            ->with('success', '✨ Setoran sampah berhasil diajukan! Menunggu verifikasi admin.');
    }

    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $pickup = Pickup::with('user')->findOrFail($id);
        $pickup->status     = $request->status;
        $pickup->handled_by = Auth::id();

        $pointsGiven = null;

        if ($request->status === 'approved') {
            $request->validate(['points_earned' => 'required|numeric|min:1']);
            $pickup->points_earned = $request->points_earned;
            $pickup->user->increment('points', $request->points_earned);
            $pointsGiven = $request->points_earned;
        }

        $pickup->save();

        ActivityLog::create([
            'admin_id'     => Auth::id(),
            'action'       => $request->status,
            'pickup_id'    => $pickup->id,
            'user_name'    => $pickup->user->name,
            'waste_type'   => $pickup->type,
            'waste_weight' => $pickup->weight,
            'points_given' => $pointsGiven,
        ]);

        // ─── Kirim system message ke conversation ─────────────
        $conversation = Conversation::where('user_id', $pickup->user_id)->first();
        if ($conversation) {
            $statusText = $request->status === 'approved'
                ? "✅ Setoran #{$pickup->id} ({$pickup->type} · {$pickup->weight} Kg) telah disetujui! Kamu mendapat +{$pointsGiven} poin 🏆"
                : "❌ Setoran #{$pickup->id} ({$pickup->type} · {$pickup->weight} Kg) telah ditolak.";

            $sysMsg = ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => Auth::id(),
                'type'            => 'system',
                'content'         => $statusText,
                'pickup_id'       => $pickup->id,
                'is_read'         => false,
            ]);

            $conversation->update(['last_message_at' => now()]);
            broadcast(new ConversationMessageSent($sysMsg->load('sender', 'pickup')));
        }
        // ──────────────────────────────────────────────────────

        return back()->with('success', 'Status setoran berhasil diupdate!');
    }

    public function destroy($id)
    {
        $pickup = Pickup::with('user')->findOrFail($id);

        if (Auth::id() === $pickup->user_id && $pickup->status === 'pending') {
            // Hapus foto jika ada
            if ($pickup->photo) {
                Storage::disk('public')->delete($pickup->photo);
            }
            $pickup->delete();
            return back()->with('success', 'Setoran berhasil dibatalkan.');
        }

        return back()->withErrors(['error' => 'Kamu hanya bisa membatalkan setoran milikmu yang masih pending.']);
    }

    public function destroyAdmin($id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403);
        }

        $pickup = Pickup::with('user')->findOrFail($id);

        // Hapus foto jika ada
        if ($pickup->photo) {
            Storage::disk('public')->delete($pickup->photo);
        }

        ActivityLog::create([
            'admin_id'     => Auth::id(),
            'action'       => 'deleted',
            'pickup_id'    => null,
            'user_name'    => $pickup->user->name,
            'waste_type'   => $pickup->type,
            'waste_weight' => $pickup->weight,
            'points_given' => null,
        ]);

        $pickup->delete();
        return back()->with('success', 'Data setoran berhasil dihapus.');
    }

    // Helper: get photo URL
    public function getPhotoUrl(Pickup $pickup): ?string
    {
        if (!$pickup->photo) return null;
        return Storage::disk('public')->url($pickup->photo);
    }
}