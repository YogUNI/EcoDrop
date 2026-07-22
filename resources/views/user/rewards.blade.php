<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-200">
                    <span class="text-2xl">🎁</span>
                </div>
                <div>
                    <h2 class="font-black text-xl text-gray-900 leading-tight">Katalog Hadiah &amp; Tukar Poin</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Tukarkan poin EcoDrop Anda dengan berbagai hadiah menarik</p>
                </div>
            </div>

            {{-- Premium Points Badge --}}
            <div class="relative flex items-center gap-2.5 bg-gradient-to-r from-amber-400 via-orange-500 to-orange-600 text-white rounded-2xl px-5 py-2.5 shadow-lg shadow-orange-200 overflow-hidden">
                {{-- Shine effect --}}
                <div class="absolute inset-0 bg-white/10 skew-x-12 -translate-x-full animate-none pointer-events-none"></div>
                <svg class="w-4 h-4 opacity-90 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs font-bold opacity-90 tracking-wide">Poin Anda</span>
                <span class="text-2xl font-black tracking-tight animate-pulse" id="userPointsBadge">{{ Auth::user()->points }}</span>
            </div>
        </div>
    </x-slot>

    {{-- ══════════════════════════════════════════════════
         CONFIRMATION MODAL
    ══════════════════════════════════════════════════ --}}
    <div id="redeemModal" class="hidden fixed inset-0 z-[300] flex items-end sm:items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4"
         onclick="if(event.target===this) closeRedeemModal()">
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-gray-100 overflow-hidden animate-[slideUp_0.25s_ease-out]">

            {{-- Modal Header --}}
            <div class="relative bg-gradient-to-br from-amber-400 via-orange-500 to-orange-600 px-6 pt-8 pb-7 text-white">
                {{-- Background decoration --}}
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-20 h-20 bg-black/5 rounded-full translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>

                {{-- Close Button --}}
                <button onclick="closeRedeemModal()"
                    class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition backdrop-blur-sm"
                    aria-label="Tutup modal">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="text-5xl mb-3 relative z-10" id="modalRewardEmoji">🎁</div>
                <h3 class="font-black text-xl leading-tight relative z-10" id="modalRewardName">Nama Hadiah</h3>
                <p class="text-white/80 text-sm mt-1.5 leading-relaxed relative z-10" id="modalRewardDesc"></p>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-3">

                {{-- Points Cost Card --}}
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-amber-700">Poin dibutuhkan</span>
                    </div>
                    <span class="font-black text-amber-600 text-xl" id="modalPointsCost">-</span>
                </div>

                {{-- Points After Card --}}
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-slate-50 rounded-2xl border border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-gray-600">Sisa poin setelah tukar</span>
                    </div>
                    <span class="font-black text-gray-800 text-xl" id="modalPointsAfter">-</span>
                </div>

                <p class="text-xs text-gray-400 text-center leading-relaxed px-2">
                    Setelah konfirmasi, silakan tunjukkan kode voucher unik kepada petugas admin saat penjemputan untuk penyelesaian.
                </p>

                <form id="redeemForm" method="POST" class="space-y-3 pt-1">
                    @csrf
                    <button type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-black text-sm rounded-2xl shadow-lg shadow-emerald-100 transition active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Ya, Tukar Sekarang!
                    </button>
                </form>

                <button onclick="closeRedeemModal()"
                    class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-2xl transition">
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MAIN CONTENT
    ══════════════════════════════════════════════════ --}}
    <div class="min-h-screen bg-transparent py-6 relative">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-6">

            {{-- Flash Toast --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
                     class="relative flex items-center gap-3.5 p-4 bg-white border border-emerald-200 text-emerald-800 rounded-2xl shadow-xl shadow-emerald-50 overflow-hidden"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2">
                    {{-- Green sidebar accent --}}
                    <div class="absolute left-0 inset-y-0 w-1 bg-gradient-to-b from-emerald-400 to-teal-500 rounded-l-2xl"></div>
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center flex-shrink-0 shadow-md shadow-emerald-100">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="font-bold text-sm flex-1">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition ml-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- ═══ 1. KATALOG REWARD ═══ --}}
            <div class="space-y-4">

                {{-- Section Header --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md shadow-amber-100">
                            <span class="text-base leading-none">🛍️</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest leading-tight">Hadiah Tersedia</h3>
                            <p class="text-[10px] text-gray-400 mt-0.5">Pilih hadiah dan tukarkan poin Anda</p>
                        </div>
                    </div>
                    @if($rewards->count() > 0)
                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-100 px-3 py-1 rounded-full">
                            {{ $rewards->count() }} Hadiah
                        </span>
                    @endif
                </div>

                @if($rewards->count() > 0)
                    {{-- Vertical Card Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-5">
                        @foreach($rewards as $reward)
                            @php
                                $userPoints  = Auth::user()->points;
                                $canRedeem   = $userPoints >= $reward->points_required && $reward->isAvailable();
                                $stockText   = is_null($reward->stock) ? 'Unlimited' : $reward->stock . ' tersisa';
                                $stockColor  = is_null($reward->stock)
                                    ? 'text-emerald-600 bg-emerald-50 border-emerald-100'
                                    : ($reward->stock > 5 ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : ($reward->stock > 0 ? 'text-amber-600 bg-amber-50 border-amber-100' : 'text-red-600 bg-red-50 border-red-100'));
                            @endphp

                            {{-- Vertical Card --}}
                            <div class="group bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition duration-200 overflow-hidden flex flex-col">

                                {{-- Image Area --}}
                                <div class="relative h-36 bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden rounded-t-2xl flex-shrink-0">
                                    @if($reward->image)
                                        <img src="{{ asset('storage/' . $reward->image) }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500 {{ !$reward->isAvailable() ? 'blur-[2px] scale-105' : '' }}"
                                             alt="{{ $reward->name }}">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <span class="text-5xl opacity-50">🎁</span>
                                        </div>
                                    @endif

                                    {{-- Habis Overlay --}}
                                    @if(!$reward->isAvailable())
                                        <div class="absolute inset-0 bg-gray-900/55 flex items-center justify-center">
                                            <span class="text-[11px] text-white font-extrabold uppercase tracking-widest bg-black/30 px-3 py-1 rounded-full backdrop-blur-sm">Habis</span>
                                        </div>
                                    @endif

                                    {{-- Stock Badge overlay on image --}}
                                    <div class="absolute top-2 left-2">
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border {{ $stockColor }} backdrop-blur-sm bg-opacity-90">
                                            {{ $stockText }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Card Body --}}
                                <div class="p-4 flex flex-col flex-1">
                                    <h4 class="font-black text-base text-gray-900 leading-tight line-clamp-1">{{ $reward->name }}</h4>
                                    <p class="text-xs text-gray-400 line-clamp-2 mt-1 leading-relaxed flex-1">{{ $reward->description ?? 'Tukarkan poin dengan item eksklusif ini.' }}</p>

                                    <div class="mt-3 pt-3 border-t border-gray-50">
                                        {{-- Points Price --}}
                                        <div class="flex items-baseline gap-1 mb-3">
                                            <span class="font-black text-lg text-emerald-600 leading-none">{{ number_format($reward->points_required) }}</span>
                                            <span class="text-[10px] font-bold text-emerald-500">poin</span>
                                        </div>

                                        {{-- Action Button --}}
                                        @if($reward->isAvailable())
                                            @if($canRedeem)
                                                <button
                                                    onclick="openRedeemModal({{ $reward->id }}, '{{ addslashes($reward->name) }}', '{{ addslashes($reward->description ?? '') }}', {{ $reward->points_required }}, {{ $userPoints }})"
                                                    class="w-full py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold text-xs shadow-md shadow-emerald-100 transition active:scale-95 flex items-center justify-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                    </svg>
                                                    Tukar Sekarang
                                                </button>
                                            @else
                                                <button disabled
                                                    class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-400 font-bold text-xs cursor-not-allowed flex items-center justify-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                    Kurang Poin
                                                </button>
                                            @endif
                                        @else
                                            <button disabled
                                                class="w-full py-2.5 rounded-xl bg-red-50 text-red-400 font-bold text-xs cursor-not-allowed">
                                                Tidak Tersedia
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Empty State Rewards --}}
                    <div class="text-center py-16 bg-white border border-dashed border-gray-200 rounded-2xl">
                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <span class="text-3xl">📭</span>
                        </div>
                        <p class="text-gray-700 font-bold text-sm">Belum ada hadiah tersedia</p>
                        <p class="text-gray-400 text-xs mt-1">Pantau terus untuk update hadiah terbaru!</p>
                    </div>
                @endif
            </div>

            {{-- ═══ 2. RIWAYAT PENUKARAN ═══ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                {{-- Section Header --}}
                <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between bg-gradient-to-r from-gray-50/80 to-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest leading-tight">Riwayat Penukaran Saya</h3>
                            <p class="text-[10px] text-gray-400 mt-0.5">Semua transaksi penukaran poin</p>
                        </div>
                    </div>
                    @if($redemptions->count() > 0)
                        <span class="text-[10px] font-bold text-slate-600 bg-slate-50 border border-slate-100 px-3 py-1 rounded-full">
                            {{ $redemptions->count() }} Transaksi
                        </span>
                    @endif
                </div>

                <div class="p-5">
                    @if($redemptions->count() > 0)
                        <div class="divide-y divide-gray-50/80 space-y-0">
                            @foreach($redemptions as $redemption)
                                <div class="flex items-start gap-3.5 py-4 first:pt-0 last:pb-0">

                                    {{-- Status Icon --}}
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm
                                        @if($redemption->status === 'completed') bg-gradient-to-br from-emerald-400 to-teal-500
                                        @elseif($redemption->status === 'canceled') bg-gradient-to-br from-red-400 to-rose-500
                                        @else bg-gradient-to-br from-yellow-400 to-amber-500 @endif">
                                        <span class="text-white font-black text-sm">
                                            @if($redemption->status === 'completed') ✓
                                            @elseif($redemption->status === 'canceled') ✕
                                            @else ⏳
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Redemption Info --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="font-extrabold text-gray-900 text-sm truncate leading-tight">{{ $redemption->reward->name }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $redemption->created_at->format('d M, H:i') }}</p>

                                        {{-- Voucher Code Box --}}
                                        <div class="mt-2 flex items-center gap-2">
                                            <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-100 rounded-lg px-2.5 py-1.5 cursor-pointer hover:bg-gray-100 transition group/copy"
                                                 title="Klik untuk salin"
                                                 onclick="navigator.clipboard.writeText('{{ $redemption->unique_code }}').then(()=>alert('Voucher disalin!'))">
                                                <svg class="w-3 h-3 text-gray-400 group-hover/copy:text-gray-600 transition flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                <code class="text-[10px] font-mono font-bold text-gray-600 tracking-wider select-all">{{ $redemption->unique_code }}</code>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Points & Status --}}
                                    <div class="flex-shrink-0 text-right">
                                        <p class="font-black text-red-500 text-sm">-{{ number_format($redemption->points_spent) }}</p>
                                        <p class="text-[9px] text-gray-400 font-medium">poin</p>

                                        {{-- Status Pill --}}
                                        <span class="inline-block text-[9px] font-extrabold px-2.5 py-0.5 rounded-full mt-1.5
                                            @if($redemption->status === 'completed') bg-emerald-100 text-emerald-700
                                            @elseif($redemption->status === 'canceled') bg-red-100 text-red-600
                                            @else bg-yellow-100 text-yellow-700 @endif">
                                            {{ $redemption->status === 'completed' ? 'Selesai' : ($redemption->status === 'canceled' ? 'Dibatalkan' : 'Pending') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Empty State Redemptions --}}
                        <div class="text-center py-10">
                            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-gray-600 font-bold text-sm">Belum ada riwayat penukaran</p>
                            <p class="text-gray-400 text-xs mt-1">Tukarkan poin Anda dengan hadiah di atas!</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        function openRedeemModal(rewardId, name, desc, cost, userPoints) {
            document.getElementById('modalRewardName').textContent  = name;
            document.getElementById('modalRewardDesc').textContent  = desc || 'Tukarkan poin Anda dengan voucher ini.';
            document.getElementById('modalPointsCost').textContent  = cost.toLocaleString('id-ID') + ' poin';
            document.getElementById('modalPointsAfter').textContent = (userPoints - cost).toLocaleString('id-ID') + ' poin';
            document.getElementById('redeemForm').action = '/user/rewards/' + rewardId + '/redeem';
            document.getElementById('redeemModal').classList.remove('hidden');
        }
        function closeRedeemModal() {
            document.getElementById('redeemModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
