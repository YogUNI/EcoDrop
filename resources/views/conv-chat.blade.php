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
        #messages { scroll-behavior: smooth; }
        .bubble-me {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            border-radius: 18px 18px 4px 18px;
        }
        .bubble-other {
            background: #f3f4f6;
            color: #1f2937;
            border-radius: 18px 18px 18px 4px;
        }
        .typing-dot { animation: bounce 1.4s infinite; }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }
        @keyframes bounce {
            0%, 60%, 100% { transform: translateY(0); opacity:.4; }
            30% { transform: translateY(-6px); opacity:1; }
        }
    </style>
</head>
<body class="bg-gray-50 h-screen flex flex-col overflow-hidden">

@php
    $role      = Auth::user()->role;
    $isUser    = $role === 'user';
    $isAdmin   = in_array($role, ['admin', 'super_admin']);
    $navColor  = $isUser ? 'from-green-600 to-emerald-600'
               : ($role === 'admin' ? 'from-blue-600 to-indigo-600'
               : 'from-amber-600 to-orange-600');
    $listRoute = route('chat.list');

    $displayName  = $isUser ? 'Admin EcoDrop' : $conv->user->name;
    $displayPhoto = $isUser
        ? 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true'
        : $conv->user->getPhotoUrl();
@endphp

{{-- Header --}}
<div class="bg-gradient-to-r {{ $navColor }} shadow-lg flex-shrink-0">
    <div class="px-4 py-4 flex items-center gap-3">
        <a href="{{ $listRoute }}"
           class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/50 flex-shrink-0">
            <img src="{{ $displayPhoto }}"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&background=10b981&color=fff&bold=true'"
                 class="w-full h-full object-cover">
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-black text-white text-base truncate">{{ $displayName }}</p>
            @if($conv->is_handled)
                <p class="text-white/70 text-xs">✓ Ditangani: {{ $conv->assignedAdmin?->name ?? 'Admin' }}</p>
            @else
                <p class="text-yellow-200 text-xs font-semibold">⏳ Menunggu admin</p>
            @endif
        </div>
    </div>

    @if($isAdmin && !$conv->is_handled)
        <div class="px-4 pb-4">
            <button id="handleBtn"
                class="w-full py-2.5 bg-white/20 hover:bg-white/30 text-white font-bold text-sm rounded-xl transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                🙋 Tangani Percakapan Ini
            </button>
        </div>
    @endif
</div>

{{-- Messages --}}
<div id="messages" class="flex-1 overflow-y-auto px-4 py-4 space-y-4">
    <div id="loadingState" class="flex justify-center py-12">
        <div class="flex flex-col items-center gap-3">
            <div class="flex gap-1">
                <div class="w-2 h-2 bg-gray-400 rounded-full typing-dot"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full typing-dot"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full typing-dot"></div>
            </div>
            <p class="text-xs text-gray-400">Memuat pesan...</p>
        </div>
    </div>
    <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-3xl flex items-center justify-center mb-4 mx-auto">
            <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <p class="text-gray-500 font-bold text-lg mb-1">Belum ada pesan</p>
        <p class="text-gray-400 text-sm">Mulai percakapan sekarang!</p>
    </div>
    <div id="msgContainer" class="space-y-4 hidden"></div>
</div>

{{-- Input --}}
<div class="bg-white border-t border-gray-100 shadow-lg flex-shrink-0 px-4 py-3">
    <div class="flex items-end gap-3">
        <div class="w-9 h-9 rounded-full overflow-hidden border-2 border-gray-200 flex-shrink-0">
            <img src="{{ Auth::user()->getPhotoUrl() }}"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=10b981&color=fff&bold=true'"
                 class="w-full h-full object-cover">
        </div>
        <div class="flex-1 relative">
            <textarea id="msgInput"
                placeholder="Tulis pesan..."
                rows="1"
                class="w-full px-4 py-3 pr-14 bg-gray-50 border-2 border-gray-200 focus:border-green-400 focus:bg-white rounded-2xl text-sm font-medium resize-none focus:outline-none transition"
                style="max-height: 120px;"
                onkeydown="handleKey(event)"
                oninput="autoResize(this)"></textarea>
            <button id="sendBtn" onclick="sendMessage()"
                class="absolute right-2 bottom-2 w-9 h-9 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl flex items-center justify-center transition hover:scale-105">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    const convId    = {{ $conv->id }};
    const myId      = {{ Auth::id() }};
    const myRole    = '{{ $role }}';
    const csrf      = document.querySelector('meta[name="csrf-token"]').content;

    function getFallbackAvatar(name) {
        return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=10b981&color=fff&bold=true`;
    }

    async function loadMessages() {
        try {
            const res = await fetch(`/conversations/${convId}/messages`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            const msgs = await res.json();
            document.getElementById('loadingState').classList.add('hidden');
            if (msgs.length === 0) {
                document.getElementById('emptyState').classList.remove('hidden');
                document.getElementById('emptyState').classList.add('flex');
            } else {
                document.getElementById('msgContainer').classList.remove('hidden');
                msgs.forEach(m => appendMessage(m));
                scrollBottom();
            }
        } catch(e) {
            document.getElementById('loadingState').innerHTML =
                '<p class="text-red-400 text-sm">Gagal memuat pesan. Refresh halaman.</p>';
        }
    }

    function appendMessage(msg) {
        const container  = document.getElementById('msgContainer');
        const emptyState = document.getElementById('emptyState');

        if (!emptyState.classList.contains('hidden')) {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
            container.classList.remove('hidden');
        }

        const isMine   = msg.sender_id === myId || msg.is_mine;
        const photoSrc = msg.sender_photo || getFallbackAvatar(msg.sender_name);
        const fallback = getFallbackAvatar(msg.sender_name);

        // System message
        if (msg.type === 'system') {
            const div = document.createElement('div');
            div.className = 'flex justify-center my-2';
            div.innerHTML = `
                <div class="bg-blue-50 border border-blue-100 text-blue-700 text-xs font-semibold px-4 py-2 rounded-full max-w-[90%] text-center">
                    ${escHtml(msg.content)}
                </div>`;
            container.appendChild(div);
            return;
        }

        // Pickup card
        if (msg.type === 'pickup_card') {
            const pickup = msg.pickup;
            const statusClass = pickup?.status === 'approved' ? 'bg-green-100 text-green-700'
                              : pickup?.status === 'rejected'  ? 'bg-red-100 text-red-700'
                              : 'bg-yellow-100 text-yellow-700';
            const statusText  = pickup?.status === 'approved' ? '✅ Approved'
                              : pickup?.status === 'rejected'  ? '❌ Rejected'
                              : '⏳ Pending';
            const div = document.createElement('div');
            div.className = `flex items-end gap-2 ${isMine ? 'flex-row-reverse' : ''}`;
            div.innerHTML = `
                <div class="w-8 h-8 rounded-full overflow-hidden border border-gray-100 flex-shrink-0">
                    <img src="${escHtml(photoSrc)}" onerror="this.src='${fallback}'" class="w-full h-full object-cover">
                </div>
                <div class="max-w-[80%]">
                    <div class="bg-white border-2 border-green-200 rounded-2xl overflow-hidden shadow-sm">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-3 py-2">
                            <p class="text-white text-xs font-black">📦 Setoran Sampah Baru</p>
                        </div>
                        <div class="p-3">
                            <p class="font-black text-gray-900 text-sm">${pickup ? escHtml(pickup.type) + ' · ' + pickup.weight + ' Kg' : ''}</p>
                            <p class="text-xs text-gray-500 mt-1">${pickup ? '📅 ' + escHtml(pickup.pickup_date) : ''}</p>
                            <span class="inline-block mt-2 text-xs font-bold px-2 py-0.5 rounded-full ${statusClass}">${statusText}</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 px-1 mt-0.5 ${isMine ? 'text-right' : ''}">${escHtml(msg.created_at)}</p>
                </div>`;
            container.appendChild(div);
            return;
        }

        // Normal text
        const senderLabel = isMine ? '' : (msg.sender_role === 'user' ? msg.sender_name : 'Admin EcoDrop');
        const div = document.createElement('div');
        div.className = `flex items-end gap-2 ${isMine ? 'flex-row-reverse' : ''}`;
        div.innerHTML = `
            <div class="w-8 h-8 rounded-full overflow-hidden border border-gray-100 shadow-sm flex-shrink-0">
                <img src="${escHtml(photoSrc)}" onerror="this.src='${fallback}'" class="w-full h-full object-cover">
            </div>
            <div class="max-w-[75%] flex flex-col gap-0.5 ${isMine ? 'items-end' : 'items-start'}">
                ${!isMine && senderLabel ? `<p class="text-xs text-gray-400 px-1 font-semibold">${escHtml(senderLabel)}</p>` : ''}
                <div class="${isMine ? 'bubble-me' : 'bubble-other'} px-4 py-3 shadow-sm">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">${escHtml(msg.content)}</p>
                </div>
                <p class="text-xs text-gray-400 px-1 ${isMine ? 'text-right' : ''}">${escHtml(msg.created_at)}</p>
            </div>`;
        container.appendChild(div);
    }

    async function sendMessage() {
        const input = document.getElementById('msgInput');
        const msg   = input.value.trim();
        if (!msg) return;

        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        input.value  = '';
        input.style.height = 'auto';

        try {
            const res = await fetch(`/conversations/${convId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ content: msg })
            });
            const data = await res.json();
            appendMessage(data);
            scrollBottom();
        } catch(e) {
            input.value = msg;
        } finally {
            btn.disabled = false;
            input.focus();
        }
    }

    const handleBtn = document.getElementById('handleBtn');
    if (handleBtn) {
        handleBtn.addEventListener('click', async () => {
            handleBtn.disabled = true;
            handleBtn.innerHTML = '⏳ Memproses...';
            try {
                const res = await fetch(`/conversations/${convId}/handle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    }
                });
                const data = await res.json();
                if (data.success) {
                    appendMessage(data.message);
                    scrollBottom();
                    handleBtn.closest('div')?.remove();
                }
            } catch(e) {
                handleBtn.disabled  = false;
                handleBtn.innerHTML = '🙋 Tangani Percakapan Ini';
            }
        });
    }

    function handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    }

    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    }

    function scrollBottom() {
        const el = document.getElementById('messages');
        if (el) el.scrollTop = el.scrollHeight;
    }

    function escHtml(text) {
        return String(text ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.addEventListener('load', () => {
        loadMessages();

        if (typeof Echo !== 'undefined') {
            Echo.channel(`conversation.${convId}`)
                .listen('.message.sent', (data) => {
                    if (data.sender_id !== myId) {
                        appendMessage(data);
                        scrollBottom();
                    }
                });
        }
    });
</script>
</body>
</html>