<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pickup;
use Illuminate\Support\Facades\Auth;

class PickupController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input di sisi Server (Syarat Dosen ✅)
        $request->validate([
            'type' => 'required|string',
            'weight' => 'required|numeric|min:0.5', // Minimal 0.5 kg
            'pickup_date' => 'required|date|after_or_equal:today', // Gak boleh tanggal kemaren
        ]);

        // 2. Simpan ke database (CRUD: Create ✅)
        Pickup::create([
            'user_id' => Auth::id(),
            'type' => $request->type,
            'weight' => $request->weight,
            'pickup_date' => $request->pickup_date,
            'status' => 'pending', // Status awal selalu pending
            'points_earned' => 0, 
        ]);

        // 3. Kembali ke dashboard bawa pesan sukses
        return back()->with('success', 'Mantap! Permintaan jemput sampah berhasil dikirim.');
    }

    public function update(Request $request, $id)
    {
        // Cari data setor sampah yang mau diupdate
        $pickup = Pickup::findOrFail($id);

        // Update statusnya
        $pickup->status = $request->status;

        // Kalau Admin nyetujuin, tambahin poin ke user
        if ($request->status === 'approved') {
            // Validasi biar poinnya diisi angka
            $request->validate(['points_earned' => 'required|numeric|min:1']);
            
            $pickup->points_earned = $request->points_earned;
            
            // Tambahin poin ke saldo si User (Relasi antar tabel beraksi!)
            $pickup->user->increment('points', $request->points_earned);
        }

        $pickup->save();

        return back()->with('success', 'Status setoran berhasil diupdate!');
    }

    public function destroy($id)
    {
        $pickup = Pickup::findOrFail($id);

        // LOGIKA KEAMANAN SISTEM:
        // Cek apakah yang login itu Admin? ATAU User yang punya data & statusnya masih pending?
        if (Auth::user()->role === 'admin' || (Auth::id() === $pickup->user_id && $pickup->status === 'pending')) {
            $pickup->delete();
            return back()->with('success', 'Data setoran berhasil dihapus/dibatalkan.');
        }

        // Kalau user iseng nyoba hapus data yang udah di-acc atau punya orang lain
        return back()->withErrors(['Ditolak: Lu cuma bisa narik data lu sendiri yang masih pending.']);
    }
}