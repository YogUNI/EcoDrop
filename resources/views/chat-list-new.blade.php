<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat | EcoDrop</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 h-screen flex flex-col overflow-hidden">

@php
    $role = Auth::user()->role;
    $isUser = $role === 'user';
    $backRoute = $isUser ? route('user.dashboard') : ($role === 'admin' ? route('admin.dashboard') : route('superadmin.dashboard'));
    $navColor = $isUser ? 'from-green-600 to-emerald-600' : ($role === 'admin' ? 'from-blue-600 to-indigo-600' : 'from-amber-600 to-orange-600');
@endphp

{{-- Header --}}
<div class="bg-gradient-to-r {{ $navColor }} px-4 py-4 flex items-center gap-3 flex-shrink-0 shadow-lg">
    <a href="{{ $backRoute }}"
       class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition flex-shrink-0">
        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h1 class="text-white font-black text-lg">💬 Chat EcoDrop</h1>
        <p class="text-white/70 text-xs">{{ $isUser ? 'Chat dengan tim admin' : 'Semua percakapan user' }}</p>
    </div>
</div>

{{-- List --}}
<div class="flex-1 overflow-y-auto" id="convList">

    @if($isUser && $conversations->isEmpty())
        {{-- User belum punya conversation --}}
        <div class="flex flex-col items-center justify-center h-64 text-center px-8">
            <div class="text-6xl mb-4">💬</div>
            <p class="text-gray-700 font-black text-lg mb-2">Chat dengan Admin</p>
            <p class="text-gray-400 text-sm mb-6">Tanya-tanya dulu sebelum setor sampah!</p>
            <button id="startChatBtn"
                class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold shadow-lg">
                Mulai Chat
            </button>
        </div>
    @else
        @forelse($conversations as $conv)
            @php
                $unread = \App\Models\ConversationMessage::where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', Auth::id())
                    ->where('is_read', false)->count();
                $lastMsg = $conv->lastMessage;
                $displayName = $isUser ? 'Admin EcoDrop' : $conv->user->name;
                $displayPhoto = $isUser
                    ? 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true'
                    : $conv->user->getPhotoUrl();
            @endphp
            <a href="/conv/{{ $conv->id }}"
               class="flex items-center gap-4 px-4 py-4 border-b border-gray-100 hover:bg-gray-50 transition active:bg-gray-100">
                <div class="relative flex-shrink-0">
                    <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100 shadow-sm">
                        <img src="{{ $displayPhoto }}" class="w-full h-full object-cover">
                    </div>
                    @if(!$conv->is_handled)
                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-yellow-400 rounded-full border-2 border-white"></span>
                    @else
                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-white"></span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-0.5">
                        <p class="font-black text-gray-900 text-sm truncate">{{ $displayName }}</p>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($unread > 0)
                                <span class="w-5 h-5 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center">{{ $unread }}</span>
                            @endif
                            @if($lastMsg)
                                <span class="text-xs text-gray-400">{{ $lastMsg->created_at->format('H:i') }}</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 truncate {{ $unread > 0 ? 'font-bold text-gray-700' : '' }}">
                        {{ $lastMsg ? Str::limit($lastMsg->content, 45) : 'Belum ada pesan' }}
                    </p>
                    <p class="text-xs mt-0.5 {{ $conv->is_handled ? 'text-green-500' : 'text-yellow-500' }} font-semibold">
                        {{ $conv->is_handled ? '✓ Ditangani: ' . ($conv->assignedAdmin?->name ?? 'Admin') : '⏳ Menunggu admin' }}
                    </p>
                </div>
                <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @empty
            <div class="flex flex-col items-center justify-center h-64 text-center px-8">
                <div class="text-6xl mb-4">📭</div>
                <p class="text-gray-500 font-bold">Belum ada percakapan</p>
            </div>
        @endforelse
    @endif
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Start chat baru untuk user
const startBtn = document.getElementById('startChatBtn');
if (startBtn) {
    startBtn.addEventListener('click', async () => {
        startBtn.disabled = true;
        startBtn.textContent = 'Memuat...';
        try {
            const res = await fetch('/conversations', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await res.json();
            const convId = data.id;
            if (convId) window.location.href = `/conv/${convId}`;
        } catch(e) {
            startBtn.disabled = false;
            startBtn.textContent = 'Mulai Chat';
        }
    });
}
</script>
</body>
</html>