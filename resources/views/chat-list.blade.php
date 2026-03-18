<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat | EcoDrop</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 h-screen flex flex-col overflow-hidden">

@php
    $role = Auth::user()->role;
    $backRoute = $role === 'user' ? route('user.dashboard') : ($role === 'admin' ? route('admin.dashboard') : route('superadmin.dashboard'));
    $navColor = $role === 'user' ? 'from-green-600 to-emerald-600' : ($role === 'admin' ? 'from-blue-600 to-indigo-600' : 'from-amber-600 to-orange-600');
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
        <p class="text-white/70 text-xs">Pilih setoran untuk mulai chat</p>
    </div>
</div>

{{-- List --}}
<div class="flex-1 overflow-y-auto">
    @forelse($pickups as $cp)
        @php
            $unread = \App\Models\Message::where('pickup_id', $cp->id)
                ->where('sender_id', '!=', Auth::id())
                ->where('is_read', false)->count();
            $lastMsg = \App\Models\Message::where('pickup_id', $cp->id)->latest()->first();
        @endphp
        <a href="{{ route('chat.show', $cp->id) }}"
           class="flex items-center gap-4 px-4 py-4 border-b border-gray-100 hover:bg-gray-50 transition active:bg-gray-100">
            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100 flex-shrink-0 shadow-sm">
                <img src="{{ $role === 'user' ? 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true' : $cp->user->getPhotoUrl() }}"
                     class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 mb-0.5">
                    <p class="font-black text-gray-900 text-sm truncate">
                        {{ $role === 'user' ? 'Admin EcoDrop' : $cp->user->name }}
                    </p>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($unread > 0)
                            <span class="w-5 h-5 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center">{{ $unread }}</span>
                        @endif
                        @if($lastMsg)
                            <span class="text-xs text-gray-400">{{ $lastMsg->created_at->format('H:i') }}</span>
                        @endif
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-1">
                    @switch($cp->type)
                        @case('Plastik') 🪴 @break
                        @case('Kertas') 📄 @break
                        @case('Logam') 🔩 @break
                        @case('Kaca') 🥛 @break
                        @case('Organik') 🍂 @break
                        @case('Elektronik') ⚡ @break
                        @default 📦
                    @endswitch
                    {{ $cp->type }} · {{ $cp->weight }} Kg ·
                    @if($cp->status === 'pending')
                        <span class="text-yellow-600 font-semibold">Pending</span>
                    @elseif($cp->status === 'approved')
                        <span class="text-green-600 font-semibold">Approved</span>
                    @else
                        <span class="text-red-600 font-semibold">Rejected</span>
                    @endif
                </p>
                <p class="text-xs text-gray-400 truncate {{ $unread > 0 ? 'font-bold text-gray-700' : '' }}">
                    {{ $lastMsg ? Str::limit($lastMsg->message, 45) : 'Belum ada pesan — mulai chat sekarang' }}
                </p>
            </div>
            <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    @empty
        <div class="flex flex-col items-center justify-center h-64 text-center px-8">
            <div class="text-6xl mb-4">📭</div>
            <p class="text-gray-500 font-bold text-lg mb-1">Belum ada setoran</p>
            <p class="text-gray-400 text-sm">Buat setoran dulu untuk bisa chat dengan admin</p>
        </div>
    @endforelse
</div>

</body>
</html>