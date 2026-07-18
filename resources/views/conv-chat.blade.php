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
                radial-gradient(circle at top left, rgba(16, 185, 129, .16), transparent 32%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, .10), transparent 32%),
                #edf7f1;
        }
        #messages { scroll-behavior: smooth; }
        .message-feed {
            background-color: #f1f7f3;
            background-image:
                linear-gradient(rgba(15, 118, 110, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15, 118, 110, .045) 1px, transparent 1px);
            background-size: 22px 22px;
        }
        .chat-scroll::-webkit-scrollbar { width: 7px; }
        .chat-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .75);
            border-radius: 999px;
        }
        .bubble-me {
            background: linear-gradient(135deg, #059669, #0f766e);
            color: white;
            border-radius: 22px 22px 6px 22px;
            box-shadow: 0 12px 26px rgba(5, 150, 105, .22);
        }
        .bubble-other {
            background: rgba(255, 255, 255, .96);
            color: #1f2937;
            border: 1px solid rgba(226, 232, 240, .95);
            border-radius: 22px 22px 22px 6px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        }
        .typing-dot { animation: bounce 1.4s infinite; }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }
        @keyframes bounce {
            0%, 60%, 100% { transform: translateY(0); opacity:.4; }
            30% { transform: translateY(-6px); opacity:1; }
        }
        #connStatus {
            position: fixed;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 50;
            transition: opacity .4s, transform .4s;
        }
        #connStatus.hidden { opacity: 0; pointer-events: none; transform: translateX(-50%) translateY(8px); }
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden text-gray-700">

@php
    $role      = Auth::user()->role;
    $isUser    = $role === 'user';
    $isAdmin   = in_array($role, ['admin', 'super_admin']);
    $navColor  = $isUser ? 'from-green-600 to-emerald-600'
               : ($role === 'admin' ? 'from-blue-600 to-indigo-600'
               : 'from-amber-600 to-orange-600');
    $listRoute = route('chat.list');

    $displayName  = $isUser ? 'Admin EcoDrop' : ($conv->user?->name ?? 'User EcoDrop');
    $displayPhoto = $isUser
        ? 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true'
        : ($conv->user?->getPhotoUrl() ?? 'https://ui-avatars.com/api/?name=User+EcoDrop&background=10b981&color=fff&bold=true');
@endphp

{{-- ═══ HEADER ═══ --}}
<div class="relative overflow-hidden bg-gradient-to-br {{ $navColor }} shadow-xl flex-shrink-0">
    <div class="absolute -right-10 -top-12 w-44 h-44 bg-white/10 rounded-full blur-2xl"></div>
    <div class="absolute left-16 -bottom-14 w-36 h-36 bg-white/10 rounded-full blur-2xl"></div>

    <div class="relative px-4 py-4 flex items-center gap-3">
        <a href="{{ $listRoute }}"
           class="w-11 h-11 bg-white/18 hover:bg-white/28 rounded-2xl flex items-center justify-center transition flex-shrink-0 border border-white/20"
           aria-label="Kembali ke daftar chat">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="relative flex-shrink-0">
            <div class="w-11 h-11 rounded-2xl overflow-hidden border-2 border-white/45 shadow-sm">
                <img src="{{ $displayPhoto }}"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&background=10b981&color=fff&bold=true'"
                     class="w-full h-full object-cover"
                     alt="{{ $displayName }}">
            </div>
            <span id="statusDot" class="absolute -right-1 -bottom-1 w-3.5 h-3.5 rounded-full {{ $conv->is_handled ? 'bg-lime-300' : 'bg-yellow-300' }} border-2 border-white"></span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-black text-white text-base truncate">{{ $displayName }}</p>
            <p id="headerSub" class="text-xs {{ $conv->is_handled ? 'text-white/75' : 'text-yellow-100 font-semibold' }}">
                @if($conv->is_handled)
                    Ditangani: {{ $conv->assignedAdmin?->name ?? 'Admin' }}
                @else
                    Menunggu admin menangani percakapan
                @endif
            </p>
        </div>
        {{-- WS status dot --}}
        <div class="flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1.5 border border-white/20 flex-shrink-0">
            <span id="wsDot" class="w-2 h-2 rounded-full bg-yellow-300"></span>
            <span id="wsLabel" class="text-white text-[10px] font-bold">Connecting</span>
        </div>
    </div>

    @if($isAdmin && !$conv->is_handled)
    <div class="relative px-4 pb-4" id="handleContainer">
        <button id="handleBtn"
            class="w-full py-2.5 bg-white/18 hover:bg-white/28 text-white font-bold text-sm rounded-2xl transition flex items-center justify-center gap-2 border border-white/20">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Tangani Percakapan Ini
        </button>
    </div>
    @endif
</div>

{{-- ═══ MESSAGES ═══ --}}
<div id="messages" class="message-feed chat-scroll flex-1 overflow-y-auto px-4 py-5 space-y-4">
    <div id="loadingState" class="flex justify-center py-12">
        <div class="flex flex-col items-center gap-3 rounded-3xl bg-white/85 px-6 py-5 shadow-lg shadow-gray-100 border border-white">
            <div class="flex gap-1">
                <div class="w-2 h-2 bg-emerald-500 rounded-full typing-dot"></div>
                <div class="w-2 h-2 bg-emerald-500 rounded-full typing-dot"></div>
                <div class="w-2 h-2 bg-emerald-500 rounded-full typing-dot"></div>
            </div>
            <p class="text-xs text-gray-400 font-semibold">Memuat pesan...</p>
        </div>
    </div>
    <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-center">
        <div class="w-20 h-20 bg-white rounded-[28px] flex items-center justify-center mb-4 mx-auto shadow-xl shadow-gray-100 border border-white">
            <svg class="w-10 h-10 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <p class="text-gray-600 font-black text-lg mb-1">Belum ada pesan</p>
        <p class="text-gray-400 text-sm">Mulai percakapan sekarang.</p>
    </div>
    <div id="msgContainer" class="space-y-4 hidden"></div>
    
    {{-- Typing Indicator Bubble --}}
    <div id="typingIndicator" class="flex items-end gap-2 hidden transition duration-200">
        <div class="w-8 h-8 rounded-2xl overflow-hidden border border-white shadow-sm flex-shrink-0">
            <img id="typingAvatar" src="" class="w-full h-full object-cover">
        </div>
        <div class="max-w-[78%] flex flex-col gap-1">
            <p id="typingName" class="text-xs text-gray-400 px-1 font-semibold"></p>
            <div class="bubble-other px-4 py-3 flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.12s"></span>
                <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.24s"></span>
            </div>
        </div>
    </div>
</div>

{{-- ═══ INPUT ═══ --}}
<div class="bg-white/95 backdrop-blur border-t border-white/80 shadow-[0_-12px_35px_rgba(15,23,42,0.08)] flex-shrink-0 px-4 py-3">
    <div class="flex items-end gap-3">
        <div class="w-10 h-10 rounded-2xl overflow-hidden border-2 border-gray-100 shadow-sm flex-shrink-0">
            <img src="{{ Auth::user()->getPhotoUrl() }}"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=10b981&color=fff&bold=true'"
                 class="w-full h-full object-cover"
                 alt="{{ Auth::user()->name }}">
        </div>
        <div class="flex-1 relative rounded-[22px] bg-gray-50 border border-gray-200 focus-within:border-emerald-300 focus-within:bg-white transition">
            <textarea id="msgInput"
                placeholder="Tulis pesan..."
                rows="1"
                class="w-full px-4 py-3 pr-14 bg-transparent border-0 rounded-[22px] text-sm font-medium resize-none focus:outline-none focus:ring-0 transition"
                style="max-height: 120px;"
                onkeydown="handleKey(event)"
                oninput="autoResize(this)"></textarea>
            <button id="sendBtn" onclick="sendMessage()"
                class="absolute right-2 bottom-2 w-9 h-9 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white rounded-2xl flex items-center justify-center transition hover:scale-105 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
    </div>
</div>

{{-- ═══ CONNECTION STATUS TOAST ═══ --}}
<div id="connStatus" class="hidden">
    <div class="flex items-center gap-2 bg-gray-800/90 text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-xl backdrop-blur-sm">
        <span id="connIcon" class="w-2 h-2 rounded-full bg-yellow-400"></span>
        <span id="connText">Menghubungkan...</span>
    </div>
</div>

<script>
    // ─── Constants ─────────────────────────────────────────────────────────────
    const convId  = {{ $conv->id }};
    const myId    = {{ Auth::id() }};
    const myRole  = '{{ $role }}';
    const csrf    = document.querySelector('meta[name="csrf-token"]').content;

    // ─── Message Deduplication ─────────────────────────────────────────────────
    // Prevents the same message from appearing twice (WS + poll overlap)
    const seenIds = new Set();
    let lastPolledId = 0;
    let pollTimer    = null;
    let wsConnected  = false;

    // ─── Helper: HTML escape ───────────────────────────────────────────────────
    function escHtml(text) {
        return String(text ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    function getFallbackAvatar(name) {
        return `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'EcoDrop')}&background=10b981&color=fff&bold=true`;
    }

    // ─── WS Status UI ─────────────────────────────────────────────────────────
    function setWsStatus(state) {
        const dot   = document.getElementById('wsDot');
        const label = document.getElementById('wsLabel');
        const toast = document.getElementById('connStatus');
        const icon  = document.getElementById('connIcon');
        const text  = document.getElementById('connText');

        if (state === 'connected') {
            dot.className   = 'w-2 h-2 rounded-full bg-lime-300';
            label.textContent = 'Live';
            toast.classList.add('hidden');
        } else if (state === 'polling') {
            dot.className   = 'w-2 h-2 rounded-full bg-yellow-300 animate-pulse';
            label.textContent = 'Polling';
            icon.className  = 'w-2 h-2 rounded-full bg-yellow-400';
            text.textContent = 'Mode backup aktif (polling)';
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 4000);
        } else {
            dot.className   = 'w-2 h-2 rounded-full bg-red-400';
            label.textContent = 'Offline';
            icon.className  = 'w-2 h-2 rounded-full bg-red-400';
            text.textContent = 'Tidak terhubung — sedang mencoba ulang...';
            toast.classList.remove('hidden');
        }
    }

    // ─── Load initial messages ─────────────────────────────────────────────────
    async function loadMessages() {
        try {
            const res  = await fetch(`/conversations/${convId}/messages`, {
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
                // Track last seen ID for incremental polling
                lastPolledId = Math.max(...msgs.map(m => m.id));
                msgs.forEach(m => seenIds.add(m.id));
            }
        } catch(e) {
            document.getElementById('loadingState').innerHTML =
                '<p class="text-red-500 text-sm font-semibold text-center">Gagal memuat pesan. Refresh halaman.</p>';
        }
    }

    // ─── Append one message (dedup-safe) ──────────────────────────────────────
    function appendMessage(msg, fromWs = false) {
        if (msg.id && seenIds.has(msg.id)) return; // already rendered
        if (msg.id) seenIds.add(msg.id);

        const container  = document.getElementById('msgContainer');
        const emptyState = document.getElementById('emptyState');

        if (!emptyState.classList.contains('hidden')) {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
            container.classList.remove('hidden');
        }

        const isMine   = msg.sender_id === myId || msg.is_mine === true;
        const photoSrc = msg.sender_photo || getFallbackAvatar(msg.sender_name);
        const fallback = getFallbackAvatar(msg.sender_name);

        // ── System message ──────────────────────────────────────────────────
        if (msg.type === 'system') {
            const div = document.createElement('div');
            div.className = 'flex justify-center my-2';
            div.setAttribute('data-msg-id', msg.id || '');
            div.innerHTML = `
                <div class="bg-white/85 border border-emerald-100 text-emerald-700 text-xs font-bold px-4 py-2 rounded-full max-w-[90%] text-center shadow-sm">
                    ${escHtml(msg.content)}
                </div>`;
            container.appendChild(div);
            return;
        }

        // ── Pickup card ─────────────────────────────────────────────────────
        if (msg.type === 'pickup_card') {
            const pickup      = msg.pickup;
            const statusClass = pickup?.status === 'approved' ? 'bg-emerald-100 text-emerald-700'
                              : pickup?.status === 'rejected'  ? 'bg-red-100 text-red-700'
                              : 'bg-yellow-100 text-yellow-700';
            const statusText  = pickup?.status === 'approved' ? 'Approved'
                              : pickup?.status === 'rejected'  ? 'Rejected'
                              : 'Pending';
            const div = document.createElement('div');
            div.className = `flex items-end gap-2 ${isMine ? 'flex-row-reverse' : ''}`;
            div.setAttribute('data-msg-id', msg.id || '');
            div.innerHTML = `
                <div class="w-8 h-8 rounded-2xl overflow-hidden border border-white shadow-sm flex-shrink-0">
                    <img src="${escHtml(photoSrc)}" onerror="this.src='${fallback}'" class="w-full h-full object-cover">
                </div>
                <div class="max-w-[82%]">
                    <div class="bg-white border border-emerald-100 rounded-[22px] overflow-hidden shadow-lg shadow-gray-100">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-3 py-2">
                            <p class="text-white text-xs font-black">Setoran Sampah Baru</p>
                        </div>
                        <div class="p-3">
                            <p class="font-black text-gray-900 text-sm">${pickup ? escHtml(pickup.type) + ' - ' + escHtml(pickup.weight) + ' Kg' : ''}</p>
                            <p class="text-xs text-gray-500 mt-1">${pickup ? escHtml(pickup.pickup_date) : ''}</p>
                            <span class="inline-block mt-2 text-xs font-bold px-2.5 py-1 rounded-full ${statusClass}">${statusText}</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 px-1 mt-1 ${isMine ? 'text-right' : ''}">${escHtml(msg.created_at)}</p>
                </div>`;
            container.appendChild(div);
            return;
        }

        // ── Text message ────────────────────────────────────────────────────
        const senderLabel = isMine ? '' : (msg.sender_role === 'user' ? msg.sender_name : 'Admin EcoDrop');
        const div = document.createElement('div');
        div.className = `flex items-end gap-2 ${isMine ? 'flex-row-reverse' : ''}`;
        div.setAttribute('data-msg-id', msg.id || '');
        div.innerHTML = `
            <div class="w-8 h-8 rounded-2xl overflow-hidden border border-white shadow-sm flex-shrink-0">
                <img src="${escHtml(photoSrc)}" onerror="this.src='${fallback}'" class="w-full h-full object-cover">
            </div>
            <div class="max-w-[78%] flex flex-col gap-1 ${isMine ? 'items-end' : 'items-start'}">
                ${!isMine && senderLabel ? `<p class="text-xs text-gray-400 px-1 font-semibold">${escHtml(senderLabel)}</p>` : ''}
                <div class="${isMine ? 'bubble-me' : 'bubble-other'} px-4 py-3">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">${escHtml(msg.content)}</p>
                </div>
                <p class="text-xs text-gray-400 px-1 ${isMine ? 'text-right' : ''}">${escHtml(msg.created_at)}</p>
            </div>`;
        container.appendChild(div);
    }

    // ─── Send message ──────────────────────────────────────────────────────────
    async function sendMessage() {
        const input = document.getElementById('msgInput');
        const msg   = input.value.trim();
        if (!msg) return;

        const btn = document.getElementById('sendBtn');
        btn.disabled = true;
        input.value  = '';
        input.style.height = 'auto';

        try {
            const res  = await fetch(`/conversations/${convId}/messages`, {
                method : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept'      : 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ content: msg })
            });
            const data = await res.json();
            // Render sender's own message immediately (don't wait for WS echo)
            appendMessage({ ...data, is_mine: true });
            scrollBottom();
            if (data.id) lastPolledId = Math.max(lastPolledId, data.id);
        } catch(e) {
            input.value = msg;
        } finally {
            btn.disabled = false;
            input.focus();
        }
    }

    // ─── Handle conversation ───────────────────────────────────────────────────
    const handleBtn = document.getElementById('handleBtn');
    if (handleBtn) {
        handleBtn.addEventListener('click', async () => {
            handleBtn.disabled = true;
            handleBtn.innerHTML = '<span class="animate-pulse">Memproses...</span>';
            try {
                const res  = await fetch(`/conversations/${convId}/handle`, {
                    method : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept'      : 'application/json',
                        'X-CSRF-TOKEN': csrf
                    }
                });
                const data = await res.json();
                if (data.success) {
                    appendMessage(data.message);
                    scrollBottom();
                    document.getElementById('handleContainer')?.remove();
                    document.getElementById('headerSub').textContent =
                        'Ditangani: ' + (data.conversation?.assigned_admin || 'Admin');
                } else {
                    // Keduluan admin lain
                    handleBtn.closest('#handleContainer')?.remove();
                    showToast('⚠️ ' + (data.message || 'Chat sudah diambil admin lain.'));
                }
            } catch(e) {
                handleBtn.disabled  = false;
                handleBtn.innerHTML = 'Tangani Percakapan Ini';
            }
        });
    }

    // ─── Polling fallback (backup when WS is disconnected) ────────────────────
    // Only fetches NEW messages since lastPolledId
    async function pollNewMessages() {
        try {
            const res  = await fetch(`/conversations/${convId}/messages`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            const msgs = await res.json();
            let hasNew = false;
            msgs.forEach(m => {
                if (m.id > lastPolledId && !seenIds.has(m.id)) {
                    appendMessage(m);
                    hasNew = true;
                    lastPolledId = Math.max(lastPolledId, m.id);
                }
            });
            if (hasNew) scrollBottom();
        } catch(e) { /* silent */ }
    }

    function startPolling() {
        if (pollTimer) return;
        setWsStatus('polling');
        pollTimer = setInterval(pollNewMessages, 2000);
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    // ─── Toast helper ─────────────────────────────────────────────────────────
    function showToast(msg) {
        const t = document.getElementById('connStatus');
        document.getElementById('connIcon').className = 'w-2 h-2 rounded-full bg-yellow-400';
        document.getElementById('connText').textContent = msg;
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 4000);
    }

    // ─── Sound Ping ──────────────────────────────────────────────────────────
    function playNotificationPing() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc1 = audioCtx.createOscillator();
            const gain1 = audioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(880, audioCtx.currentTime);
            gain1.gain.setValueAtTime(0.1, audioCtx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
            osc1.connect(gain1);
            gain1.connect(audioCtx.destination);
            osc1.start();
            osc1.stop(audioCtx.currentTime + 0.15);

            setTimeout(() => {
                const osc2 = audioCtx.createOscillator();
                const gain2 = audioCtx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(1046.5, audioCtx.currentTime);
                gain2.gain.setValueAtTime(0.1, audioCtx.currentTime);
                gain2.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.25);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.start();
                osc2.stop(audioCtx.currentTime + 0.25);
            }, 80);
        } catch (e) {}
    }

    // ─── Whisper Typing status ───────────────────────────────────────────────
    let typingTimeout = null;
    function sendTypingWhisper() {
        if (typeof Echo !== 'undefined' && wsConnected) {
            Echo.private(`conversation.${convId}`)
                .whisper('typing', {
                    sender_id: myId,
                    is_typing: true
                });
        }
    }

    // ─── AutoResize and Typing Hook ──────────────────────────────────────────
    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        sendTypingWhisper();
    }

    // ─── Scroll ───────────────────────────────────────────────────────────────
    function scrollBottom() {
        const el = document.getElementById('messages');
        if (el) el.scrollTop = el.scrollHeight;
    }
    function handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    }

    // ─── INIT ─────────────────────────────────────────────────────────────────
    window.addEventListener('load', () => {
        loadMessages();

        // Start polling immediately as a safety net; will be stopped when WS connects
        startPolling();
        setWsStatus('connecting');

        // ── WebSocket via Laravel Echo ──────────────────────────────────────
        if (typeof Echo !== 'undefined') {
            const channel = Echo.private(`conversation.${convId}`);
            
            channel.listen('.message.sent', (data) => {
                // Ignore own messages (already rendered optimistically on send)
                if (data.sender_id === myId) return;
                playNotificationPing();
                appendMessage(data, true);
                scrollBottom();
                
                // Stop typing indication on new message
                document.getElementById('typingIndicator').classList.add('hidden');
            });

            channel.listenForWhisper('typing', (e) => {
                if (e.sender_id !== myId) {
                    const indicator = document.getElementById('typingIndicator');
                    const avatar = document.getElementById('typingAvatar');
                    const name = document.getElementById('typingName');

                    // Populate metadata
                    avatar.src = e.sender_photo || getFallbackAvatar(e.sender_name);
                    name.textContent = e.sender_role === 'user' ? e.sender_name : 'Admin EcoDrop';

                    indicator.classList.remove('hidden');
                    scrollBottom();

                    // Auto hide after 3 seconds of inactivity
                    if (typingTimeout) clearTimeout(typingTimeout);
                    typingTimeout = setTimeout(() => {
                        indicator.classList.add('hidden');
                    }, 3000);
                }
            });
        }

        // ── React to global Echo connection events ──────────────────────────
        document.addEventListener('echo:connected', () => {
            wsConnected = true;
            setWsStatus('connected');
            stopPolling(); // WS is live → polling not needed
        });

        document.addEventListener('echo:disconnected', () => {
            wsConnected = false;
            setWsStatus('polling');
            startPolling(); // Fall back to polling until WS recovers
        });

        // Check initial WS state (it might already be connected by the time load fires)
        if (window.__echoConnected === true) {
            wsConnected = true;
            setWsStatus('connected');
            stopPolling();
        }
    });
</script>
</body>
</html>

