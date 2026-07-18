<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md">
                    <span class="text-xl">🎁</span>
                </div>
                <div>
                    <h2 class="font-black text-xl text-gray-900">Katalog Hadiah & Tukar Poin</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Tukarkan poin EcoDrop Anda dengan berbagai hadiah menarik</p>
                </div>
            </div>
            {{-- Points Badge --}}
            <div class="flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-2xl px-4 py-2 shadow-md shadow-emerald-100">
                <svg class="w-4 h-4 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs font-bold opacity-90">Poin Anda:</span>
                <span class="text-xl font-black" id="userPointsBadge">{{ Auth::user()->points }}</span>
            </div>
        </div>
    </x-slot>

    {{-- Confirmation Modal --}}
    <div id="redeemModal" class="hidden fixed inset-0 z-[300] flex items-end sm:items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
         onclick="if(event.target===this) closeRedeemModal()">
        <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-gray-100 overflow-hidden animate-[slideUp_0.25s_ease-out]">
            <div class="bg-gradient-to-r from-amber-400 to-orange-500 px-6 py-5 text-white">
                <div class="text-3xl mb-2" id="modalRewardEmoji">🎁</div>
                <h3 class="font-black text-xl" id="modalRewardName">Nama Hadiah</h3>
                <p class="text-white/80 text-sm mt-1" id="modalRewardDesc"></p>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between p-4 bg-amber-50 rounded-2xl border border-amber-100">
                    <span class="text-sm font-bold text-amber-700">Poin dibutuhkan</span>
                    <span class="font-black text-amber-600 text-lg" id="modalPointsCost">-</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <span class="text-sm font-bold text-gray-600">Poin Anda setelah tukar</span>
                    <span class="font-black text-gray-800 text-lg" id="modalPointsAfter">-</span>
                </div>
                <p class="text-xs text-gray-400 text-center leading-relaxed">
                    Setelah konfirmasi, silakan tunjukkan kode voucher unik kepada petugas admin saat penjemputan untuk penyelesaian.
                </p>
                <form id="redeemForm" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-black text-sm rounded-2xl shadow-lg shadow-emerald-100 transition active:scale-95">
                        Yes, Tukar Sekarang!
                    </button>
                </form>
                <button onclick="closeRedeemModal()"
                    class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-2xl transition">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <div class="min-h-screen bg-transparent py-6 relative">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-6">

            {{-- Flash messages --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
                     class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm">
                    <span class="text-lg">✅</span>
                    <span class="font-bold text-sm flex-1">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition text-lg leading-none">✕</button>
                </div>
            @endif

            {{-- ═══ 1. KATALOG REWARD (COMPACT STYLE) ═══ --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-md font-black text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                        <span>🛍️ Hadiah Tersedia</span>
                    </h3>
                </div>

                @if($rewards->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($rewards as $reward)
                            @php
                                $userPoints  = Auth::user()->points;
                                $canRedeem   = $userPoints >= $reward->points_required && $reward->isAvailable();
                                $stockText   = is_null($reward->stock) ? 'Unlimited' : $reward->stock . ' tersisa';
                                $stockColor  = is_null($reward->stock)
                                    ? 'text-emerald-600 bg-emerald-50 border-emerald-100'
                                    : ($reward->stock > 5 ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : ($reward->stock > 0 ? 'text-amber-600 bg-amber-50 border-amber-100' : 'text-red-600 bg-red-50 border-red-100'));
                            @endphp
                            {{-- Premium Compact Horizontal Layout --}}
                            <div class="group bg-white border border-gray-100 rounded-2xl p-4 flex gap-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition duration-200 relative overflow-hidden">
                                {{-- Thumbnail Image --}}
                                <div class="w-20 h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl overflow-hidden border border-gray-100 flex-shrink-0 flex items-center justify-center relative">
                                    @if($reward->image)
                                        <img src="{{ asset('storage/' . $reward->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="{{ $reward->name }}">
                                    @else
                                        <span class="text-3xl">🎁</span>
                                    @endif

                                    @if(!$reward->isAvailable())
                                        <div class="absolute inset-0 bg-gray-900/60 flex items-center justify-center">
                                            <span class="text-[9px] text-white font-extrabold uppercase tracking-wide">Habis</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div class="flex-1 flex flex-col justify-between min-w-0">
                                    <div>
                                        <div class="flex items-start justify-between gap-1.5">
                                            <h4 class="font-extrabold text-gray-900 text-sm truncate leading-tight">{{ $reward->name }}</h4>
                                        </div>
                                        <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5 leading-relaxed">{{ $reward->description ?? 'Tukarkan poin dengan item ini.' }}</p>
                                        
                                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border {{ $stockColor }}">
                                                {{ $stockText }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-50">
                                        <span class="font-black text-emerald-600 text-sm">{{ number_format($reward->points_required) }} <span class="text-[10px] font-bold">poin</span></span>

                                        @if($reward->isAvailable())
                                            @if($canRedeem)
                                                <button
                                                    onclick="openRedeemModal({{ $reward->id }}, '{{ addslashes($reward->name) }}', '{{ addslashes($reward->description ?? '') }}', {{ $reward->points_required }}, {{ $userPoints }})"
                                                    class="px-3.5 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-[11px] font-black rounded-xl shadow-sm transition active:scale-95">
                                                    Tukar
                                                </button>
                                            @else
                                                <div class="flex flex-col items-end">
                                                    <button disabled class="px-3.5 py-1.5 bg-gray-50 text-gray-400 text-[11px] font-black rounded-xl cursor-not-allowed">
                                                        Kurang Poin
                                                    </button>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-[10px] text-red-500 font-extrabold uppercase">Tidak Tersedia</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-white border border-dashed border-gray-150 rounded-2xl">
                        <span class="text-3xl">📭</span>
                        <p class="text-gray-500 font-bold text-xs mt-2">Belum ada item hadiah tersedia.</p>
                    </div>
                @endif
            </div>

            {{-- ═══ 2. RIWAYAT PENUKARAN ═══ --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                    <span>📋 Riwayat Penukaran Saya</span>
                </h3>

                @if($redemptions->count() > 0)
                    <div class="divide-y divide-gray-50">
                        @foreach($redemptions as $redemption)
                            <div class="flex items-center gap-3 py-3.5 first:pt-0 last:pb-0">
                                {{-- Status Circle --}}
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                                    @if($redemption->status === 'completed') bg-emerald-50 text-emerald-600 @elseif($redemption->status === 'canceled') bg-red-50 text-red-500 @else bg-yellow-50 text-yellow-600 @endif">
                                    <span class="text-sm">
                                        @if($redemption->status === 'completed') ✓
                                        @elseif($redemption->status === 'canceled') ✕
                                        @else ⏳
                                        @endif
                                    </span>
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="font-extrabold text-gray-900 text-xs truncate">{{ $redemption->reward->name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <code class="px-2 py-0.5 bg-gray-50 text-gray-600 rounded text-[10px] font-mono font-bold tracking-wider select-all cursor-pointer border border-gray-100"
                                              title="Klik untuk salin" onclick="navigator.clipboard.writeText('{{ $redemption->unique_code }}').then(()=>alert('Voucher disalin!'))">
                                            {{ $redemption->unique_code }}
                                        </code>
                                        <span class="text-[10px] text-gray-400">{{ $redemption->created_at->format('d M, H:i') }}</span>
                                    </div>
                                </div>

                                {{-- Poin --}}
                                <div class="flex-shrink-0 text-right">
                                    <p class="font-black text-red-500 text-xs">-{{ number_format($redemption->points_spent) }} Poin</p>
                                    <span class="text-[9px] font-bold block mt-0.5
                                        @if($redemption->status === 'completed') text-emerald-600 @elseif($redemption->status === 'canceled') text-red-500 @else text-yellow-600 @endif">
                                        {{ $redemption->status === 'completed' ? 'Selesai' : ($redemption->status === 'canceled' ? 'Dibatalkan' : 'Pending') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-400 text-xs">
                        Belum ada riwayat penukaran hadiah.
                    </div>
                @endif
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
