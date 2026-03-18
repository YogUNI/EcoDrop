<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat — {{ $pickup->user->name }} | EcoDrop</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .chat-bubble-user {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-radius: 18px 18px 4px 18px;
        }
        .chat-bubble-admin {
            background: white;
            color: #1f2937;
            border-radius: 18px 18px 18px 4px;
            border: 1px solid #e5e7eb;
        }
        .chat-bubble-me {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            border-radius: 18px 18px 4px 18px;
        }
        #messages { scroll-behavior: smooth; }
        .typing-dot { animation: typingDot 1.4s infinite; }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typingDot {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-6px); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50 h-screen flex flex-col overflow-hidden">

@php
    $role      = Auth::user()->role;
    $isUser    = $role === 'user';
    $navColor  = $isUser ? 'from-green-600 to-emerald-600'
               : ($role === 'admin' ? 'from-blue-600 to-indigo-600'
               : 'from-amber-600 to-orange-600');
    $dashRoute = $isUser ? route('user.dashboard')
               : ($role === 'admin' ? route('admin.dashboard')
               : route('superadmin.dashboard'));
    $listRoute = route('chat.list');
@endphp

{{-- Header --}}
<div class="bg-gradient-to-r {{ $navColor }} shadow-lg flex-shrink-0">
    <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-4">

        {{-- Back button: Mobile → chat list, Desktop → dashboard --}}
        <a href="{{ $listRoute }}" class="md:hidden w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition duration-200 flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <a href="{{ $dashRoute }}" class="hidden md:flex w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl items-center justify-center transition duration-200 flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>

        {{-- User info --}}
        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/50 flex-shrink-0">
            <img src="{{ $pickup->user->getPhotoUrl() }}" class="w-full h-full object-cover" alt="{{ $pickup->user->name }}">
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-black text-white text-base leading-tight truncate">
                {{ $isUser ? 'Admin EcoDrop' : $pickup->user->name }}
            </p>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs text-white/80 font-semibold">
                    @switch($pickup->type)
                        @case('Plastik') 🪴 @break
                        @case('Kertas') 📄 @break
                        @case('Logam') 🔩 @break
                        @case('Kaca') 🥛 @break
                        @case('Organik') 🍂 @break
                        @case('Elektronik') ⚡ @break
                        @default 📦
                    @endswitch
                    {{ $pickup->type }} · {{ $pickup->weight }} Kg
                </span>
                <span class="text-white/50">·</span>
                @if($pickup->status === 'pending')
                    <span class="text-xs text-yellow-200 font-bold">⏳ Pending</span>
                @elseif($pickup->status === 'approved')
                    <span class="text-xs text-green-200 font-bold">✅ Approved</span>
                @else
                    <span class="text-xs text-red-200 font-bold">❌ Rejected</span>
                @endif
            </div>
        </div>

        {{-- Setoran ID badge --}}
        <div class="flex-shrink-0 bg-white/20 rounded-xl px-3 py-1.5 text-center hidden sm:block">
            <p class="text-white/60 text-xs">Setoran</p>
            <p class="text-white font-black text-sm">#{{ $pickup->id }}</p>
        </div>
    </div>
</div>

{{-- Messages Area --}}
<div id="messages" class="flex-1 overflow-y-auto px-4 py-6 max-w-3xl mx-auto w-full space-y-4">

    {{-- Loading state --}}
    <div id="loadingMessages" class="flex items-center justify-center py-12">
        <div class="flex flex-col items-center gap-3">
            <div class="flex gap-1">
                <div class="w-2 h-2 bg-gray-400 rounded-full typing-dot"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full typing-dot"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full typing-dot"></div>
            </div>
            <p class="text-xs text-gray-400 font-medium">Memuat pesan...</p>
        </div>
    </div>

    {{-- Empty state --}}
    <div id="emptyState" class="hidden flex flex-col items-center justify-center py-16 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-3xl flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <p class="text-gray-500 font-bold text-lg mb-1">Belum ada pesan</p>
        <p class="text-gray-400 text-sm">Mulai percakapan tentang setoran ini</p>
    </div>

    {{-- Messages container --}}
    <div id="messagesContainer" class="space-y-4 hidden"></div>
</div>

{{-- Input Area --}}
<div class="bg-white border-t border-gray-100 shadow-lg flex-shrink-0">
    <div class="max-w-3xl mx-auto px-4 py-4">
        <div class="flex items-end gap-3">
            <div class="w-9 h-9 rounded-full overflow-hidden border-2 border-gray-200 flex-shrink-0 mb-0.5">
                <img src="{{ Auth::user()->getPhotoUrl() }}" class="w-full h-full object-cover" alt="Me">
            </div>
            <div class="flex-1 relative">
                <textarea id="messageInput"
                    placeholder="Tulis pesan..."
                    rows="1"
                    class="w-full px-4 py-3 pr-14 bg-gray-50 border-2 border-gray-200 focus:border-blue-400 focus:bg-white rounded-2xl text-sm font-medium text-gray-800 placeholder-gray-400 resize-none focus:outline-none transition duration-200"
                    style="max-height: 120px;"
                    onkeydown="handleKeyDown(event)"
                    oninput="autoResize(this)"></textarea>
                <button id="sendBtn" onclick="sendMessage()"
                    class="absolute right-2 bottom-2 w-9 h-9 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl flex items-center justify-center transition duration-200 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2 ml-12 hidden sm:block">
            Enter untuk kirim · Shift+Enter untuk baris baru
        </p>
    </div>
</div>

<script>
    const pickupId  = {{ $pickup->id }};
    const myId      = {{ Auth::id() }};
    const myRole    = '{{ Auth::user()->role }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    async function loadMessages() {
        try {
            const res = await fetch(`/messages/${pickupId}`, {
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const messages = await res.json();
            document.getElementById('loadingMessages').classList.add('hidden');
            if (messages.length === 0) {
                document.getElementById('emptyState').classList.remove('hidden');
            } else {
                document.getElementById('messagesContainer').classList.remove('hidden');
                messages.forEach(msg => appendMessage(msg));
                scrollToBottom();
            }
        } catch (err) {
            document.getElementById('loadingMessages').innerHTML =
                '<p class="text-red-400 text-sm font-medium">Gagal memuat pesan. Refresh halaman.</p>';
        }
    }

    function appendMessage(msg) {
        const container  = document.getElementById('messagesContainer');
        const emptyState = document.getElementById('emptyState');

        if (!emptyState.classList.contains('hidden')) {
            emptyState.classList.add('hidden');
            container.classList.remove('hidden');
        }

        const isMine   = msg.sender_id === myId || msg.is_mine;
        const isAdmin  = msg.sender_role === 'admin' || msg.sender_role === 'super_admin';
        const roleLabel = msg.sender_role === 'super_admin' ? '⭐ Super Admin'
                        : msg.sender_role === 'admin' ? '👑 Admin' : '🌿 User';

        const div = document.createElement('div');
        div.className = `flex items-end gap-2 ${isMine ? 'flex-row-reverse' : 'flex-row'}`;
        div.id = `msg-${msg.id}`;

        const bubbleClass = isMine ? 'chat-bubble-me'
                          : (isAdmin ? 'chat-bubble-admin' : 'chat-bubble-user');

        div.innerHTML = `
            <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-white shadow flex-shrink-0">
                <img src="${msg.sender_photo}" class="w-full h-full object-cover" alt="${msg.sender_name}">
            </div>
            <div class="max-w-[70%] space-y-1">
                ${!isMine ? `<p class="text-xs font-bold text-gray-500 px-1">${msg.sender_name} <span class="font-normal">${roleLabel}</span></p>` : ''}
                <div class="${bubbleClass} px-4 py-3 shadow-sm">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">${escapeHtml(msg.message)}</p>
                </div>
                <p class="text-xs text-gray-400 px-1 ${isMine ? 'text-right' : 'text-left'}">${msg.created_at}</p>
            </div>
        `;
        container.appendChild(div);
    }

    async function sendMessage() {
        const input   = document.getElementById('messageInput');
        const message = input.value.trim();
        if (!message) return;

        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        input.value  = '';
        input.style.height = 'auto';

        try {
            const res = await fetch(`/messages/${pickupId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message })
            });
            const data = await res.json();
            appendMessage(data);
            scrollToBottom();
        } catch (err) {
            input.value = message;
        } finally {
            btn.disabled = false;
            input.focus();
        }
    }

    function handleKeyDown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    }

    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    }

    function scrollToBottom() {
        const el = document.getElementById('messages');
        el.scrollTop = el.scrollHeight;
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.addEventListener('load', () => {
        loadMessages();

        if (typeof Echo !== 'undefined') {
            Echo.channel(`pickup.${pickupId}`)
                .listen('.message.sent', (data) => {
                    if (data.sender_id !== myId) {
                        appendMessage(data);
                        scrollToBottom();
                    }
                });
        }
    });
</script>
</body>
</html>