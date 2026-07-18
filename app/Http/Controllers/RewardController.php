<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RewardController extends Controller
{
    // Halaman katalog reward untuk user (hanya tampilkan yang aktif)
    public function userIndex()
    {
        $rewards = Reward::where('is_active', true)->get();
        $redemptions = RewardRedemption::with('reward')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('user.rewards', compact('rewards', 'redemptions'));
    }

    // Proses penukaran poin user
    public function redeem(Request $request, $id)
    {
        $reward = Reward::findOrFail($id);
        $user   = Auth::user();

        // 1. Pastikan reward masih aktif & ada stok
        if (!$reward->isAvailable()) {
            return back()->with('error', '❌ Hadiah ini sudah tidak tersedia / kehabisan stok.');
        }

        // 2. Validasi poin cukup
        if ($user->points < $reward->points_required) {
            return back()->with('error', '❌ Poin Anda tidak mencukupi untuk menukarkan hadiah ini.');
        }

        // 3. Gunakan DB Transaction + atomic update untuk mencegah race condition
        DB::transaction(function () use ($user, $reward) {
            // Kurangi poin user
            $user->decrement('points', $reward->points_required);

            // Kurangi stok (jika terbatas/bukan unlimited)
            if (!is_null($reward->stock)) {
                $reward->decrement('stock');
            }

            // Generate unique voucher code (format: ECD-XXXXXX) — bebas collision
            do {
                $uniqueCode = 'ECD-' . strtoupper(Str::random(6));
            } while (RewardRedemption::where('unique_code', $uniqueCode)->exists());

            // Buat record klaim penukaran baru
            RewardRedemption::create([
                'user_id'      => $user->id,
                'reward_id'    => $reward->id,
                'points_spent' => $reward->points_required,
                'unique_code'  => $uniqueCode,
                'status'       => 'pending',
            ]);
        });

        return back()->with('success', '🎉 Penukaran berhasil diajukan! Silakan tunjukkan kode voucher kepada petugas admin.');
    }

    // Panel Admin & Super Admin melihat semua pengajuan klaim penukaran
    public function adminIndex()
    {
        $redemptions = RewardRedemption::with(['user', 'reward'])
            ->latest()
            ->get();

        return view('admin.redemptions', compact('redemptions'));
    }

    // Aksi penyelesaian / pembatalan klaim penukaran oleh admin
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:completed,canceled'
        ]);

        $redemption = RewardRedemption::with('reward')->findOrFail($id);

        if ($redemption->status !== 'pending') {
            return back()->with('error', '⚠️ Status penukaran ini sudah tidak dapat diubah.');
        }

        DB::transaction(function () use ($redemption, $request) {
            $redemption->update(['status' => $request->status]);

            if ($request->status === 'canceled') {
                // Kembalikan poin ke user
                $redemption->user->increment('points', $redemption->points_spent);

                // Kembalikan stok reward (jika stok terbatas)
                if ($redemption->reward && !is_null($redemption->reward->stock)) {
                    $redemption->reward->increment('stock');
                }
            }
        });

        $message = $request->status === 'completed'
            ? '✅ Klaim voucher berhasil diselesaikan!'
            : '🚫 Penukaran dibatalkan, poin & stok telah dikembalikan.';

        return back()->with('success', $message);
    }
}
