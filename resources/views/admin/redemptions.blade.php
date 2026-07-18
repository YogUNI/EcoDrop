<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-black text-xl text-gray-900">Verifikasi Klaim Voucher Hadiah 🎟️</h2>
            <p class="text-xs text-gray-400 mt-0.5">Validasi kode voucher penukaran poin milik pengguna</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-transparent py-8 relative">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 space-y-6">

            {{-- Flash messages --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl shadow-sm flex justify-between items-center">
                    <span class="font-bold text-sm">{{ session('success') }}</span>
                    <button @click="show = false" class="text-green-600 hover:text-green-800 transition">✕</button>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl shadow-sm flex justify-between items-center">
                    <span class="font-bold text-sm">{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-600 hover:text-red-800 transition">✕</button>
                </div>
            @endif

            {{-- DAFTAR KLAIM KESELURUHAN --}}
            <div class="bg-white rounded-[28px] border border-gray-100 shadow-sm p-6 space-y-5">
                <div class="flex justify-between items-center flex-wrap gap-3">
                    <h3 class="text-lg font-black text-gray-900">Semua Permohonan Penukaran Hadiah</h3>
                </div>

                @if($redemptions->count() > 0)
                    <div class="overflow-x-auto rounded-2xl border border-gray-100">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-400 border-b border-gray-100 font-bold uppercase tracking-wider">
                                    <th class="p-4">Tanggal Pengajuan</th>
                                    <th class="p-4">Pengguna</th>
                                    <th class="p-4">Item Hadiah</th>
                                    <th class="p-4 text-center">Debet Poin</th>
                                    <th class="p-4 text-center">Kode Voucher</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-center">Aksi / Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                                @foreach($redemptions as $redemption)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="p-4 whitespace-nowrap text-gray-400">
                                            {{ $redemption->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="p-4">
                                            <div class="font-bold text-gray-900">{{ $redemption->user->name }}</div>
                                            <div class="text-[10px] text-gray-400 font-medium">{{ $redemption->user->email }}</div>
                                        </td>
                                        <td class="p-4">
                                            <div class="font-bold text-gray-900">{{ $redemption->reward->name }}</div>
                                        </td>
                                        <td class="p-4 text-center text-red-500 font-bold">
                                            -{{ $redemption->points_spent }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <code class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-mono font-bold tracking-wider select-all cursor-pointer">
                                                {{ $redemption->unique_code }}
                                            </code>
                                        </td>
                                        <td class="p-4 text-center">
                                            @if($redemption->status === 'pending')
                                                <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700 border border-yellow-100">⏳ Pending</span>
                                            @elseif($redemption->status === 'completed')
                                                <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-100">✅ Selesai</span>
                                            @else
                                                <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-600 border border-red-100">❌ Dibatalkan</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-center whitespace-nowrap">
                                            @if($redemption->status === 'pending')
                                                @php
                                                    $updateRoute = Auth::user()->role === 'super_admin' 
                                                        ? 'superadmin.redemptions.update' 
                                                        : 'admin.redemptions.update';
                                                @endphp
                                                <div class="flex items-center justify-center gap-2">
                                                    {{-- SELESAIKAN --}}
                                                    <form action="{{ route($updateRoute, $redemption->id) }}" method="POST"
                                                          onsubmit="return confirm('Selesaikan penukaran ini? Pastikan hadiah sudah diserahkan ke user.');">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-[10px] font-bold shadow-sm transition active:scale-95">
                                                            Selesaikan Hadiah
                                                        </button>
                                                    </form>
                                                    
                                                    {{-- BATALKAN --}}
                                                    <form action="{{ route($updateRoute, $redemption->id) }}" method="POST"
                                                          onsubmit="return confirm('Batalkan penukaran voucher ini? Poin akan dikembalikan penuh ke saldo user.');">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="canceled">
                                                        <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-500 rounded-lg text-[10px] font-bold transition active:scale-95">
                                                            Tolak/Batal
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-[11px]">Tidak ada aksi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-16 px-6 border border-dashed border-gray-100 rounded-3xl">
                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-3xl">🎟️</span>
                        </div>
                        <p class="text-gray-500 font-bold text-sm">Belum ada antrean klaim voucher.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
