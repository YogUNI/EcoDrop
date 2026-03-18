<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'EcoDrop') }} - Platform Manajemen Sampah</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-blue-50">
            @include('layouts.navigation')
            <main class="min-h-[calc(100vh-200px)]">
                {{ $slot }}
            </main>
            <footer class="bg-white/50 backdrop-blur-sm border-t border-green-100 mt-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-600 rounded-lg flex items-center justify-center text-lg">🌱</div>
                                <h3 class="font-black text-gray-900">EcoDrop</h3>
                            </div>
                            <p class="text-sm text-gray-600">Platform manajemen sampah berbasis reward untuk gaya hidup yang lebih hijau.</p>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Quick Links</h4>
                            <ul class="space-y-2 text-sm">
                                @auth
                                    @if(Auth::user()->role === 'user')
                                        <li><a href="{{ route('user.dashboard') }}" class="text-gray-600 hover:text-green-600 transition">Dashboard</a></li>
                                    @elseif(Auth::user()->role === 'admin')
                                        <li><a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-blue-600 transition">Dashboard</a></li>
                                    @else
                                        <li><a href="{{ route('superadmin.dashboard') }}" class="text-gray-600 hover:text-amber-600 transition">Dashboard</a></li>
                                    @endif
                                    <li><a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-green-600 transition">Profile</a></li>
                                @endauth
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Info</h4>
                            <p class="text-sm text-gray-600">© 2026 EcoDrop. Semua hak dilindungi.</p>
                            <p class="text-xs text-gray-500 mt-2">Dibuat dengan ❤️ untuk planet yang lebih baik</p>
                        </div>
                    </div>
                    <div class="border-t border-green-100 mt-8 pt-8 text-center text-xs text-gray-500">
                        <p>EcoDrop v1.0 | Pemrograman Web Project</p>
                    </div>
                </div>
            </footer>
        </div>

        {{-- ═══ CHAT WIDGET ═══ --}}
        @auth
        @php
            $authRole    = Auth::user()->role;
            $authUserId  = Auth::id();
            $isUser      = $authRole === 'user';
            $isAdminRole = in_array($authRole, ['admin', 'super_admin']);

            if ($isUser) {
                $conv = \App\Models\Conversation::where('user_id', $authUserId)
                    ->with(['lastMessage.sender', 'assignedAdmin'])
                    ->first();
                $widgetConversations = $conv ? collect([$conv]) : collect();
            } else {
                if ($authRole === 'super_admin') {
                    $widgetConversations = \App\Models\Conversation::with(['user', 'lastMessage', 'assignedAdmin'])
                        ->orderByDesc('updated_at')
                        ->take(30)
                        ->get()
                        ->filter(fn($c) => $c->user !== null);
                } else {
                    $widgetConversations = \App\Models\Conversation::with(['user', 'lastMessage', 'assignedAdmin'])
                        ->where(function($q) use ($authUserId) {
                            $q->where('is_handled', false)
                              ->orWhere('assigned_admin_id', $authUserId);
                        })
                        ->orderByDesc('updated_at')
                        ->take(30)
                        ->get()
                        ->filter(fn($c) => $c->user !== null);
                }
            }
        @endphp

        <div x-data="chatWidget()"
             x-init="fetchUnread(); setInterval(() => fetchUnread(), 15000);">

            {{-- DESKTOP --}}
            <div class="hidden md:block">
                <div class="fixed bottom-6 right-6 z-[900]">
                    <div x-show="unreadTotal > 0 && !isOpen"
                         class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center z-10 shadow-lg animate-pulse">
                        <span x-text="unreadTotal > 9 ? '9+' : unreadTotal"></span>
                    </div>
                    <button @click="isOpen = !isOpen"
                        class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-2xl shadow-2xl shadow-green-200/50 flex items-center justify-center transition duration-300 hover:scale-110">
                        <svg x-show="!isOpen" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <svg x-show="isOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="isOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0 transform translate-y-4 scale-95"
                     class="fixed bottom-24 right-6 z-[899] w-96 bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col"
                     style="height: 580px; display: none;">

                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-5 py-4 flex-shrink-0">
                        <div x-show="!currentConvId">
                            <h3 class="text-white font-black text-base">💬 Chat EcoDrop</h3>
                            <p class="text-green-100 text-xs mt-0.5">
                                {{ $isUser ? 'Chat dengan tim admin kami' : 'Semua percakapan user' }}
                            </p>
                        </div>
                        <div x-show="currentConvId" class="flex items-center gap-3">
                            <button @click="closeConv()"
                                class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-white/50 flex-shrink-0">
                                <img :src="currentConv ? currentConv.photo : ''"
                                     x-on:error="$el.src='https://ui-avatars.com/api/?name='+(currentConv ? encodeURIComponent(currentConv.name) : 'Admin')+'&background=10b981&color=fff&bold=true'"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-black text-sm truncate" x-text="currentConv ? currentConv.name : ''"></p>
                                {{-- Status: closed --}}
                                <p x-show="currentConv && currentConv.is_closed" class="text-red-200 text-xs">🔒 Sesi telah diakhiri</p>
                                {{-- Status: handled --}}
                                <div x-show="currentConv && currentConv.is_handled && !currentConv.is_closed" class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-green-300 rounded-full animate-pulse"></span>
                                    <p class="text-green-100 text-xs" x-text="'Ditangani: ' + (currentConv ? currentConv.assigned_admin || 'Admin' : '')"></p>
                                </div>
                                {{-- Status: waiting --}}
                                <p x-show="currentConv && !currentConv.is_handled && !currentConv.is_closed" class="text-yellow-200 text-xs">⏳ Menunggu admin</p>
                            </div>
                            {{-- Tombol Akhiri Layanan (hanya admin, hanya kalau sudah handled dan belum closed) --}}
                            @if($isAdminRole)
                            <button x-show="currentConv && currentConv.is_handled && !currentConv.is_closed"
                                @click="endService()"
                                :disabled="ending"
                                class="flex-shrink-0 px-2.5 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold text-xs transition disabled:opacity-50 flex items-center gap-1">
                                <span x-show="!ending">🔒 Akhiri</span>
                                <span x-show="ending">...</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Conversation List --}}
                    <div x-show="!currentConvId" class="flex-1 overflow-y-auto">
                        @if($isUser)
                            @if($widgetConversations->count() > 0)
                                @php
                                    $cv = $widgetConversations->first();
                                    $unread = \App\Models\ConversationMessage::where('conversation_id', $cv->id)
                                        ->where('sender_id', '!=', $authUserId)
                                        ->where('is_read', false)->count();
                                    $lastCvMsg = $cv->lastMessage;
                                @endphp
                                <button @click="openConv({{ $cv->id }}, {
                                    name: 'Admin EcoDrop',
                                    photo: 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true',
                                    is_handled: {{ $cv->is_handled ? 'true' : 'false' }},
                                    is_closed: {{ $cv->is_closed ? 'true' : 'false' }},
                                    assigned_admin: '{{ $cv->assignedAdmin?->name ?? '' }}'
                                })"
                                    class="w-full flex items-center gap-3 p-4 hover:bg-gray-50 transition text-left border-b border-gray-50">
                                    <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100 flex-shrink-0">
                                        <img src="https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                            <p class="font-black text-gray-900 text-sm">Admin EcoDrop</p>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                @if($unread > 0)
                                                    <span class="w-5 h-5 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center">{{ $unread }}</span>
                                                @endif
                                                @if($lastCvMsg)
                                                    <span class="text-xs text-gray-400">{{ $lastCvMsg->created_at->format('H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-400 truncate">
                                            {{ $lastCvMsg ? Str::limit($lastCvMsg->content, 40) : 'Mulai chat dengan admin!' }}
                                        </p>
                                        @if($cv->is_closed)
                                            <p class="text-xs text-red-400 font-semibold mt-0.5">🔒 Sesi diakhiri</p>
                                        @endif
                                    </div>
                                </button>
                            @else
                                <div class="p-4">
                                    <button @click="startNewChat()"
                                        class="w-full py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-sm flex items-center justify-center gap-2 hover:from-green-600 hover:to-emerald-700 transition">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        Chat dengan Admin
                                    </button>
                                    <p class="text-xs text-gray-400 text-center mt-3">Tanya-tanya dulu sebelum setor sampah!</p>
                                </div>
                            @endif
                        @else
                            @forelse($widgetConversations as $cv)
                                @if(!$cv->user) @continue @endif
                                @php
                                    $unread = \App\Models\ConversationMessage::where('conversation_id', $cv->id)
                                        ->where('sender_id', '!=', $authUserId)
                                        ->where('is_read', false)->count();
                                    $lastCvMsg = $cv->lastMessage;
                                @endphp
                                <button @click="openConv({{ $cv->id }}, {
                                    name: '{{ addslashes($cv->user->name) }}',
                                    photo: '{{ $cv->user->getPhotoUrl() }}',
                                    is_handled: {{ $cv->is_handled ? 'true' : 'false' }},
                                    is_closed: {{ $cv->is_closed ? 'true' : 'false' }},
                                    assigned_admin: '{{ addslashes($cv->assignedAdmin?->name ?? '') }}'
                                })"
                                    class="w-full flex items-center gap-3 p-4 hover:bg-gray-50 transition text-left border-b border-gray-50">
                                    <div class="relative flex-shrink-0">
                                        <div class="w-11 h-11 rounded-full overflow-hidden border-2 border-gray-100">
                                            <img src="{{ $cv->user->getPhotoUrl() }}"
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($cv->user->name) }}&background=6366f1&color=fff&bold=true'"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        @if($cv->is_closed)
                                            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-red-400 rounded-full border-2 border-white"></span>
                                        @elseif(!$cv->is_handled)
                                            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-yellow-400 rounded-full border-2 border-white"></span>
                                        @else
                                            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-white"></span>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                            <p class="font-black text-gray-900 text-sm truncate">{{ $cv->user->name }}</p>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                @if($unread > 0)
                                                    <span class="w-5 h-5 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center">{{ $unread }}</span>
                                                @endif
                                                @if($lastCvMsg)
                                                    <span class="text-xs text-gray-400">{{ $lastCvMsg->created_at->format('H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-400 truncate">
                                            {{ $lastCvMsg ? Str::limit($lastCvMsg->content, 38) : 'Belum ada pesan' }}
                                        </p>
                                        <p class="text-xs mt-0.5 font-semibold
                                            {{ $cv->is_closed ? 'text-red-400' : ($cv->is_handled ? 'text-green-500' : 'text-yellow-500') }}">
                                            {{ $cv->is_closed ? '🔒 Sesi diakhiri' : ($cv->is_handled ? '✓ Ditangani: ' . ($cv->assignedAdmin?->name ?? 'Admin') : '⏳ Belum ditangani') }}
                                        </p>
                                    </div>
                                </button>
                            @empty
                                <div class="text-center py-12">
                                    <div class="text-4xl mb-3">💬</div>
                                    <p class="text-gray-400 text-sm font-medium">Belum ada percakapan</p>
                                </div>
                            @endforelse
                        @endif
                    </div>

                    {{-- Messages Area --}}
                    <div x-show="currentConvId" class="flex-1 overflow-y-auto p-4 space-y-3" id="chatMessages" style="display:none;">

                        {{-- Banner: belum ditangani (admin) --}}
                        @if($isAdminRole)
                        <div x-show="currentConv && !currentConv.is_handled && !currentConv.is_closed"
                            class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center">
                            <p class="text-sm font-bold text-yellow-800 mb-3">⏳ Percakapan ini belum ditangani</p>
                            <button @click="handleConv()" :disabled="handling"
                                class="px-5 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold text-sm hover:from-green-600 hover:to-emerald-700 transition disabled:opacity-50">
                                <span x-show="!handling">🙋 Tangani Percakapan Ini</span>
                                <span x-show="handling">Memproses...</span>
                            </button>
                        </div>
                        @endif

                        {{-- Banner: sesi sudah diakhiri (user) --}}
                        @if($isUser)
                        <div x-show="currentConv && currentConv.is_closed"
                            class="bg-red-50 border border-red-200 rounded-2xl p-4 text-center">
                            <p class="text-sm font-bold text-red-700 mb-1">🔒 Sesi layanan telah diakhiri</p>
                            <p class="text-xs text-red-500">Kirim pesan baru untuk membuka sesi chat baru</p>
                        </div>
                        @endif

                        <div x-show="loading" class="flex justify-center py-8">
                            <div class="flex gap-1">
                                <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay:.1s"></div>
                                <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay:.2s"></div>
                            </div>
                        </div>

                        <div x-show="!loading && messages.length === 0" class="text-center py-8">
                            <div class="text-3xl mb-2">👋</div>
                            <p class="text-gray-400 text-sm">Mulai percakapan!</p>
                        </div>

                        <template x-for="msg in messages" :key="msg.id">
                            <div>
                                {{-- System --}}
                                <div x-show="msg.type === 'system'" class="flex justify-center my-2">
                                    <div class="bg-blue-50 border border-blue-100 text-blue-700 text-xs font-semibold px-4 py-2 rounded-full max-w-[90%] text-center" x-text="msg.content"></div>
                                </div>
                                {{-- Pickup card --}}
                                <div x-show="msg.type === 'pickup_card'"
                                     :class="msg.is_mine ? 'flex flex-row-reverse items-end gap-2' : 'flex items-end gap-2'">
                                    <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 border border-gray-100">
                                        <img :src="msg.sender_photo" class="w-full h-full object-cover">
                                    </div>
                                    <div class="max-w-[80%]">
                                        <div class="bg-white border-2 border-green-200 rounded-2xl overflow-hidden shadow-sm">
                                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-3 py-2">
                                                <p class="text-white text-xs font-black">📦 Setoran Sampah Baru</p>
                                            </div>
                                            <div class="p-3" x-show="msg.pickup">
                                                <p class="font-black text-gray-900 text-sm" x-text="msg.pickup ? msg.pickup.type + ' · ' + msg.pickup.weight + ' Kg' : ''"></p>
                                                <p class="text-xs text-gray-500 mt-1" x-text="msg.pickup ? '📅 ' + msg.pickup.pickup_date : ''"></p>
                                                <span :class="{
                                                    'bg-yellow-100 text-yellow-700': msg.pickup && msg.pickup.status === 'pending',
                                                    'bg-green-100 text-green-700': msg.pickup && msg.pickup.status === 'approved',
                                                    'bg-red-100 text-red-700': msg.pickup && msg.pickup.status === 'rejected'
                                                }" class="inline-block mt-2 text-xs font-bold px-2 py-0.5 rounded-full"
                                                x-text="msg.pickup ? (msg.pickup.status === 'pending' ? '⏳ Pending' : msg.pickup.status === 'approved' ? '✅ Approved' : '❌ Rejected') : ''">
                                                </span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-400 px-1 mt-0.5" :class="msg.is_mine ? 'text-right' : 'text-left'" x-text="msg.created_at"></p>
                                    </div>
                                </div>
                                {{-- Text --}}
                                <div x-show="msg.type === 'text'"
                                     :class="msg.is_mine ? 'flex flex-row-reverse items-end gap-2' : 'flex items-end gap-2'">
                                    <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 border border-gray-100 shadow-sm">
                                        <img :src="msg.sender_photo" class="w-full h-full object-cover">
                                    </div>
                                    <div :class="msg.is_mine ? 'items-end' : 'items-start'" class="max-w-[75%] flex flex-col gap-0.5">
                                        <p x-show="!msg.is_mine" class="text-xs text-gray-400 px-1 font-semibold"
                                           x-text="msg.sender_role === 'user' ? msg.sender_name : 'Admin EcoDrop'"></p>
                                        <div :class="msg.is_mine
                                            ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-2xl rounded-br-sm'
                                            : 'bg-gray-100 text-gray-800 rounded-2xl rounded-bl-sm'"
                                             class="px-3 py-2 shadow-sm">
                                            <p class="text-sm leading-relaxed break-words whitespace-pre-wrap" x-text="msg.content"></p>
                                        </div>
                                        <p class="text-xs text-gray-400 px-1" :class="msg.is_mine ? 'text-right' : 'text-left'" x-text="msg.created_at"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Input --}}
                    <div x-show="currentConvId" class="border-t border-gray-100 p-3 flex-shrink-0 bg-white" style="display:none;">
                        <div class="flex items-end gap-2">
                            <textarea x-model="newMessage"
                                @keydown.enter.prevent="!$event.shiftKey && sendMsg()"
                                placeholder="Tulis pesan..."
                                rows="1"
                                class="flex-1 px-3 py-2.5 bg-gray-50 border border-gray-200 focus:border-green-400 focus:bg-white rounded-xl text-sm resize-none focus:outline-none transition"
                                style="max-height:80px;"
                                @input="$el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight,80)+'px'">
                            </textarea>
                            <button @click="sendMsg()" :disabled="sending || !newMessage.trim()"
                                class="w-9 h-9 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 disabled:opacity-40 text-white rounded-xl flex items-center justify-center transition hover:scale-105 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MOBILE --}}
            <div class="md:hidden fixed bottom-6 right-6 z-[900]">
                <div x-show="unreadTotal > 0"
                     class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center z-10 shadow-lg">
                    <span x-text="unreadTotal > 9 ? '9+' : unreadTotal"></span>
                </div>
                <a href="{{ route('chat.list') }}"
                   class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-2xl shadow-2xl flex items-center justify-center transition hover:scale-110">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </a>
            </div>
        </div>

        <script>
        const chatMyId      = {{ Auth::id() }};
        const chatCsrf      = document.querySelector('meta[name="csrf-token"]')?.content;
        const chatIsUser    = {{ $isUser ? 'true' : 'false' }};
        const chatIsAdmin   = {{ $isAdminRole ? 'true' : 'false' }};
        @verbatim
        document.addEventListener('alpine:init', () => {
            Alpine.data('chatWidget', () => ({
                isOpen: false,
                currentConvId: null,
                currentConv: null,
                messages: [],
                newMessage: '',
                loading: false,
                sending: false,
                handling: false,
                ending: false,
                unreadTotal: 0,

                async fetchUnread() {
                    try {
                        const res = await fetch('/conversations/unread/count', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                        });
                        const data = await res.json();
                        this.unreadTotal = data.count || 0;
                    } catch(e) {}
                },

                async startNewChat() {
                    try {
                        const res = await fetch('/conversations', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                        });
                        const data = await res.json();
                        const convId = Array.isArray(data) ? null : data.id;
                        if (convId) {
                            this.openConv(convId, {
                                name: 'Admin EcoDrop',
                                photo: 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true',
                                is_handled: data.is_handled,
                                is_closed: data.is_closed,
                                assigned_admin: data.assigned_admin
                            });
                        }
                    } catch(e) {}
                },

                async openConv(convId, convInfo) {
                    this.currentConvId = convId;
                    this.currentConv   = convInfo;
                    this.messages      = [];
                    this.loading       = true;

                    try {
                        const res = await fetch(`/conversations/${convId}/messages`, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                        });
                        this.messages = await res.json();
                    } catch(e) {}

                    this.loading = false;
                    await this.$nextTick();
                    this.scrollBottom();

                    if (typeof Echo !== 'undefined') {
                        Echo.channel(`conversation.${convId}`)
                            .listen('.message.sent', (data) => {
                                if (data.sender_id !== chatMyId) {
                                    this.messages.push(data);
                                    this.$nextTick(() => this.scrollBottom());
                                    this.unreadTotal = Math.max(0, this.unreadTotal - 1);
                                }
                                // Update is_closed jika ada system message akhiri layanan
                                if (data.type === 'system' && data.content && data.content.includes('diakhiri')) {
                                    if (this.currentConv) this.currentConv.is_closed = true;
                                }
                                // Update is_closed = false jika reopen
                                if (data.type === 'system' && data.content && data.content.includes('sesi chat baru')) {
                                    if (this.currentConv) {
                                        this.currentConv.is_closed = false;
                                        this.currentConv.is_handled = false;
                                    }
                                }
                            });
                    }
                },

                closeConv() {
                    if (typeof Echo !== 'undefined' && this.currentConvId) {
                        Echo.leaveChannel(`conversation.${this.currentConvId}`);
                    }
                    this.currentConvId = null;
                    this.currentConv   = null;
                    this.messages      = [];
                },

                async handleConv() {
                    if (this.handling) return;
                    this.handling = true;
                    try {
                        const res = await fetch(`/conversations/${this.currentConvId}/handle`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': chatCsrf
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.currentConv.is_handled     = true;
                            this.currentConv.is_closed      = false;
                            this.currentConv.assigned_admin = data.conversation.assigned_admin;
                            this.messages.push(data.message);
                            this.$nextTick(() => this.scrollBottom());
                        }
                    } catch(e) {}
                    this.handling = false;
                },

                async endService() {
                    if (this.ending) return;
                    if (!confirm('Yakin ingin mengakhiri sesi layanan ini?')) return;
                    this.ending = true;
                    try {
                        const res = await fetch(`/conversations/${this.currentConvId}/close`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': chatCsrf
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.currentConv.is_closed = true;
                            this.messages.push(data.message);
                            this.$nextTick(() => this.scrollBottom());
                        }
                    } catch(e) {}
                    this.ending = false;
                },

                async sendMsg() {
                    if (!this.newMessage.trim() || this.sending) return;
                    this.sending = true;
                    const msg = this.newMessage.trim();
                    this.newMessage = '';
                    try {
                        const res = await fetch(`/conversations/${this.currentConvId}/messages`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': chatCsrf
                            },
                            body: JSON.stringify({ content: msg })
                        });
                        const data = await res.json();
                        this.messages.push(data);
                        // Kalau user kirim pesan ke closed conv → otomatis reopen
                        if (chatIsUser && this.currentConv && this.currentConv.is_closed) {
                            this.currentConv.is_closed   = false;
                            this.currentConv.is_handled  = false;
                        }
                        this.$nextTick(() => this.scrollBottom());
                    } catch(e) {
                        this.newMessage = msg;
                    }
                    this.sending = false;
                },

                scrollBottom() {
                    const el = document.getElementById('chatMessages');
                    el && (el.scrollTop = el.scrollHeight);
                }
            }));
        });
        @endverbatim
        </script>
        @endauth
    </body>
</html>