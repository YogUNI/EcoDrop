<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-xl text-gray-900">Halo, {{ Auth::user()->name }} 👋</h2>
                <p class="text-xs text-gray-400 mt-0.5">Selamat datang kembali di EcoDrop</p>
            </div>
        </div>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <div x-data="{
            isModalOpen: {{ $errors->any() ? 'true' : 'false' }},
            isDetailOpen: false,
            detail: null,
            photoPreview: null,
            photoName: '',
            handlePhoto(e) {
                const file = e.target.files[0];
                if (!file) return;
                this.photoName = file.name;
                const reader = new FileReader();
                reader.onload = (ev) => { this.photoPreview = ev.target.result; };
                reader.readAsDataURL(file);
            },
            openDetail(data) { this.detail = data; this.isDetailOpen = true; }
         }"
         class="min-h-screen bg-transparent py-8 relative">

        <div class="max-w-2xl mx-auto px-4 sm:px-6 relative z-10 space-y-6">

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl shadow-sm flex justify-between items-center">
                    <span class="font-bold text-sm">{{ session('success') }}</span>
                    <button @click="show = false" class="text-green-600 hover:text-green-800 transition">✕</button>
                </div>
            @endif

            {{-- HERO CARD (Poin) - MATCHING FIGMA --}}
            <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-[24px] p-8 shadow-lg shadow-emerald-500/10 relative overflow-hidden text-white">
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white/80 text-xs font-semibold">Total Poin Anda</p>
                            <p class="text-white font-black text-5xl mt-1 leading-none">{{ Auth::user()->points }}</p>
                            <p class="text-white/70 text-xs mt-3 leading-relaxed max-w-sm">Tukarkan poin Anda dengan hadiah menarik atau saldo digital</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 mt-8 pt-6 border-t border-white/10">
                    <div class="flex items-center gap-2">
                        <button @click="isModalOpen = true"
                            class="flex items-center gap-2 bg-white text-emerald-700 font-bold text-sm px-5 py-3 rounded-2xl hover:bg-emerald-50 transition active:scale-[0.98] shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Setor Sampah
                        </button>
                        <a href="{{ route('rewards.index') }}"
                            class="flex items-center gap-2 bg-emerald-600/50 hover:bg-emerald-600 border border-white/20 text-white font-bold text-sm px-5 py-3 rounded-2xl transition active:scale-[0.98] shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0h4m-4 0h-4m0 0v13m0 13h12"></path>
                            </svg>
                            Tukar Poin
                        </a>
                    </div>
                    <div class="flex items-center gap-6 text-white/80 text-xs font-semibold">
                        <div class="text-center">
                            <span class="text-lg font-black text-white">{{ $pickups->where('status','approved')->count() }}</span>
                            <p class="text-[10px] text-white/60 font-medium">Disetujui</p>
                        </div>
                        <div class="w-px h-6 bg-white/10"></div>
                        <div class="text-center">
                            <span class="text-lg font-black text-white">{{ $pickups->where('status','pending')->count() }}</span>
                            <p class="text-[10px] text-white/60 font-medium">Pending</p>
                        </div>
                        <div class="w-px h-6 bg-white/10"></div>
                        <div class="text-center">
                            <span class="text-lg font-black text-white">{{ $pickups->count() }}</span>
                            <p class="text-[10px] text-white/60 font-medium">Total</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIWAYAT SETORAN - MATCHING FIGMA --}}
            <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 space-y-5">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Riwayat Setoran</h3>
                </div>

                @if($pickups->count() > 0)
                    <div class="space-y-3">
                        @foreach($pickups as $pickup)
                            @php
                                $detailData = json_encode([
                                    'id'          => $pickup->id,
                                    'type'        => $pickup->type,
                                    'weight'      => $pickup->weight,
                                    'pickup_date' => \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y'),
                                    'address'     => $pickup->address,
                                    'phone'       => $pickup->phone,
                                    'status'      => $pickup->status,
                                    'points'      => $pickup->points_earned,
                                    'notes'       => $pickup->notes,
                                    'photo'       => $pickup->photo ? Storage::url($pickup->photo) : null,
                                    'latitude'    => $pickup->latitude,
                                    'longitude'   => $pickup->longitude,
                                    'handled_by'  => $pickup->handledBy?->name,
                                    'maps_url'    => $pickup->latitude ? "https://maps.google.com/?q={$pickup->latitude},{$pickup->longitude}" : null,
                                ]);
                                $typeIcons = [
                                    'Plastik' => '🥤', 'Kertas' => '📄', 'Logam' => '🔧',
                                    'Kaca' => '🥛', 'Organik' => '🍂', 'Elektronik' => '⚡', 'Lainnya' => '📦'
                                ];
                                $icon = $typeIcons[$pickup->type] ?? '♻️';
                            @endphp
                            
                            {{-- PEMBUNGKUS BARIS ADALAH DIV (ANTI LAYOUT CRASH) --}}
                            <div class="w-full bg-white border border-gray-100 hover:border-emerald-200 hover:shadow-sm rounded-2xl p-4 transition duration-200 flex items-center justify-between gap-4">
                                
                                {{-- Area Klik Kiri (Icon + Info) - Memicu modal detail --}}
                                <div @click="openDetail({{ $detailData }})" class="flex items-center gap-4 flex-1 min-w-0 cursor-pointer">
                                    {{-- Icon --}}
                                    <div class="w-11 h-11 bg-gray-50 rounded-xl flex items-center justify-center text-xl flex-shrink-0 border border-gray-100">
                                        {{ $icon }}
                                    </div>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-gray-900 text-sm">{{ $pickup->type }}</span>
                                            @if($pickup->status === 'pending')
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700 border border-yellow-100">Pending</span>
                                            @elseif($pickup->status === 'approved')
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">Disetujui</span>
                                            @else
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-600 border border-red-100">Ditolak</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-gray-400 font-semibold">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                                                {{ $pickup->weight }} kg
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                {{ \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y') }}
                                            </span>
                                            <span class="flex items-center gap-1 max-w-[150px] sm:max-w-[200px] truncate">
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                                <span class="truncate">{{ $pickup->address }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Area Kanan (Poin / Batalkan terpisah dari klik detail) --}}
                                <div class="flex-shrink-0 text-right">
                                    @if($pickup->status === 'approved' && $pickup->points_earned)
                                        <p class="text-emerald-600 font-black text-lg leading-none">+{{ $pickup->points_earned }}</p>
                                        <p class="text-gray-400 text-[10px] mt-1">poin</p>
                                    @elseif($pickup->status === 'pending')
                                        <form action="{{ route('pickups.destroy', $pickup->id) }}" method="POST"
                                              onsubmit="return confirm('Batalkan pengajuan setoran ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-bold hover:underline transition">
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <svg class="w-4 h-4 text-gray-300 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 px-6 border border-dashed border-gray-100 rounded-2xl">
                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                        <p class="text-gray-700 text-sm font-bold">Belum Ada Setoran</p>
                        <p class="text-gray-400 text-xs mt-1">Mulai kontribusi lingkungan Anda sekarang!</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- MODAL DETAIL --}}
        <div x-show="isDetailOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[300] flex items-end sm:items-center justify-center bg-gray-900/50 p-4" style="display:none;">
            <div @click.away="isDetailOpen = false" x-show="isDetailOpen"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-4"
                 class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">

                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
                    <div>
                        <h3 class="font-black text-gray-900 text-base" x-text="detail ? detail.type : ''"></h3>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="detail ? 'ID Setoran: #' + detail.id : ''"></p>
                    </div>
                    <button @click="isDetailOpen = false" class="w-9 h-9 bg-gray-50 hover:bg-gray-100 rounded-xl flex items-center justify-center text-gray-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-6 space-y-4">
                    <div x-show="detail && detail.photo">
                        <img :src="detail ? detail.photo : ''" loading="lazy" class="w-full rounded-2xl object-cover max-h-52 border border-gray-100">
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 border border-gray-100">
                        <span class="font-bold text-xs text-gray-700" x-text="detail ? 'Status: ' + detail.status.toUpperCase() : ''"></span>
                        <span x-show="detail && detail.status === 'approved'" class="font-black text-emerald-700" x-text="detail ? '+' + detail.points + ' Poin' : ''"></span>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 space-y-3 text-xs">
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Berat</p>
                            <p class="font-semibold text-gray-700" x-text="detail ? detail.weight + ' kg' : ''"></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Tanggal Pengajuan</p>
                            <p class="font-semibold text-gray-700" x-text="detail ? detail.pickup_date : ''"></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">No. Telepon / WA</p>
                            <p class="font-semibold text-gray-700" x-text="detail ? detail.phone : ''"></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Alamat Penjemputan</p>
                            <p class="font-semibold text-gray-700 leading-relaxed" x-text="detail ? detail.address : ''"></p>
                            <a x-show="detail && detail.maps_url" :href="detail ? detail.maps_url : '#'" target="_blank"
                               class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:underline mt-2 font-bold">
                                Lihat lokasi di Peta →
                            </a>
                        </div>
                        <div x-show="detail && detail.notes">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Catatan Tambahan</p>
                            <p class="font-semibold text-gray-700 leading-relaxed" x-text="detail ? detail.notes : ''"></p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex-shrink-0">
                    <button @click="isDetailOpen = false" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-bold transition text-xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- FORM MODAL --}}
        <div x-show="isModalOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[300] flex items-end sm:items-center justify-center bg-gray-900/50 p-4" style="display: none;">
            <div @click.away="isModalOpen = false" x-show="isModalOpen"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-4"
                 class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">

                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
                    <div>
                        <h3 class="text-base font-black text-gray-900">Form Setoran Sampah</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Isi data penjemputan dengan lengkap dan benar</p>
                    </div>
                    <button type="button" @click="isModalOpen = false" class="w-9 h-9 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-6">
                    <form method="POST" action="{{ route('pickups.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Jenis Sampah <span class="text-red-500">*</span></label>
                            <select name="type" required class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition bg-white text-sm text-gray-700">
                                <option value="" disabled selected>Pilih jenis sampah...</option>
                                @foreach(['Plastik', 'Kertas', 'Logam', 'Kaca', 'Organik', 'Elektronik', 'Lainnya'] as $type)
                                    <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('type') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Berat (Kg) <span class="text-red-500">*</span></label>
                                <input type="number" name="weight" step="0.1" min="0.1" required placeholder="5.0" value="{{ old('weight') }}"
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition bg-white text-sm text-gray-700" />
                                @error('weight') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Tanggal Jemput <span class="text-red-500">*</span></label>
                                <input type="date" name="pickup_date" required min="{{ date('Y-m-d') }}" value="{{ old('pickup_date') }}"
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition bg-white text-sm text-gray-500" />
                                @error('pickup_date') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Alamat Lengkap <span class="text-red-500">*</span></label>
                            <textarea name="address" id="addressInput" required rows="2" placeholder="Nama jalan, No. rumah, RT/RW, Kelurahan..."
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition bg-white text-sm text-gray-700 resize-none">{{ old('address') }}</textarea>
                            @error('address') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                            <button type="button" id="detectLocationBtn" onclick="detectLocation()"
                                class="mt-2 w-full flex items-center justify-center gap-2 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-100 rounded-2xl font-bold text-xs transition active:scale-[0.98]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                <span id="detectBtnText">Deteksi Lokasi GPS</span>
                            </button>
                            <div id="gpsStatus" class="hidden mt-2 p-3 rounded-xl text-xs font-semibold"></div>
                            <div id="mapContainer" class="hidden mt-3 rounded-2xl overflow-hidden border border-emerald-200">
                                <div id="map" style="height: 180px; width: 100%;"></div>
                                <p class="bg-emerald-50 px-4 py-2 text-xs text-emerald-700 font-semibold">Seret pin untuk menyesuaikan lokasi</p>
                            </div>
                        </div>

                        <input type="hidden" name="latitude" id="latInput" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="lngInput" value="{{ old('longitude') }}">

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">No. Telepon (WhatsApp) <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" required placeholder="08123456789" value="{{ old('phone') }}"
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition bg-white text-sm text-gray-700" />
                            @error('phone') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Foto Sampah <span class="text-red-500">*</span> <span class="text-gray-400 font-normal">(Maks 5MB)</span></label>
                            <div class="relative">
                                <input type="file" name="photo" accept="image/*" required @change="handlePhoto($event)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="border border-dashed border-gray-200 hover:border-emerald-400 rounded-2xl p-5 text-center transition-colors bg-gray-50 hover:bg-emerald-50/30">
                                    <div x-show="photoPreview" class="mb-2">
                                        <img :src="photoPreview" class="w-full max-h-32 object-cover rounded-xl border border-gray-100">
                                    </div>
                                    <div x-show="!photoPreview" class="py-2">
                                        <svg class="w-7 h-7 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <p class="text-xs text-gray-500 font-semibold">Klik atau seret foto di sini</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">JPG, PNG, WEBP</p>
                                    </div>
                                    <p x-show="photoName" x-text="photoName" class="text-xs text-emerald-600 font-bold mt-1 truncate"></p>
                                </div>
                            </div>
                            @error('photo') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Catatan <span class="text-gray-400 font-normal">(Opsional)</span></label>
                            <textarea name="notes" rows="2" maxlength="500" placeholder="Contoh: Titip di pos satpam..."
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition bg-white text-sm text-gray-700 resize-none">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex gap-3 pt-2 border-t border-gray-100">
                            <button type="button" @click="isModalOpen = false"
                                class="flex-1 py-3 text-gray-500 hover:bg-gray-100 rounded-2xl font-bold transition active:scale-[0.98] text-sm">
                                Batal
                            </button>
                            <button type="submit"
                                class="flex-1 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold shadow-sm active:scale-[0.98] transition text-sm">
                                Ajukan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = null, marker = null;
        function detectLocation() {
            const btn = document.getElementById('detectLocationBtn');
            const btnText = document.getElementById('detectBtnText');
            const status = document.getElementById('gpsStatus');
            if (!navigator.geolocation) {
                status.className = 'mt-2 p-3 rounded-xl text-xs font-semibold bg-red-50 text-red-600 border border-red-200';
                status.textContent = 'GPS tidak didukung browser ini';
                status.classList.remove('hidden'); return;
            }
            btnText.textContent = 'Mengakses GPS...'; btn.disabled = true;
            status.className = 'mt-2 p-3 rounded-xl text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200';
            status.textContent = 'Mendeteksi koordinat...'; status.classList.remove('hidden');
            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude, lng = pos.coords.longitude;
                document.getElementById('mapContainer').classList.remove('hidden');
                if (map) { map.remove(); map = null; }
                setTimeout(() => {
                    map = L.map('map').setView([lat, lng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
                    const icon = L.divIcon({ html: `<div style="width:28px;height:28px;background:#10b981;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 4px 12px rgba(16,185,129,0.5);"></div>`, className:'', iconSize:[28,28], iconAnchor:[14,28] });
                    marker = L.marker([lat, lng], { draggable: true, icon }).addTo(map);
                    marker.on('dragend', function(e) {
                        const p = e.target.getLatLng();
                        document.getElementById('latInput').value = p.lat.toFixed(8);
                        document.getElementById('lngInput').value = p.lng.toFixed(8);
                        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${p.lat}&lon=${p.lng}&format=json`).then(r=>r.json()).then(d=>{ if(d.display_name) document.getElementById('addressInput').value = d.display_name; });
                    });
                    document.getElementById('latInput').value = lat.toFixed(8);
                    document.getElementById('lngInput').value = lng.toFixed(8);
                }, 150);
                fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`).then(r=>r.json()).then(d=>{ if(d.display_name) document.getElementById('addressInput').value = d.display_name; });
                status.className = 'mt-2 p-3 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                status.textContent = 'Koordinat berhasil dideteksi! Seret pin untuk menyesuaikan.';
                btnText.textContent = 'Lokasi Terkunci ✓'; btn.disabled = false;
            }, function(err) {
                let msg = 'Gagal mendeteksi lokasi';
                if (err.code === 1) msg = 'Izin GPS ditolak. Aktifkan di pengaturan browser.';
                status.className = 'mt-2 p-3 rounded-xl text-xs font-semibold bg-red-50 text-red-600 border border-red-200';
                status.textContent = msg; btnText.textContent = 'Coba Lagi'; btn.disabled = false;
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        }
    </script>
</x-app-layout>