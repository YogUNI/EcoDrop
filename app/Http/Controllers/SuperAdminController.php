<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pickup;
use App\Models\ActivityLog;

// ✅ TAMBAHAN UNTUK FITUR EMAIL
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminVerifiedMail;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $pendingAdmins  = User::where('role', 'admin')->where('is_verified', false)->get();
        $verifiedAdmins = User::where('role', 'admin')->where('is_verified', true)->get();

        $pickups = Pickup::with('user', 'handledBy')->latest()->get();

        $activityLogs = ActivityLog::with('admin')->latest()->take(50)->get();

        // List semua user biasa
        $users = User::where('role', 'user')
                     ->withCount('pickups')
                     ->latest()
                     ->get();

        // Get all rewards for management
        $rewards = \App\Models\Reward::all();

        return view('superadmin.dashboard', compact(
            'pendingAdmins', 'verifiedAdmins',
            'pickups', 'activityLogs', 'users', 'rewards'
        ));
    }

    public function realtimeData()
    {
        $pendingAdmins  = User::where('role', 'admin')->where('is_verified', false)->get();
        $verifiedAdmins = User::where('role', 'admin')->where('is_verified', true)->get();
        $pickups        = Pickup::with('user', 'handledBy')->latest()->get();
        $activityLogs   = ActivityLog::with('admin')->latest()->take(50)->get();
        $users          = User::where('role', 'user')->withCount('pickups')->latest()->get();

        $totalPickups    = $pickups->count();
        $pendingPickups  = $pickups->where('status', 'pending')->count();
        $approvedPickups = $pickups->where('status', 'approved')->count();
        $rejectedPickups = $pickups->where('status', 'rejected')->count();
        $totalWeight     = $pickups->where('status', 'approved')->sum('weight');
        $todayStr        = \Carbon\Carbon::today()->format('Y-m-d');
        $todayWeight     = $pickups->where('status', 'approved')
            ->filter(fn($i) => \Carbon\Carbon::parse($i->pickup_date)->format('Y-m-d') === $todayStr)
            ->sum('weight');

        $wasteByType = [];
        foreach(['Plastik','Kertas','Logam','Kaca','Organik','Elektronik','Lainnya'] as $type) {
            $wasteByType[$type] = $pickups->where('type', $type)->where('status','approved')->sum('weight');
        }

        $last7Days = [];
        for($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i)->format('Y-m-d');
            $last7Days[\Carbon\Carbon::parse($date)->format('d M')] = $pickups->where('status','approved')
                ->filter(fn($item) => \Carbon\Carbon::parse($item->pickup_date)->format('Y-m-d') === $date)
                ->sum('weight');
        }

        // Format data logs
        $formattedLogs = [];
        foreach($activityLogs as $log) {
            $formattedLogs[] = [
                'type' => 'admin',
                'admin_name' => optional($log->admin)->name ?? 'Admin',
                'action' => $log->action,
                'user_name' => $log->user_name,
                'waste_type' => $log->waste_type,
                'waste_weight' => $log->waste_weight,
                'points_given' => $log->points_given,
                'date' => \Carbon\Carbon::parse($log->created_at)->format('d M Y'),
                'time' => \Carbon\Carbon::parse($log->created_at)->format('H:i'),
                'timestamp' => \Carbon\Carbon::parse($log->created_at)->timestamp
            ];
        }
        foreach($pickups as $pickup) {
            $formattedLogs[] = [
                'type' => 'user',
                'user_name' => optional($pickup->user)->name ?? 'User',
                'waste_type' => $pickup->type,
                'waste_weight' => $pickup->weight,
                'date' => \Carbon\Carbon::parse($pickup->created_at)->format('d M Y'),
                'time' => \Carbon\Carbon::parse($pickup->created_at)->format('H:i'),
                'timestamp' => \Carbon\Carbon::parse($pickup->created_at)->timestamp,
                'status' => $pickup->status
            ];
        }
        usort($formattedLogs, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        // Format pickups for direct JSON consumption
        $formattedPickups = $pickups->map(function($pickup) {
            return [
                'id' => $pickup->id,
                'user_name' => optional($pickup->user)->name ?? 'User',
                'user_email' => optional($pickup->user)->email ?? '',
                'user_photo' => optional($pickup->user)->getPhotoUrl() ?? '',
                'type' => $pickup->type,
                'weight' => $pickup->weight,
                'pickup_date' => \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y'),
                'pickup_date_raw' => \Carbon\Carbon::parse($pickup->pickup_date)->format('Y-m-d'),
                'address' => $pickup->address,
                'phone' => $pickup->phone,
                'status' => $pickup->status,
                'points' => $pickup->points_earned,
                'notes' => $pickup->notes,
                'photo' => $pickup->photo ? \Illuminate\Support\Facades\Storage::url($pickup->photo) : null,
                'latitude' => $pickup->latitude,
                'longitude' => $pickup->longitude,
                'handled_by' => optional($pickup->handledBy)->name,
                'maps_url' => $pickup->latitude ? "https://maps.google.com/?q={$pickup->latitude},{$pickup->longitude}" : null,
            ];
        });

        return response()->json([
            'totalPickups' => $totalPickups,
            'pendingPickups' => $pendingPickups,
            'approvedPickups' => $approvedPickups,
            'rejectedPickups' => $rejectedPickups,
            'totalWeight' => $totalWeight,
            'todayWeight' => $todayWeight,
            'wasteByType' => $wasteByType,
            'last7Days' => $last7Days,
            'activityLogs' => $formattedLogs,
            'pickups' => $formattedPickups
        ]);
    }

    public function verifyAdmin($id)
    {
        // ✅ PERBAIKAN: Cari adminnya dulu, simpan di variabel, baru diupdate
        $admin = User::findOrFail($id);
        $admin->update(['is_verified' => true]);

        // ✅ TAMBAHAN: Eksekusi kirim email ke alamat email si admin
        Mail::to($admin->email)->send(new AdminVerifiedMail($admin->name));

        return back()->with('success', '✅ Admin berhasil diverifikasi dan email notifikasi telah dikirim!');
    }

    public function deleteAdmin($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'Akun admin berhasil dihapus!');
    }

    public function banUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => !$user->is_banned]);
        $status = $user->is_banned ? 'dibanned' : 'diaktifkan kembali';
        return back()->with('success', "Akun user berhasil {$status}!");
    }

    // Menambah Item Voucher/Hadiah Baru
    public function storeReward(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'points_required' => 'required|integer|min:1',
            'description'     => 'nullable|string',
            'stock'           => 'nullable|integer|min:0',
            'image'           => 'nullable|image|max:3072', // max 3MB
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rewards', 'public');
        }

        \App\Models\Reward::create([
            'name'            => $request->name,
            'points_required' => $request->points_required,
            'description'     => $request->description,
            'stock'           => $request->stock ?: null, // 0 atau kosong → null (unlimited)
            'is_active'       => true,
            'image'           => $imagePath,
        ]);

        return back()->with('success', '🎉 Item hadiah baru berhasil ditambahkan ke katalog!');
    }

    // Mengedit Item Voucher/Hadiah
    public function updateReward(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'points_required' => 'required|integer|min:1',
            'description'     => 'nullable|string',
            'stock'           => 'nullable|integer|min:0',
            'image'           => 'nullable|image|max:3072' // max 3MB
        ]);

        $reward = \App\Models\Reward::findOrFail($id);

        $imagePath = $reward->image;
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($reward->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($reward->image);
            }
            $imagePath = $request->file('image')->store('rewards', 'public');
        }

        $reward->update([
            'name'            => $request->name,
            'points_required' => $request->points_required,
            'description'     => $request->description,
            'stock'           => $request->stock !== '' ? $request->stock : null,
            'image'           => $imagePath
        ]);

        return back()->with('success', '✏️ Item hadiah berhasil diperbarui!');
    }

    // Toggle aktif/nonaktif reward tanpa menghapus
    public function toggleReward($id)
    {
        $reward = \App\Models\Reward::findOrFail($id);
        $reward->update(['is_active' => !$reward->is_active]);
        $status = $reward->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "✅ Hadiah berhasil {$status}.");
    }

    // Menghapus Item Voucher/Hadiah dari Katalog
    public function deleteReward($id)
    {
        $reward = \App\Models\Reward::findOrFail($id);
        
        // Hapus foto fisik dari storage jika ada
        if ($reward->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($reward->image);
        }

        $reward->delete();

        return back()->with('success', '🗑️ Item hadiah berhasil dihapus dari katalog.');
    }
}