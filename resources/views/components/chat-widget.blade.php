@php
    $role = Auth::user()->role;
    $isMobile = false; // deteksi via JS
@endphp

{{-- Chat Widget — Desktop: popup pojok kanan bawah, Mobile: full page --}}
<div id="chatWidget"
     x-data="{
         isOpen: false,
         currentPickupId: null,
         currentPickup: null,
         messages: [],
         newMessage: '',
         loading: false,
         sending: false,
         unreadTotal: 0,
     }"
     x-init="
         fetchUnread();
         setInterval(() => fetchUnread(), 30000);
     "
     class="hidden md:block">

    {{-- Floating Button (Desktop only) --}}
    <div class="fixed bottom-6 right-6 z-[900]">
        {{-- Unread badge --}}
        <div x-show="unreadTotal > 0 && !isOpen"
             class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center z-10 shadow-lg animate-bounce">
            <span x-text="unreadTotal > 9 ? '9+' : unreadTotal"></span>
        </div>

        {{-- Toggle button --}}
        <button @click="isOpen = !isOpen"
            class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-2xl shadow-2xl shadow-green-200 flex items-center justify-center transition duration-300 hover:scale-110">
            <svg x-show="!isOpen" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <svg x-show="isOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Chat Popup --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform translate-y-4 scale-95"
         class="fixed bottom-24 right-6 z-[899] w-96 bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col"
         style="height: 520px; display: none;">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-5 py-4 flex-shrink-0">
            <div x-show="!currentPickupId">
                <h3 class="text-white font-black text-base">💬 Chat EcoDrop</h3>
                <p class="text-green-100 text-xs mt-0.5">Pilih setoran untuk mulai chat</p>
            </div>
            <div x-show="currentPickupId" class="flex items-center gap-3">
                <button @click="currentPickupId = null; currentPickup = null; messages = []"
                    class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div class="flex-1 min-w-0" x-show="currentPickup">
                    <p class="text-white font-black text-sm truncate" x-text="currentPickup ? (currentPickup.user_name ?? 'Admin EcoDrop') : ''"></p>
                    <p class="text-green-100 text-xs truncate" x-text="currentPickup ? currentPickup.type + ' · ' + currentPickup.weight + ' Kg' : ''"></p>
                </div>
            </div>
        </div>

        {{-- Pickup List (saat belum pilih setoran) --}}
        <div x-show="!currentPickupId" class="flex-1 overflow-y-auto p-4 space-y-2">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Setoran Kamu</p>
            @php
                $role = Auth::user()->role;
                if ($role === 'user') {
                    $chatPickups = \App\Models\Pickup::with(['user', 'messages' => function($q) {
                        $q->latest()->limit(1);
                    }])->where('user_id', Auth::id())->latest()->get();
                } else {
                    $chatPickups = \App\Models\Pickup::with(['user', 'messages' => function($q) {
                        $q->latest()->limit(1);
                    }])->latest()->take(20)->get();
                }
            @endphp

            @forelse($chatPickups as $cp)
                @php
                    $unreadCount = $cp->messages->where('sender_id', '!=', Auth::id())->where('is_read', false)->count();
                    $lastMsg = \App\Models\Message::where('pickup_id', $cp->id)->latest()->first();
                @endphp
                <button
                    @click="openChat({{ $cp->id }}, {{ json_encode([
                        'user_name' => $role === 'user' ? 'Admin EcoDrop' : $cp->user->name,
                        'type'      => $cp->type,
                        'weight'    => $cp->weight,
                        'status'    => $cp->status,
                    ]) }})"
                    class="w-full flex items-center gap-3 p-3 rounded-2xl hover:bg-gray-50 transition duration-200 text-left border border-transparent hover:border-gray-200">
                    <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-100 flex-shrink-0">
                        <img src="{{ $role === 'user' ? asset('images/admin-avatar.png') : $cp->user->getPhotoUrl() }}"
                             onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=10b981&color=fff'"
                             class="w-full h-full object-cover" alt="">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="font-bold text-gray-900 text-sm truncate">
                                {{ $role === 'user' ? 'Admin EcoDrop' : $cp->user->name }}
                            </p>
                            @if($unreadCount > 0)
                                <span class="w-5 h-5 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center flex-shrink-0">{{ $unreadCount }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 truncate">
                            @switch($cp->type)
                                @case('Plastik') 🪴 @break
                                @case('Kertas') 📄 @break
                                @case('Logam') 🔩 @break
                                @case('Kaca') 🥛 @break
                                @case('Organik') 🍂 @break
                                @case('Elektronik') ⚡ @break
                                @default 📦
                            @endswitch
                            {{ $cp->type }} · {{ $cp->weight }} Kg
                        </p>
                        @if($lastMsg)
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ Str::limit($lastMsg->message, 30) }}</p>
                        @else
                            <p class="text-xs text-gray-400 italic mt-0.5">Belum ada pesan</p>
                        @endif
                    </div>
                </button>
            @empty
                <div class="text-center py-8">
                    <div class="text-4xl mb-2">📭</div>
                    <p class="text-gray-400 text-sm">Belum ada setoran</p>
                </div>
            @endforelse
        </div>

        {{-- Messages Area (saat sudah pilih setoran) --}}
        <div x-show="currentPickupId" class="flex-1 overflow-y-auto p-4 space-y-3" id="chatMessages" style="display:none;">
            {{-- Loading --}}
            <div x-show="loading" class="flex justify-center py-8">
                <div class="flex gap-1">
                    <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay:0.1s"></div>
                    <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay:0.2s"></div>
                </div>
            </div>

            {{-- Messages --}}
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.is_mine ? 'flex flex-row-reverse items-end gap-2' : 'flex flex-row items-end gap-2'">
                    <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 border border-gray-100">
                        <img :src="msg.sender_photo" class="w-full h-full object-cover">
                    </div>
                    <div :class="msg.is_mine ? 'items-end' : 'items-start'" class="max-w-[75%] flex flex-col gap-1">
                        <div :class="msg.is_mine
                            ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-2xl rounded-br-sm'
                            : (msg.sender_role === 'user' ? 'bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-2xl rounded-bl-sm' : 'bg-gray-100 text-gray-800 rounded-2xl rounded-bl-sm')"
                             class="px-3 py-2 shadow-sm">
                            <p class="text-sm leading-relaxed break-words whitespace-pre-wrap" x-text="msg.message"></p>
                        </div>
                        <p class="text-xs text-gray-400 px-1" x-text="msg.created_at"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Input (saat sudah pilih setoran) --}}
        <div x-show="currentPickupId" class="border-t border-gray-100 p-3 flex-shrink-0" style="display:none;">
            <div class="flex items-end gap-2">
                <textarea
                    x-model="newMessage"
                    @keydown.enter.prevent="!$event.shiftKey && sendMsg()"
                    placeholder="Tulis pesan..."
                    rows="1"
                    class="flex-1 px-3 py-2.5 bg-gray-50 border border-gray-200 focus:border-green-400 focus:bg-white rounded-xl text-sm font-medium resize-none focus:outline-none transition duration-200"
                    style="max-height: 80px;"
                    @input="$el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight,80)+'px'">
                </textarea>
                <button @click="sendMsg()"
                    :disabled="sending || !newMessage.trim()"
                    class="w-9 h-9 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 disabled:opacity-40 text-white rounded-xl flex items-center justify-center transition duration-200 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Mobile: tombol chat redirect ke halaman penuh --}}
<script>
    const chatMyId = {{ Auth::id() }};
    const chatCsrf = document.querySelector('meta[name="csrf-token"]')?.content;

    // Alpine functions — harus di-inject ke dalam komponen
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatWidget', () => ({
            isOpen: false,
            currentPickupId: null,
            currentPickup: null,
            messages: [],
            newMessage: '',
            loading: false,
            sending: false,
            unreadTotal: 0,

            async fetchUnread() {
                try {
                    const res = await fetch('/messages/unread/count', {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                    });
                    const data = await res.json();
                    this.unreadTotal = data.count || 0;
                } catch(e) {}
            },

            async openChat(pickupId, pickupInfo) {
                this.currentPickupId = pickupId;
                this.currentPickup = pickupInfo;
                this.messages = [];
                this.loading = true;

                await this.$nextTick();
                const el = document.getElementById('chatMessages');
                if (el) el.scrollTop = 0;

                try {
                    const res = await fetch(`/messages/${pickupId}`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                    });
                    this.messages = await res.json();
                } catch(e) {}

                this.loading = false;
                await this.$nextTick();
                this.scrollBottom();

                // Subscribe Reverb
                if (typeof Echo !== 'undefined') {
                    Echo.channel(`pickup.${pickupId}`)
                        .listen('.message.sent', (data) => {
                            if (data.sender_id !== chatMyId) {
                                this.messages.push(data);
                                this.$nextTick(() => this.scrollBottom());
                            }
                        });
                }
            },

            async sendMsg() {
                if (!this.newMessage.trim() || this.sending) return;
                this.sending = true;
                const msg = this.newMessage.trim();
                this.newMessage = '';

                try {
                    const res = await fetch(`/messages/${this.currentPickupId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': chatCsrf
                        },
                        body: JSON.stringify({ message: msg })
                    });
                    const data = await res.json();
                    this.messages.push(data);
                    await this.$nextTick();
                    this.scrollBottom();
                } catch(e) {
                    this.newMessage = msg;
                }
                this.sending = false;
            },

            scrollBottom() {
                const el = document.getElementById('chatMessages');
                if (el) el.scrollTop = el.scrollHeight;
            }
        }));
    });
</script>