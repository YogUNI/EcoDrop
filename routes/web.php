<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConversationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ─── Landing Page ───────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

// ─── Auto Redirect Setelah Login (sesuai role) ──────────────
Route::get('/dashboard', function () {
    $role = Auth::user()->role;
    if ($role === 'super_admin') return redirect('/superadmin/dashboard');
    if ($role === 'admin')       return redirect('/admin/dashboard');
    return redirect('/user/dashboard');
})->middleware('auth')->name('dashboard');

// ─── USER BIASA ─────────────────────────────────────────────
Route::middleware(['auth', 'role:user'])->prefix('user')->group(function () {
    Route::get('/dashboard', [PickupController::class, 'userDashboard'])->name('user.dashboard');
    Route::post('/pickups', [PickupController::class, 'store'])->name('pickups.store');
    Route::delete('/pickups/{id}', [PickupController::class, 'destroy'])->name('pickups.destroy');
});

// ─── ADMIN ──────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// ─── SUPER ADMIN ─────────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
    Route::patch('/admins/{id}/verify', [SuperAdminController::class, 'verifyAdmin'])->name('superadmin.verify');
    Route::delete('/admins/{id}', [SuperAdminController::class, 'deleteAdmin'])->name('superadmin.delete');
    Route::patch('/users/{id}/ban', [SuperAdminController::class, 'banUser'])->name('superadmin.ban');
});

// ─── SHARED: Admin & Super Admin bisa update/delete setoran ──
Route::middleware('auth')->group(function () {
    Route::patch('/pickups/{id}', [PickupController::class, 'update'])->name('pickups.update');
    Route::delete('/pickups/{id}/admin', [PickupController::class, 'destroyAdmin'])->name('pickups.destroy.admin');
});

// ─── PROFILE ────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
});

// ─── CONVERSATIONS (Chat Shopee-style) — taruh SEBELUM chat routes ──
Route::middleware('auth')->group(function () {
    // ⚠️ unread harus PALING ATAS biar tidak dikira {id}
    Route::get('/conversations/unread/count', [ConversationController::class, 'unreadCount'])->name('conversations.unread');
    Route::get('/conversations', [ConversationController::class, 'getOrCreate'])->name('conversations.index');
    Route::get('/conversations/{id}/messages', [ConversationController::class, 'messages'])->name('conversations.messages');
    Route::post('/conversations/{id}/messages', [ConversationController::class, 'send'])->name('conversations.send');
    Route::post('/conversations/{id}/handle', [ConversationController::class, 'handle'])->name('conversations.handle');
    Route::post('/conversations/{id}/close', [ConversationController::class, 'close'])->name('conversations.close');
    Route::post('/conversations/{id}/reopen', [ConversationController::class, 'reopen'])->name('conversations.reopen');
    Route::post('/conversations/{id}/pickup-card/{pickupId}', [ConversationController::class, 'sendPickupCard'])->name('conversations.pickup-card');
});

// ─── CHAT / MESSAGES (lama — tetap ada untuk backward compat) ──
Route::middleware('auth')->group(function () {
    Route::get('/messages/unread/count', [MessageController::class, 'unreadCount'])->name('messages.unread');
    Route::get('/messages/{pickupId}', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/{pickupId}', [MessageController::class, 'store'])->name('messages.store');
});

// ─── HALAMAN CHAT (Mobile full page) ────────────────────────
Route::middleware('auth')->get('/chat/{pickupId}', function ($pickupId) {
    $pickup = \App\Models\Pickup::with('user')->findOrFail($pickupId);
    if (Auth::user()->role === 'user' && $pickup->user_id !== Auth::id()) {
        abort(403);
    }
    return view('chat', compact('pickup'));
})->name('chat.show');

// ─── CHAT LIST (Mobile) ──────────────────────────────────────
Route::middleware('auth')->get('/chat', function () {
    $role = Auth::user()->role;

    // Pakai sistem conversation baru
    if ($role === 'user') {
        $conversations = \App\Models\Conversation::where('user_id', Auth::id())
            ->with(['lastMessage.sender', 'assignedAdmin'])
            ->get();
    } else {
        $conversations = \App\Models\Conversation::with(['user', 'lastMessage', 'assignedAdmin'])
            ->orderByDesc('last_message_at')
            ->get();
    }

    return view('chat-list-new', compact('conversations'));
})->name('chat.list');

// ─── HALAMAN CHAT CONVERSATION (Mobile full page) ────────────
Route::middleware('auth')->get('/conv/{convId}', function ($convId) {
    $conv = \App\Models\Conversation::with('user', 'assignedAdmin')->findOrFail($convId);
    if (Auth::user()->role === 'user' && $conv->user_id !== Auth::id()) {
        abort(403);
    }
    return view('conv-chat', compact('conv'));
})->name('conv.chat');

require __DIR__.'/auth.php';