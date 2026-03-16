<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PickupController;
use Illuminate\Support\Facades\Auth; // 👉 INI OBAT GARIS MERAHNYA (Import Auth)

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // Karena Auth udah di-import di atas, sekarang garis merahnya pasti minggat!
    if (Auth::user()->role === 'admin') {
        // ADMIN: Ambil semua data dari semua user, bawa nama usernya juga
        $pickups = \App\Models\Pickup::with('user')->latest()->get();
    } else {
        // USER BIASA: Ambil punya dia sendiri aja
        $pickups = \App\Models\Pickup::where('user_id', Auth::id())->latest()->get();
    }
    
    return view('dashboard', compact('pickups'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Rute buat nambah setoran (Create)
    Route::post('/pickups', [PickupController::class, 'store'])->name('pickups.store');
    
    // 👉 INI RUTE BARU BUAT ADMIN NGE-ACC / NOLAK SETORAN (Update)
    Route::patch('/pickups/{id}', [PickupController::class, 'update'])->name('pickups.update');
    // Rute buat Hapus Data (Delete)
    Route::delete('/pickups/{id}', [PickupController::class, 'destroy'])->name('pickups.destroy');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';