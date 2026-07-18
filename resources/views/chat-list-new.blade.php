<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat | EcoDrop</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, .18), transparent 34%),
                radial-gradient(circle at bottom right, rgba(20, 184, 166, .14), transparent 30%),
                #eef7f0;
        }
        .chat-scroll::-webkit-scrollbar { width: 7px; }
        .chat-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .75);
            border-radius: 999px;
        }
        .conversation-card {
            background: rgba(255, 255, 255, .88);
            border: 1px solid rgba(226, 232, 240, .92);
            box-shadow: 0 14px 34px rgba(15, 23, 42, .07);
            backdrop-filter: blur(14px);
            display: block;
            transition: transform .15s, box-shadow .15s;
        }
        .conversation-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 42px rgba(15, 23, 42, .10);
        }
        .unread-pulse {
            animation: pulse-ring 1.5s cubic-bezier(.215,.61,.355,1) infinite;
        }
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(239,68,68,.5); }
            70%  { box-shadow: 0 0 0 6px rgba(239,68,68,0); }
            100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
        }
    </style>
</head>

@php
    $role      = Auth::user()->role;
    $isUser    = $role === 'user';
    $myId      = Auth::id();
    $backRoute = $isUser
        ? route('user.dashboard')
        : ($role === 'admin' ? route('admin.dashboard') : route('superadmin.dashboard'));
    $navColor  = $isUser
        ? 'from-green-600 to-emerald-600'
        : ($role === 'admin' ? 'from-blue-600 to-indigo-600' : 'from-amber-600 to-orange-600');

    $initialConvs = $conversations->map(function ($c) use ($myId) {
        $last   = $c->lastMessage;
        $unread = \App\Models\ConversationMessage::where('conversation_id', $c->id)
            ->where('sender_id', '!=', $myId)
            ->where('is_read', false)
            ->count();
        return [
            'id'             => $c->id,
            'user_id'        => $c->user_id,
            'user_name'      => $c->user?->name ?? '',
            'user_photo'     => $c->user?->getPhotoUrl() ?? '',
            'is_handled'     => $c->is_handled,
            'is_closed'      => $c->is_closed,
            'assigned_admin' => $c->assignedAdmin?->name,
            'last_message'   => $last?->content,
            'last_time'      => $last?->created_at?->format('H:i'),
            'unread'         => $unread,
        ];
    })->values()->toArray();
@endphp

{{-- ═══ Alpine.js component defined OUTSIDE x-data to avoid HTML escaping bugs ═══ --}}
<script>
    // Runs before Alpine initialises (deferred via Alpine.data registry)
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatList', () => ({
            isUser:       {{ $isUser ? 'true' : 'false' }},
            myId:         {{ $myId }},
            myRole:       '{{ $role }}',
            conversations: @js($initialConvs),
            startingChat: false,
            wsConnected:  false,
            pollTimer:    null,

            async refresh() {
                try {
                    const res  = await fetch('/conversations', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (Array.isArray(data)) {
                        this.conversations = data;
                    } else if (data && typeof data === 'object' && data.id !== undefined) {
                        this.conversations = [data];
                    }
                } catch (e) { /* silent */ }
            },

            startPolling() {
                if (this.pollTimer) return;
                this.pollTimer = setInterval(() => this.refresh(), 2000);
            },

            stopPolling() {
                if (this.pollTimer) { clearInterval(this.pollTimer); this.pollTimer = null; }
            },

            async startChat() {
                if (this.startingChat) return;
                this.startingChat = true;
                try {
                    const csrf = document.querySelector("meta[name='csrf-token']").content;
                    const res  = await fetch('/conversations', {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    const data = await res.json();
                    if (data && data.id) window.location.href = '/conv/' + data.id;
                } catch (e) {
                    this.startingChat = false;
                }
            },

            init() {
                this.startPolling(); // safety-net

                if (typeof Echo !== 'undefined') {
                    if (this.isUser) {
                        Echo.channel('user-updates.' + this.myId)
                            .listen('.conversation.updated', () => this.refresh());
                    } else {
                        Echo.channel('chat-admin-updates')
                            .listen('.conversation.updated', () => this.refresh());
                    }
                }

                document.addEventListener('echo:connected', () => {
                    this.wsConnected = true;
                    this.stopPolling();
                    this.refresh();
                });
                document.addEventListener('echo:disconnected', () => {
                    this.wsConnected = false;
                    this.startPolling();
                });
                if (window.__echoConnected === true) {
                    this.wsConnected = true;
                    this.stopPolling();
                }
            }
        }));
    });
</script>

<body class="bg-[#eef7f0] h-screen flex flex-col overflow-hidden" x-data="chatList()">

{{-- ═══ HEADER ═══ --}}
<div class="relative overflow-hidden bg-gradient-to-br {{ $navColor }} px-4 pt-5 pb-6 flex-shrink-0 shadow-xl">
    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="absolute left-8 bottom-0 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

    <div class="relative flex items-center gap-3">
        {{-- Back button --}}
        <a href="{{ $backRoute }}"
           class="w-10 h-10 flex-shrink-0 bg-white/18 hover:bg-white/28 rounded-2xl flex items-center justify-center transition border border-white/20"
           aria-label="Kembali">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>

        {{-- Title --}}
        <div class="flex-1 min-w-0">
            <p class="text-white/75 text-xs font-bold uppercase tracking-[0.18em]">Support Center</p>
            <h1 class="text-white font-black text-xl leading-tight">Chat EcoDrop</h1>
            <p class="text-white/70 text-xs truncate">
                {{ $isUser ? 'Chat dengan tim admin' : 'Pantau dan tangani percakapan user' }}
            </p>
        </div>

        {{-- WS indicator --}}
        <div class="flex-shrink-0 flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1.5 border border-white/20">
            <span class="w-2 h-2 rounded-full animate-pulse"
                  :class="wsConnected ? 'bg-lime-300' : 'bg-yellow-300'"></span>
            <span class="text-white text-[10px] font-bold"
                  x-text="wsConnected ? 'Live' : 'Sync'"></span>
        </div>
    </div>
</div>

{{-- ═══ LIST ═══ --}}
<div class="flex-1 overflow-y-auto chat-scroll px-3 py-4 sm:px-4" id="convList">

    {{-- User — belum punya conversation --}}
    <template x-if="isUser && conversations.length === 0">
        <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-6">
            <div class="w-20 h-20 bg-white rounded-[28px] shadow-xl shadow-emerald-100 flex items-center justify-center mb-5">
                <svg class="w-10 h-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a10.5 10.5 0 01-4.6-1.05L3 20l1.25-3.75A7.08 7.08 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <p class="text-gray-700 font-black text-lg mb-2">Chat dengan Admin</p>
            <p class="text-gray-400 text-sm mb-6">Tanya dulu ke admin sebelum setor sampah atau saat butuh bantuan.</p>
            <button @click="startChat()"
                    :disabled="startingChat"
                    class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl font-bold shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 disabled:opacity-60 disabled:cursor-not-allowed"
                    x-text="startingChat ? 'Memuat...' : 'Mulai Chat'">
            </button>
        </div>
    </template>

    {{-- Admin/Superadmin — belum ada percakapan --}}
    <template x-if="!isUser && conversations.length === 0">
        <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-6">
            <div class="w-20 h-20 bg-white rounded-[28px] shadow-xl shadow-gray-100 flex items-center justify-center mb-5">
                <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-2.586 2.586A2 2 0 0116 16H8a2 2 0 01-1.414-.586L4 13m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4"/>
                </svg>
            </div>
            <p class="text-gray-600 font-black text-lg">Belum ada percakapan</p>
            <p class="text-gray-400 text-sm mt-1">Percakapan dari user akan muncul otomatis di sini.</p>
        </div>
    </template>

    {{-- List percakapan --}}
    <template x-if="conversations.length > 0">
        <div class="space-y-3">
            <template x-for="conv in conversations" :key="conv.id">
                <a :href="'/conv/' + conv.id"
                   class="conversation-card group flex items-center gap-3 p-4 rounded-[20px] sm:rounded-[24px] active:scale-[0.98]">

                    {{-- Avatar --}}
                    <div class="relative flex-shrink-0">
                        <div class="w-12 h-12 rounded-2xl overflow-hidden border-2 border-white shadow-sm bg-gray-100">
                            <img class="w-full h-full object-cover"
                                 :src="isUser
                                     ? 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true&size=128'
                                     : (conv.user_photo || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(conv.user_name || 'User') + '&background=10b981&color=fff&bold=true&size=128'))"
                                 :alt="isUser ? 'Admin EcoDrop' : conv.user_name">
                        </div>
                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white"
                              :class="conv.is_closed ? 'bg-red-400' : (conv.is_handled ? 'bg-emerald-500' : 'bg-yellow-400')"></span>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <p class="font-black text-gray-900 text-sm truncate"
                               x-text="isUser ? 'Admin EcoDrop' : conv.user_name"></p>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <template x-if="conv.unread > 0">
                                    <span class="min-w-[20px] h-5 px-1.5 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center unread-pulse"
                                          x-text="conv.unread > 99 ? '99+' : conv.unread"></span>
                                </template>
                                <template x-if="conv.last_time">
                                    <span class="text-[11px] text-gray-400 font-medium" x-text="conv.last_time"></span>
                                </template>
                            </div>
                        </div>

                        <p class="text-xs truncate leading-snug"
                           :class="conv.unread > 0 ? 'font-bold text-gray-700' : 'text-gray-400'"
                           x-text="conv.last_message
                               ? (conv.last_message.length > 45 ? conv.last_message.substring(0,45) + '…' : conv.last_message)
                               : 'Belum ada pesan'"></p>

                        <p class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold leading-none"
                           :class="conv.is_closed
                               ? 'bg-red-50 text-red-600'
                               : (conv.is_handled ? 'bg-emerald-50 text-emerald-600' : 'bg-yellow-50 text-yellow-700')"
                           x-text="conv.is_closed
                               ? 'Sesi diakhiri'
                               : (conv.is_handled ? 'Ditangani: ' + (conv.assigned_admin || 'Admin') : 'Menunggu admin')">
                        </p>
                    </div>

                    {{-- Chevron --}}
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-400 transition flex-shrink-0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </template>
        </div>
    </template>

</div>
</body>
</html>
