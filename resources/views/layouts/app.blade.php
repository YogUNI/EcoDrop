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
        <style>
            .ec-chat-panel {
                background:
                    radial-gradient(circle at top left, rgba(16, 185, 129, .12), transparent 32%),
                    linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            }

            .ec-chat-feed {
                background-color: #f1f5f3;
                background-image:
                    linear-gradient(rgba(15, 118, 110, .045) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(15, 118, 110, .045) 1px, transparent 1px);
                background-size: 22px 22px;
            }

            .ec-chat-scroll::-webkit-scrollbar { width: 7px; }
            .ec-chat-scroll::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 999px;
            }

            .ec-chat-card {
                box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
            }

            .ec-chat-bubble-mine {
                background: linear-gradient(135deg, #059669, #0f766e);
                box-shadow: 0 10px 24px rgba(5, 150, 105, .22);
            }

            .ec-chat-bubble-other {
                background: rgba(255, 255, 255, .96);
                box-shadow: 0 8px 22px rgba(15, 23, 42, .08);
            }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-700">
        <div class="min-h-screen bg-[#f3f4f6]">
            @include('layouts.navigation')
            <main class="min-h-[calc(100vh-200px)]">
                {{ $slot }}
            </main>
            <footer class="bg-white border-t border-gray-100 mt-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-emerald-500 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-bold text-gray-700">EcoDrop</span>
                            <span class="text-xs text-gray-400">— Waste to Worth</span>
                        </div>
                        <p class="text-xs text-gray-400">© 2026 EcoDrop. Semua hak dilindungi.</p>
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
            // NOTE: conversation list is now loaded dynamically via API (not PHP queries)
        @endphp

        <div x-data="chatWidget()">

            {{-- DESKTOP --}}
            <div class="hidden md:block">
                <div class="fixed bottom-6 right-6 z-[900]">
                    <div x-show="unreadTotal > 0 && !isOpen"
                         class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center z-10 shadow-lg animate-pulse">
                        <span x-text="unreadTotal > 9 ? '9+' : unreadTotal"></span>
                    </div>
                    <button @click="isOpen = !isOpen"
                        class="group w-16 h-16 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl shadow-2xl shadow-emerald-300/60 flex items-center justify-center transition duration-300 hover:-translate-y-1">
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
                     class="fixed bottom-24 right-6 z-[899] w-[400px] ec-chat-panel rounded-[28px] shadow-2xl border border-white/80 overflow-hidden flex flex-col"
                     style="height: 620px; display: none;">

                    {{-- Header --}}
                    <div class="bg-gradient-to-br from-emerald-700 to-teal-700 px-5 py-4 flex-shrink-0">
                        <div x-show="!currentConvId">
                            <h3 class="text-white font-black text-base">💬 Chat EcoDrop</h3>
                            <p class="text-emerald-100 text-xs mt-0.5">
                                {{ $isUser ? 'Chat dengan tim admin kami' : 'Semua percakapan user' }}
                            </p>
                        </div>
                        <div x-show="currentConvId" class="flex items-center gap-3">
                            <button @click="closeConv()"
                                class="w-9 h-9 bg-white/15 hover:bg-white/25 rounded-xl flex items-center justify-center transition flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <div class="w-10 h-10 rounded-2xl overflow-hidden border border-white/30 shadow-sm flex-shrink-0">
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

                    {{-- Conversation List — FULLY DYNAMIC via Alpine.js --}}
                    <div x-show="!currentConvId" class="flex-1 overflow-y-auto ec-chat-scroll p-3 space-y-2">

                        {{-- Loading state --}}
                        <div x-show="convListLoading" class="flex justify-center py-8">
                            <div class="flex gap-1">
                                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay:.1s"></div>
                                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay:.2s"></div>
                            </div>
                        </div>

                        {{-- User: start chat button if no conversation --}}
                        <template x-if="!convListLoading && chatIsUser && widgetConvs.length === 0">
                            <div class="p-4">
                                <button @click="startNewChat()"
                                    class="w-full py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-sm flex items-center justify-center gap-2 hover:from-green-600 hover:to-emerald-700 transition">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    Mulai Chat dengan Admin
                                </button>
                                <p class="text-xs text-gray-400 text-center mt-3">Tanya-tanya dulu sebelum setor sampah!</p>
                            </div>
                        </template>

                        {{-- Admin: empty state --}}
                        <template x-if="!convListLoading && !chatIsUser && widgetConvs.length === 0">
                            <div class="text-center py-12">
                                <div class="text-4xl mb-3">💬</div>
                                <p class="text-gray-400 text-sm font-medium">Belum ada percakapan</p>
                            </div>
                        </template>

                        {{-- Conversation cards — reactive to widgetConvs --}}
                        <template x-if="!convListLoading && widgetConvs.length > 0">
                            <div class="space-y-2">
                                <template x-for="cv in widgetConvs" :key="cv.id">
                                    <button @click="openConvFromList(cv)"
                                        class="w-full flex items-center gap-3 p-4 bg-white hover:bg-emerald-50/50 transition text-left border border-gray-100 rounded-2xl ec-chat-card">

                                        {{-- Avatar --}}
                                        <div class="relative flex-shrink-0">
                                            <div class="w-11 h-11 rounded-full overflow-hidden border-2 border-gray-100">
                                                <img :src="chatIsUser
                                                    ? 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true'
                                                    : (cv.user_photo || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(cv.user_name || 'User') + '&background=6366f1&color=fff&bold=true'))"
                                                     class="w-full h-full object-cover">
                                            </div>
                                            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white"
                                                  :class="cv.is_closed ? 'bg-red-400' : (cv.is_handled ? 'bg-green-500' : 'bg-yellow-400')"></span>
                                        </div>

                                        {{-- Info --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                                <p class="font-black text-gray-900 text-sm truncate"
                                                   x-text="chatIsUser ? 'Admin EcoDrop' : cv.user_name"></p>
                                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                                    <template x-if="cv.unread > 0">
                                                        <span class="min-w-[20px] h-5 px-1 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center animate-pulse"
                                                              x-text="cv.unread > 9 ? '9+' : cv.unread"></span>
                                                    </template>
                                                    <template x-if="cv.last_time">
                                                        <span class="text-xs text-gray-400" x-text="cv.last_time"></span>
                                                    </template>
                                                </div>
                                            </div>
                                            <p class="text-xs truncate"
                                               :class="cv.unread > 0 ? 'text-gray-700 font-semibold' : 'text-gray-400'"
                                               x-text="cv.last_message ? cv.last_message.substring(0, 38) + (cv.last_message.length > 38 ? '…' : '') : (chatIsUser ? 'Mulai chat dengan admin!' : 'Belum ada pesan')"></p>
                                            <p class="text-xs mt-0.5 font-semibold"
                                               :class="cv.is_closed ? 'text-red-400' : (cv.is_handled ? 'text-green-500' : 'text-yellow-500')"
                                               x-text="cv.is_closed ? '🔒 Sesi diakhiri' : (cv.is_handled ? '✓ Ditangani: ' + (cv.assigned_admin || 'Admin') : '⏳ Belum ditangani')"></p>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </template>

                    </div>

                    {{-- Messages Area --}}
                    <div x-show="currentConvId" class="flex-1 overflow-y-auto ec-chat-scroll ec-chat-feed p-4 space-y-3" id="chatMessages" style="display:none;">

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
                                            ? 'ec-chat-bubble-mine text-white rounded-2xl rounded-br-sm'
                                            : 'ec-chat-bubble-other text-gray-800 border border-gray-100 rounded-2xl rounded-bl-sm'"
                                             class="px-3 py-2 shadow-sm">
                                            <p class="text-sm leading-relaxed break-words whitespace-pre-wrap" x-text="msg.content"></p>
                                        </div>
                                        <p class="text-xs text-gray-400 px-1" :class="msg.is_mine ? 'text-right' : 'text-left'" x-text="msg.created_at"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Typing indicator bubble --}}
                        <div x-show="isPartnerTyping" class="flex items-end gap-2" style="display:none;">
                            <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 border border-gray-100 shadow-sm">
                                <img :src="chatIsUser ? 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true' : (currentConv ? currentConv.photo : '')" class="w-full h-full object-cover">
                            </div>
                            <div class="items-start max-w-[75%] flex flex-col gap-0.5">
                                <p class="text-xs text-gray-400 px-1 font-semibold" x-text="chatIsUser ? 'Admin EcoDrop' : (currentConv ? currentConv.name : '')"></p>
                                <div class="ec-chat-bubble-other text-gray-800 border border-gray-100 rounded-2xl rounded-bl-sm px-3.5 py-3 shadow-sm flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                                    <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                                    <span class="w-1.5 h-1.5 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Input --}}
                    <div x-show="currentConvId" class="border-t border-gray-100 p-3 flex-shrink-0 bg-white/95 backdrop-blur" style="display:none;">
                        <div class="flex items-end gap-2 rounded-2xl border border-gray-200 bg-gray-50 p-1.5">
                            <textarea x-model="newMessage"
                                @keydown.enter.prevent="!$event.shiftKey && sendMsg()"
                                @input="$el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight,80)+'px'; sendTypingWhisper()"
                                placeholder="Tulis pesan..."
                                rows="1"
                                class="flex-1 px-3 py-2.5 bg-transparent border-0 rounded-xl text-sm resize-none focus:outline-none focus:ring-0 transition"
                                style="max-height:80px;">
                            </textarea>
                            <button @click="sendMsg()" :disabled="sending || !newMessage.trim()"
                                class="w-10 h-10 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white rounded-xl flex items-center justify-center transition flex-shrink-0 shadow-sm">
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
        const chatMyId   = {{ Auth::id() }};
        const chatCsrf   = document.querySelector("meta[name='csrf-token']")?.content;
        const chatIsUser = {{ $isUser ? 'true' : 'false' }};
        const chatUserId = {{ Auth::id() }};

        @verbatim
        document.addEventListener('alpine:init', () => {
            Alpine.data('chatWidget', () => ({
                isOpen:          false,
                currentConvId:   null,
                currentConv:     null,
                messages:        [],
                newMessage:      '',
                loading:         false,
                sending:         false,
                handling:        false,
                ending:          false,
                unreadTotal:     0,
                widgetConvs:     [],      // reactive conversation list
                convListLoading: true,
                seenMsgIds:      new Set(),
                lastMsgId:       0,
                pollTimer:       null,
                listPollTimer:   null,
                wsConnected:     false,

                // ── Fetch conversation list (the widget list) ──────────────────
                async fetchConvList() {
                    try {
                        const res  = await fetch('/conversations', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                        });
                        const data = await res.json();
                        if (Array.isArray(data)) {
                            this.widgetConvs = data;
                        } else if (data && data.id) {
                            this.widgetConvs = [data];
                        } else {
                            this.widgetConvs = [];
                        }
                        // Sync total unread from list
                        this.unreadTotal = this.widgetConvs.reduce((sum, c) => sum + (c.unread || 0), 0);
                    } catch(e) {}
                    this.convListLoading = false;
                },

                // ── Fetch unread count only (lightweight) ─────────────────────
                async fetchUnread() {
                    try {
                        const res  = await fetch('/conversations/unread/count', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                        });
                        const data = await res.json();
                        this.unreadTotal = data.count || 0;
                    } catch(e) {}
                },

                // ── Open conversation from widget list card ────────────────────
                openConvFromList(cv) {
                    const convInfo = chatIsUser
                        ? { name: 'Admin EcoDrop', photo: 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true', is_handled: cv.is_handled, is_closed: cv.is_closed, assigned_admin: cv.assigned_admin }
                        : { name: cv.user_name, photo: cv.user_photo || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(cv.user_name || 'User') + '&background=6366f1&color=fff&bold=true'), is_handled: cv.is_handled, is_closed: cv.is_closed, assigned_admin: cv.assigned_admin };
                    this.openConv(cv.id, convInfo);
                },

                // ── Start new chat (user only) ────────────────────────────────
                async startNewChat() {
                    try {
                        const res  = await fetch('/conversations', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                        });
                        const data = await res.json();
                        const convId = Array.isArray(data) ? null : data.id;
                        if (convId) {
                            this.openConv(convId, {
                                name: 'Admin EcoDrop',
                                photo: 'https://ui-avatars.com/api/?name=Admin+EcoDrop&background=10b981&color=fff&bold=true',
                                is_handled: data.is_handled,
                                is_closed:  data.is_closed,
                                assigned_admin: data.assigned_admin
                            });
                        }
                    } catch(e) {}
                },

                // ── Open a conversation ───────────────────────────────────────
                async openConv(convId, convInfo) {
                    // Leave any previous channel
                    if (this.currentConvId && typeof Echo !== 'undefined') {
                        Echo.leaveChannel(`conversation.${this.currentConvId}`);
                    }
                    this.stopMsgPoll();

                    this.currentConvId = convId;
                    this.currentConv   = convInfo;
                    this.messages      = [];
                    this.seenMsgIds    = new Set();
                    this.lastMsgId     = 0;
                    this.loading       = true;

                    // Load history
                    try {
                        const res  = await fetch(`/conversations/${convId}/messages`, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                        });
                        const msgs = await res.json();
                        this.messages = msgs;
                        msgs.forEach(m => {
                            this.seenMsgIds.add(m.id);
                            if (m.id > this.lastMsgId) this.lastMsgId = m.id;
                        });
                        // Mark as read → update unread badge
                        await this.fetchUnread();
                    } catch(e) {}

                    this.loading = false;
                    await this.$nextTick();
                    this.scrollBottom();

                    // ── Subscribe via WebSocket ─────────────────────────────
                    if (typeof Echo !== 'undefined') {
                        Echo.channel(`conversation.${convId}`)
                            .listen('.message.sent', (data) => {
                                if (data.sender_id === chatMyId) return; // own message already rendered
                                if (this.seenMsgIds.has(data.id)) return; // dedup
                                this.seenMsgIds.add(data.id);
                                if (data.id > this.lastMsgId) this.lastMsgId = data.id;

                                // Merge is_mine flag
                                this.messages.push({ ...data, is_mine: false });
                                this.$nextTick(() => this.scrollBottom());

                                // System message: update conv state
                                if (data.type === 'system') {
                                    if (data.content && data.content.includes('diakhiri') && this.currentConv) {
                                        this.currentConv.is_closed = true;
                                    }
                                    if (data.content && data.content.includes('dibuka kembali') && this.currentConv) {
                                        this.currentConv.is_closed  = false;
                                        this.currentConv.is_handled = false;
                                    }
                                }

                                // Decrement unread badge
                                this.unreadTotal = Math.max(0, this.unreadTotal - 1);
                            });
                    }

                    // ── Polling fallback ────────────────────────────────────
                    if (!this.wsConnected) {
                        this.startMsgPoll(convId);
                    }
                },

                // ── Close conversation ────────────────────────────────────────
                closeConv() {
                    if (typeof Echo !== 'undefined' && this.currentConvId) {
                        Echo.leaveChannel(`conversation.${this.currentConvId}`);
                    }
                    this.stopMsgPoll();
                    this.currentConvId = null;
                    this.currentConv   = null;
                    this.messages      = [];
                    this.seenMsgIds    = new Set();
                },

                // ── Handle (admin takes the conversation) ─────────────────────
                async handleConv() {
                    if (this.handling) return;
                    this.handling = true;
                    try {
                        const res  = await fetch(`/conversations/${this.currentConvId}/handle`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': chatCsrf
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.currentConv.is_handled    = true;
                            this.currentConv.is_closed     = false;
                            this.currentConv.assigned_admin = data.conversation?.assigned_admin;
                            const msg = { ...data.message, is_mine: true };
                            if (!this.seenMsgIds.has(msg.id)) {
                                this.seenMsgIds.add(msg.id);
                                this.messages.push(msg);
                                this.$nextTick(() => this.scrollBottom());
                            }
                        } else {
                            alert(data.message || 'Chat sudah diambil admin lain.');
                        }
                    } catch(e) {}
                    this.handling = false;
                },

                // ── End service ───────────────────────────────────────────────
                async endService() {
                    if (this.ending) return;
                    if (!confirm('Yakin ingin mengakhiri sesi layanan ini?')) return;
                    this.ending = true;
                    try {
                        const res  = await fetch(`/conversations/${this.currentConvId}/close`, {
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
                            const msg = { ...data.message, is_mine: true };
                            if (!this.seenMsgIds.has(msg.id)) {
                                this.seenMsgIds.add(msg.id);
                                this.messages.push(msg);
                                this.$nextTick(() => this.scrollBottom());
                            }
                        }
                    } catch(e) {}
                    this.ending = false;
                },

                // ── Send message ──────────────────────────────────────────────
                async sendMsg() {
                    if (!this.newMessage.trim() || this.sending) return;
                    this.sending = true;
                    const text = this.newMessage.trim();
                    this.newMessage = '';
                    try {
                        const res  = await fetch(`/conversations/${this.currentConvId}/messages`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': chatCsrf
                            },
                            body: JSON.stringify({ content: text })
                        });
                        const data = await res.json();
                        // Optimistic render with is_mine = true
                        const msg = { ...data, is_mine: true };
                        if (!this.seenMsgIds.has(msg.id)) {
                            this.seenMsgIds.add(msg.id);
                            if (msg.id > this.lastMsgId) this.lastMsgId = msg.id;
                            this.messages.push(msg);
                            this.$nextTick(() => this.scrollBottom());
                        }
                        // Auto-reopen state for user
                        if (chatIsUser && this.currentConv && this.currentConv.is_closed) {
                            this.currentConv.is_closed  = false;
                            this.currentConv.is_handled = false;
                        }
                    } catch(e) {
                        this.newMessage = text;
                    }
                    this.sending = false;
                },

                // ── Polling fallback for message area ─────────────────────────
                startMsgPoll(convId) {
                    if (this.pollTimer) return;
                    this.pollTimer = setInterval(async () => {
                        if (!this.currentConvId) return;
                        try {
                            const res  = await fetch(`/conversations/${convId}/messages`, {
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                            });
                            const msgs = await res.json();
                            let hasNew = false;
                            msgs.forEach(m => {
                                if (!this.seenMsgIds.has(m.id)) {
                                    this.seenMsgIds.add(m.id);
                                    if (m.id > this.lastMsgId) this.lastMsgId = m.id;
                                    this.messages.push({ ...m, is_mine: m.sender_id === chatMyId });
                                    hasNew = true;
                                }
                            });
                            if (hasNew) this.$nextTick(() => this.scrollBottom());
                        } catch(e) {}
                    }, 2000);
                },

                // ── Web Audio API Notification Sound (Synthesized, no files needed) ──
                playNotificationPing() {
                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        
                        // Sound 1 (high bell pitch)
                        const osc1 = audioCtx.createOscillator();
                        const gain1 = audioCtx.createGain();
                        osc1.type = 'sine';
                        osc1.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
                        gain1.gain.setValueAtTime(0.1, audioCtx.currentTime);
                        gain1.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
                        
                        osc1.connect(gain1);
                        gain1.connect(audioCtx.destination);
                        osc1.start();
                        osc1.stop(audioCtx.currentTime + 0.15);
                        
                        // Sound 2 (subtle delay bell)
                        setTimeout(() => {
                            const osc2 = audioCtx.createOscillator();
                            const gain2 = audioCtx.createGain();
                            osc2.type = 'sine';
                            osc2.frequency.setValueAtTime(1046.5, audioCtx.currentTime); // C6
                            gain2.gain.setValueAtTime(0.1, audioCtx.currentTime);
                            gain2.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.25);
                            
                            osc2.connect(gain2);
                            gain2.connect(audioCtx.destination);
                            osc2.start();
                            osc2.stop(audioCtx.currentTime + 0.25);
                        }, 80);
                    } catch (e) {
                        // Browser audio context block fallback
                    }
                },

                // ── Typing Whisper Status ────────────────────────────────────
                isPartnerTyping: false,
                typingTimeout:   null,

                sendTypingWhisper() {
                    if (typeof Echo !== 'undefined' && this.currentConvId) {
                        Echo.private(`conversation.${this.currentConvId}`)
                            .whisper('typing', {
                                sender_id: chatMyId,
                                is_typing: true
                            });
                    }
                },

                // ── Open a conversation ───────────────────────────────────────
                async openConv(convId, convInfo) {
                    // Leave any previous channel
                    if (this.currentConvId && typeof Echo !== 'undefined') {
                        Echo.leaveChannel(`conversation.${this.currentConvId}`);
                    }
                    this.stopMsgPoll();
                    this.isPartnerTyping = false;

                    this.currentConvId = convId;
                    this.currentConv   = convInfo;
                    this.messages      = [];
                    this.seenMsgIds    = new Set();
                    this.lastMsgId     = 0;
                    this.loading       = true;

                    // Load history
                    try {
                        const res  = await fetch(`/conversations/${convId}/messages`, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                        });
                        const msgs = await res.json();
                        this.messages = msgs;
                        msgs.forEach(m => {
                            this.seenMsgIds.add(m.id);
                            if (m.id > this.lastMsgId) this.lastMsgId = m.id;
                        });
                        // Mark as read → update unread badge
                        await this.fetchUnread();
                    } catch(e) {}

                    this.loading = false;
                    await this.$nextTick();
                    this.scrollBottom();

                    // ── Subscribe via WebSocket ─────────────────────────────
                    if (typeof Echo !== 'undefined') {
                        Echo.private(`conversation.${convId}`)
                            .listen('.message.sent', (data) => {
                                if (data.sender_id === chatMyId) return; // own message already rendered
                                if (this.seenMsgIds.has(data.id)) return; // dedup
                                this.seenMsgIds.add(data.id);
                                if (data.id > this.lastMsgId) this.lastMsgId = data.id;

                                // Play high-quality sound ping
                                this.playNotificationPing();

                                // Merge is_mine flag
                                this.messages.push({ ...data, is_mine: false });
                                this.$nextTick(() => this.scrollBottom());

                                // System message: update conv state
                                if (data.type === 'system') {
                                    if (data.content && data.content.includes('diakhiri') && this.currentConv) {
                                        this.currentConv.is_closed = true;
                                    }
                                    if (data.content && data.content.includes('dibuka kembali') && this.currentConv) {
                                        this.currentConv.is_closed  = false;
                                        this.currentConv.is_handled = false;
                                    }
                                }

                                // Decrement unread badge
                                this.unreadTotal = Math.max(0, this.unreadTotal - 1);
                                this.isPartnerTyping = false; // message received, stop typing indicator
                            })
                            .listenForWhisper('typing', (e) => {
                                if (e.sender_id !== chatMyId) {
                                    this.isPartnerTyping = e.is_typing;
                                    
                                    // Clear existing timeout
                                    if (this.typingTimeout) clearTimeout(this.typingTimeout);
                                    
                                    // Auto stop typing indicator after 3 seconds of inactivity
                                    this.typingTimeout = setTimeout(() => {
                                        this.isPartnerTyping = false;
                                    }, 3000);
                                    
                                    this.$nextTick(() => this.scrollBottom());
                                }
                            });
                    }

                    // ── Polling fallback ────────────────────────────────────
                    if (!this.wsConnected) {
                        this.startMsgPoll(convId);
                    }
                },

                // ── Close conversation ────────────────────────────────────────
                closeConv() {
                    if (typeof Echo !== 'undefined' && this.currentConvId) {
                        Echo.leaveChannel(`conversation.${this.currentConvId}`);
                    }
                    this.stopMsgPoll();
                    this.currentConvId = null;
                    this.currentConv   = null;
                    this.messages      = [];
                    this.seenMsgIds    = new Set();
                    this.isPartnerTyping = false;
                },

                // ── Handle (admin takes the conversation) ─────────────────────
                async handleConv() {
                    if (this.handling) return;
                    this.handling = true;
                    try {
                        const res  = await fetch(`/conversations/${this.currentConvId}/handle`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': chatCsrf
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.currentConv.is_handled    = true;
                            this.currentConv.is_closed     = false;
                            this.currentConv.assigned_admin = data.conversation?.assigned_admin;
                            const msg = { ...data.message, is_mine: true };
                            if (!this.seenMsgIds.has(msg.id)) {
                                this.seenMsgIds.add(msg.id);
                                this.messages.push(msg);
                                this.$nextTick(() => this.scrollBottom());
                            }
                        } else {
                            alert(data.message || 'Chat sudah diambil admin lain.');
                        }
                    } catch(e) {}
                    this.handling = false;
                },

                // ── End service ───────────────────────────────────────────────
                async endService() {
                    if (this.ending) return;
                    if (!confirm('Yakin ingin mengakhiri sesi layanan ini?')) return;
                    this.ending = true;
                    try {
                        const res  = await fetch(`/conversations/${this.currentConvId}/close`, {
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
                            const msg = { ...data.message, is_mine: true };
                            if (!this.seenMsgIds.has(msg.id)) {
                                this.seenMsgIds.add(msg.id);
                                this.messages.push(msg);
                                this.$nextTick(() => this.scrollBottom());
                            }
                        }
                    } catch(e) {}
                    this.ending = false;
                },

                // ── Send message ──────────────────────────────────────────────
                async sendMsg() {
                    if (!this.newMessage.trim() || this.sending) return;
                    this.sending = true;
                    const text = this.newMessage.trim();
                    this.newMessage = '';
                    try {
                        const res  = await fetch(`/conversations/${this.currentConvId}/messages`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': chatCsrf
                            },
                            body: JSON.stringify({ content: text })
                        });
                        const data = await res.json();
                        // Optimistic render with is_mine = true
                        const msg = { ...data, is_mine: true };
                        if (!this.seenMsgIds.has(msg.id)) {
                            this.seenMsgIds.add(msg.id);
                            if (msg.id > this.lastMsgId) this.lastMsgId = msg.id;
                            this.messages.push(msg);
                            this.$nextTick(() => this.scrollBottom());
                        }
                        // Auto-reopen state for user
                        if (chatIsUser && this.currentConv && this.currentConv.is_closed) {
                            this.currentConv.is_closed  = false;
                            this.currentConv.is_handled = false;
                        }
                    } catch(e) {
                        this.newMessage = text;
                    }
                    this.sending = false;
                },

                // ── Polling fallback for message area ─────────────────────────
                startMsgPoll(convId) {
                    if (this.pollTimer) return;
                    this.pollTimer = setInterval(async () => {
                        if (!this.currentConvId) return;
                        try {
                            const res  = await fetch(`/conversations/${convId}/messages`, {
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatCsrf }
                            });
                            const msgs = await res.json();
                            let hasNew = false;
                            msgs.forEach(m => {
                                if (!this.seenMsgIds.has(m.id)) {
                                    this.seenMsgIds.add(m.id);
                                    if (m.id > this.lastMsgId) this.lastMsgId = m.id;
                                    this.messages.push({ ...m, is_mine: m.sender_id === chatMyId });
                                    hasNew = true;
                                    
                                    // Play sound on polling update only if the widget/conv is active
                                    if (m.sender_id !== chatMyId) {
                                        this.playNotificationPing();
                                    }
                                }
                            });
                            if (hasNew) this.$nextTick(() => this.scrollBottom());
                        } catch(e) {}
                    }, 2000);
                },

                stopMsgPoll() {
                    if (this.pollTimer) { clearInterval(this.pollTimer); this.pollTimer = null; }
                },

                // ── Scroll to bottom ──────────────────────────────────────────
                scrollBottom() {
                    const el = document.getElementById('chatMessages');
                    el && (el.scrollTop = el.scrollHeight);
                },

                // ── Alpine init ───────────────────────────────────────────────
                init() {
                    // Load conversation list + unread on startup
                    this.fetchConvList();

                    // ── WebSocket: update list on ConversationListUpdated ─────
                    if (typeof Echo !== 'undefined') {
                        if (chatIsUser) {
                            Echo.channel('user-updates.' + chatUserId)
                                .listen('.conversation.updated', () => {
                                    this.fetchConvList();
                                    this.playNotificationPing();
                                });
                        } else {
                            Echo.channel('chat-admin-updates')
                                .listen('.conversation.updated', () => {
                                    this.fetchConvList();
                                    this.playNotificationPing();
                                });
                        }
                    }

                    // ── React to WS connection state ────────────────────────
                    document.addEventListener('echo:connected', () => {
                        this.wsConnected = true;
                        this.stopMsgPoll();
                        if (this.listPollTimer) { clearInterval(this.listPollTimer); this.listPollTimer = null; }
                    });
                    document.addEventListener('echo:disconnected', () => {
                        this.wsConnected = false;
                        if (this.currentConvId) this.startMsgPoll(this.currentConvId);
                        // Polling fallback for conversation list (every 3s)
                        if (!this.listPollTimer) {
                            this.listPollTimer = setInterval(() => this.fetchConvList(), 3000);
                        }
                    });

                    if (window.__echoConnected === true) {
                        this.wsConnected = true;
                    } else {
                        // Fallback polling until WS connects
                        this.listPollTimer = setInterval(() => {
                            if (this.wsConnected) {
                                clearInterval(this.listPollTimer);
                                this.listPollTimer = null;
                                return;
                            }
                            this.fetchConvList();
                        }, 3000);
                    }
                }
            }));
        });
        @endverbatim
        </script>
        @endauth
    </body>
</html>


