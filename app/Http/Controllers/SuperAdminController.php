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

        return view('superadmin.dashboard', compact(
            'pendingAdmins', 'verifiedAdmins',
            'pickups', 'activityLogs', 'users'
        ));
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
}