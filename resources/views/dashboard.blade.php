<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center shadow-lg">
                <span class="text-2xl">🌱</span>
            </div>
            <div>
                <h2 class="font-extrabold text-3xl bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                    EcoDrop Dashboard
                </h2>
                <p class="text-sm text-gray-500 mt-1">Selamat datang, {{ Auth::user()->name }}! 👋</p>
            </div>
        </div>
    </x-slot>

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
            openDetail(data) {
                this.detail = data;
                this.isDetailOpen = true;
            }
         }"
         class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-blue-50 py-12 relative overflow-hidden">

        <div class="fixed inset-0 pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-green-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute top-40 right-10 w-72 h-72 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">

            @if (session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-90"
                     class="mb-6 p-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-2xl shadow-xl flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl animate-bounce">✨</span>
                        <span class="font-bold text-lg">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="hover:bg-white/20 p-2 rounded-lg transition duration-300">✕</button>
                </div>
            @endif

            {{-- Points + CTA --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-gradient-to-br from-green-400 via-emerald-500 to-green-600 rounded-3xl shadow-2xl overflow-hidden">
                    <div class="p-8 text-white">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-green-100 text-sm font-bold uppercase tracking-wider">Saldo Poin Anda</p>
                                <h3 class="text-6xl font-black mt-3 drop-shadow-lg">{{ Auth::user()->points }}</h3>
                            </div>
                            <div class="text-5xl animate-bounce">🏆</div>
                        </div>
                        <p class="text-green-100 text-sm font-semibold">Terus setor sampah untuk mendapat lebih banyak poin!</p>
                    </div>
                </div>
                <div class="md:col-span-2 bg-white/80 backdrop-blur-sm p-8 rounded-3xl shadow-2xl border border-gray-100/50 flex flex-col justify-between">
                    <div class="mb-6">
                        <h3 class="text-3xl font-black bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">♻️ Setor Sampah</h3>
                        <p class="text-gray-600 mt-2 font-medium">Ubah sampah Anda menjadi poin reward!</p>
                    </div>
                    <button @click="isModalOpen = true"
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-8 py-4 rounded-2xl font-bold shadow-lg hover:shadow-2xl transition duration-300 transform hover:scale-105 flex items-center justify-center gap-3 text-lg">
                        <span class="text-2xl">➕</span>
                        <span>Setor Baru Sekarang</span>
                        <span class="text-2xl animate-pulse">→</span>
                    </button>
                </div>
            </div>

            {{-- Riwayat Setoran --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-2xl font-black text-gray-900">📜 Riwayat Setoran Anda</h3>
                    <p class="text-sm text-gray-500 mt-1">Pantau status semua setoran sampah Anda</p>
                </div>

                <div class="p-8">
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
                                @endphp
                                <div class="flex items-center justify-between p-5 rounded-2xl border border-gray-200 hover:border-green-300 hover:bg-green-50/50 transition duration-300 hover:shadow-md group">
                                    {{-- Left: info ringkas --}}
                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        {{-- Icon jenis --}}
                                        <div class="w-12 h-12 rounded-2xl bg-gray-100 group-hover:bg-green-100 flex items-center justify-center text-2xl transition flex-shrink-0">
                                            @switch($pickup->type)
                                                @case('Plastik') 🪴 @break
                                                @case('Kertas') 📄 @break
                                                @case('Logam') 🔩 @break
                                                @case('Kaca') 🥛 @break
                                                @case('Organik') 🍂 @break
                                                @case('Elektronik') ⚡ @break
                                                @case('Lainnya') 📦 @break
                                                @default ♻️
                                            @endswitch
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-black text-gray-900">{{ $pickup->type }} · {{ $pickup->weight }} Kg</p>
                                            <p class="text-xs text-gray-400 mt-0.5">📅 {{ \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y') }}</p>
                                        </div>
                                    </div>

                                    {{-- Center: status --}}
                                    <div class="flex-shrink-0 mx-4">
                                        @if($pickup->status === 'pending')
                                            <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-800 px-3 py-1.5 rounded-full font-bold text-xs">
                                                <span class="w-1.5 h-1.5 bg-yellow-600 rounded-full animate-pulse"></span>
                                                Menunggu
                                            </span>
                                        @elseif($pickup->status === 'approved')
                                            <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-800 px-3 py-1.5 rounded-full font-bold text-xs">
                                                <span class="w-1.5 h-1.5 bg-green-600 rounded-full"></span>
                                                +{{ $pickup->points_earned }} poin
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-800 px-3 py-1.5 rounded-full font-bold text-xs">
                                                <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                                                Ditolak
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Right: actions --}}
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        {{-- Tombol Detail --}}
                                        <button @click="openDetail({{ $detailData }})"
                                            class="text-xs bg-green-50 text-green-700 hover:bg-green-100 font-bold px-3 py-2 border border-green-200 rounded-lg transition duration-200">
                                            🔍 Detail
                                        </button>

                                        {{-- Batalkan --}}
                                        @if($pickup->status === 'pending')
                                            <form action="{{ route('pickups.destroy', $pickup->id) }}" method="POST"
                                                  onsubmit="return confirm('⚠️ Yakin ingin membatalkan setoran ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-bold px-3 py-2 border border-red-200 rounded-lg transition duration-200">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">🌱</div>
                            <p class="text-gray-500 text-lg font-semibold mb-2">Belum ada setoran sampah</p>
                            <p class="text-gray-400 text-sm">Mulai sekarang dengan menekan tombol "Setor Baru"!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══ MODAL DETAIL SETORAN ═══ --}}
        <template x-teleport="body">
            <div x-show="isDetailOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-md p-4"
                 style="display: none;">
                <div @click.away="isDetailOpen = false"
                     x-show="isDetailOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0 transform scale-90"
                     class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-5 flex justify-between items-center flex-shrink-0">
                        <div>
                            <h3 class="text-white font-black text-lg">📦 Detail Setoran</h3>
                            <p class="text-green-100 text-xs mt-0.5" x-text="detail ? '#' + detail.id + ' · ' + detail.type : ''"></p>
                        </div>
                        <button @click="isDetailOpen = false"
                            class="w-9 h-9 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center text-white text-xl font-bold transition">✕</button>
                    </div>

                    <div class="overflow-y-auto flex-1 p-6 space-y-4">

                        {{-- Foto sampah --}}
                        <div x-show="detail && detail.photo">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">📸 Foto Sampah</p>
                            <img :src="detail ? detail.photo : ''"
                                 class="w-full rounded-2xl object-cover max-h-56 shadow-sm border border-gray-100">
                        </div>

                        {{-- Status --}}
                        <div class="flex items-center justify-between p-4 rounded-2xl"
                             :class="{
                                'bg-yellow-50 border border-yellow-200': detail && detail.status === 'pending',
                                'bg-green-50 border border-green-200': detail && detail.status === 'approved',
                                'bg-red-50 border border-red-200': detail && detail.status === 'rejected'
                             }">
                            <span class="font-black text-sm"
                                  :class="{
                                    'text-yellow-800': detail && detail.status === 'pending',
                                    'text-green-800': detail && detail.status === 'approved',
                                    'text-red-800': detail && detail.status === 'rejected'
                                  }"
                                  x-text="detail ? (detail.status === 'pending' ? '⏳ Menunggu Verifikasi' : detail.status === 'approved' ? '✅ Disetujui' : '❌ Ditolak') : ''">
                            </span>
                            <span x-show="detail && detail.status === 'approved'"
                                  class="text-green-600 font-black text-lg"
                                  x-text="detail ? '+' + detail.points + ' poin 🏆' : ''">
                            </span>
                        </div>

                        {{-- Info grid --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 rounded-2xl p-4">
                                <p class="text-xs text-gray-400 font-semibold mb-1">Jenis Sampah</p>
                                <p class="font-black text-gray-900" x-text="detail ? detail.type : ''"></p>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4">
                                <p class="text-xs text-gray-400 font-semibold mb-1">Berat</p>
                                <p class="font-black text-gray-900" x-text="detail ? detail.weight + ' Kg' : ''"></p>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4">
                                <p class="text-xs text-gray-400 font-semibold mb-1">Tanggal Jemput</p>
                                <p class="font-black text-gray-900 text-sm" x-text="detail ? detail.pickup_date : ''"></p>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4">
                                <p class="text-xs text-gray-400 font-semibold mb-1">No. Telepon</p>
                                <p class="font-black text-gray-900 text-sm" x-text="detail ? detail.phone : ''"></p>
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div class="bg-gray-50 rounded-2xl p-4">
                            <p class="text-xs text-gray-400 font-semibold mb-1">📍 Alamat Penjemputan</p>
                            <p class="font-semibold text-gray-800 text-sm" x-text="detail ? detail.address : ''"></p>
                            <a x-show="detail && detail.maps_url"
                               :href="detail ? detail.maps_url : '#'"
                               target="_blank"
                               class="inline-flex items-center gap-1 text-xs text-blue-500 hover:text-blue-700 mt-2 font-bold">
                                🗺️ Lihat di Google Maps
                            </a>
                        </div>

                        {{-- Catatan --}}
                        <div x-show="detail && detail.notes" class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                            <p class="text-xs text-amber-600 font-bold mb-1">📝 Catatan</p>
                            <p class="text-sm text-gray-700" x-text="detail ? detail.notes : ''"></p>
                        </div>

                        {{-- Ditangani oleh --}}
                        <div x-show="detail && detail.handled_by" class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4">
                            <p class="text-xs text-indigo-500 font-bold mb-1">🛡️ Ditangani Oleh</p>
                            <p class="font-black text-indigo-800 text-sm" x-text="detail ? detail.handled_by : ''"></p>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                        <button @click="isDetailOpen = false"
                            class="w-full py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-bold transition duration-200">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- ═══ MODAL FORM SETORAN BARU ═══ --}}
        <template x-teleport="body">
            <div x-show="isModalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-md p-4"
                 style="display: none;">

                <div @click.away="isModalOpen = false"
                     x-show="isModalOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-90"
                     class="bg-white rounded-3xl w-full max-w-lg shadow-2xl relative border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]">

                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 via-emerald-500 to-blue-500 z-20"></div>

                    <div class="sticky top-0 bg-white/95 backdrop-blur-sm z-10 px-8 py-6 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-black bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">📦 Ajukan Setor Sampah</h3>
                            <p class="text-sm text-gray-500 mt-1">Isi form untuk mengajukan setor sampah</p>
                        </div>
                        <button type="button" @click="isModalOpen = false" class="text-3xl font-bold text-gray-400 hover:text-red-500 transition duration-300">&times;</button>
                    </div>

                    <div class="p-8 overflow-y-auto">
                        <form method="POST" action="{{ route('pickups.store') }}" enctype="multipart/form-data" class="space-y-5">
                            @csrf

                            {{-- Jenis Sampah --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">♻️ Jenis Sampah</label>
                                <select name="type" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300 font-semibold bg-gray-50">
                                    <option value="" disabled selected>Pilih jenis sampah...</option>
                                    <option value="Plastik" {{ old('type') === 'Plastik' ? 'selected' : '' }}>🪴 Plastik</option>
                                    <option value="Kertas" {{ old('type') === 'Kertas' ? 'selected' : '' }}>📄 Kertas</option>
                                    <option value="Logam" {{ old('type') === 'Logam' ? 'selected' : '' }}>🔩 Logam</option>
                                    <option value="Kaca" {{ old('type') === 'Kaca' ? 'selected' : '' }}>🥛 Kaca</option>
                                    <option value="Organik" {{ old('type') === 'Organik' ? 'selected' : '' }}>🍂 Organik</option>
                                    <option value="Elektronik" {{ old('type') === 'Elektronik' ? 'selected' : '' }}>⚡ Elektronik</option>
                                    <option value="Lainnya" {{ old('type') === 'Lainnya' ? 'selected' : '' }}>📦 Lainnya</option>
                                </select>
                                @error('type') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                            </div>

                            {{-- Berat --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">⚖️ Berat Sampah (Kg)</label>
                                <input type="number" name="weight" step="0.1" required placeholder="Contoh: 5.5" value="{{ old('weight') }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300 font-semibold bg-gray-50" />
                                @error('weight') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                            </div>

                            {{-- Tanggal --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">📅 Tanggal Penjemputan</label>
                                <input type="date" name="pickup_date" required min="{{ date('Y-m-d') }}" value="{{ old('pickup_date') }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300 font-semibold bg-gray-50" />
                                @error('pickup_date') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                            </div>

                            {{-- Alamat + GPS --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">📍 Alamat Penjemputan</label>
                                <textarea name="address" id="addressInput" required placeholder="Contoh: Jl. Merdeka No. 123, Bandung" rows="2"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300 font-semibold bg-gray-50">{{ old('address') }}</textarea>
                                @error('address') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror

                                <button type="button" id="detectLocationBtn" onclick="detectLocation()"
                                    class="mt-2 w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border-2 border-blue-200 rounded-xl font-bold text-sm transition duration-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span id="detectBtnText">Deteksi Lokasi Otomatis (GPS)</span>
                                </button>
                                <div id="gpsStatus" class="hidden mt-2 p-3 rounded-xl text-xs font-semibold"></div>
                                <div id="mapContainer" class="hidden mt-3 rounded-xl overflow-hidden border-2 border-blue-200 shadow-sm">
                                    <div id="map" style="height: 200px; width: 100%;"></div>
                                    <div class="bg-blue-50 px-4 py-2 text-xs text-blue-600 font-semibold flex items-center gap-2">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Seret pin untuk menyesuaikan lokasi
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="latitude" id="latInput" value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="lngInput" value="{{ old('longitude') }}">

                            {{-- Telepon --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">📱 Nomor Telepon</label>
                                <input type="tel" name="phone" required placeholder="Contoh: 0812345678" value="{{ old('phone') }}"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300 font-semibold bg-gray-50" />
                                @error('phone') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                            </div>

                            {{-- Foto Sampah --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    📸 Foto Sampah
                                    <span class="text-red-500">*</span>
                                    <span class="text-gray-400 font-normal text-xs ml-1">(Wajib, maks 5MB)</span>
                                </label>
                                <div class="relative">
                                    <input type="file" name="photo" id="photoInput" accept="image/*" required
                                           @change="handlePhoto($event)"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                    <div class="border-2 border-dashed border-gray-300 hover:border-green-400 rounded-xl p-6 text-center transition duration-300 bg-gray-50 hover:bg-green-50">
                                        <div x-show="photoPreview" class="mb-3">
                                            <img :src="photoPreview" class="w-full max-h-40 object-cover rounded-xl shadow-sm">
                                        </div>
                                        <div x-show="!photoPreview">
                                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                            </svg>
                                            <p class="text-sm text-gray-500 font-semibold">Klik atau drag foto sampah</p>
                                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP • Maks 5MB</p>
                                        </div>
                                        <p x-show="photoName" x-text="'📎 ' + photoName" class="text-xs text-green-600 font-bold mt-2 truncate"></p>
                                    </div>
                                </div>
                                @error('photo') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                            </div>

                            {{-- Catatan --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    📝 Catatan Tambahan
                                    <span class="text-gray-400 font-normal text-xs ml-1">(Opsional)</span>
                                </label>
                                <textarea name="notes" rows="3"
                                    placeholder="Contoh: Sampah sudah dipilah, ada beberapa botol plastik besar..."
                                    maxlength="500"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300 font-medium bg-gray-50 text-sm resize-none">{{ old('notes') }}</textarea>
                                @error('notes') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex gap-3 pt-6 border-t border-gray-200">
                                <button type="button" @click="isModalOpen = false"
                                    class="flex-1 px-5 py-3 text-gray-700 border-2 border-gray-300 rounded-xl font-bold hover:bg-gray-100 transition duration-300">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="flex-1 px-5 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition duration-300 flex items-center justify-center gap-2">
                                    ✅ Ajukan Sekarang
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        <style>
            @keyframes blob {
                0%, 100% { transform: translate(0, 0) scale(1); }
                25% { transform: translate(20px, -50px) scale(1.1); }
                50% { transform: translate(-20px, 20px) scale(0.9); }
                75% { transform: translate(50px, 50px) scale(1.05); }
            }
            .animate-blob { animation: blob 7s infinite; }
            .animation-delay-2000 { animation-delay: 2s; }
            .animation-delay-4000 { animation-delay: 4s; }
        </style>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = null;
        let marker = null;

        function initMap(lat, lng) {
            const mapContainer = document.getElementById('mapContainer');
            mapContainer.classList.remove('hidden');
            if (map) { map.remove(); map = null; }
            setTimeout(() => {
                map = L.map('map').setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);
                const greenIcon = L.divIcon({
                    html: `<div style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#059669);border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 4px 12px rgba(16,185,129,0.5);"></div>`,
                    className: '', iconSize: [36,36], iconAnchor: [18,36],
                });
                marker = L.marker([lat, lng], { draggable: true, icon: greenIcon }).addTo(map);
                marker.on('dragend', function(e) {
                    const pos = e.target.getLatLng();
                    document.getElementById('latInput').value = pos.lat.toFixed(8);
                    document.getElementById('lngInput').value = pos.lng.toFixed(8);
                    fetch(`https://nominatim.openstreetmap.org/reverse?lat=${pos.lat}&lon=${pos.lng}&format=json`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.display_name) document.getElementById('addressInput').value = data.display_name;
                        });
                });
                document.getElementById('latInput').value = lat.toFixed(8);
                document.getElementById('lngInput').value = lng.toFixed(8);
            }, 100);
        }

        function detectLocation() {
            const btn = document.getElementById('detectLocationBtn');
            const btnText = document.getElementById('detectBtnText');
            const status = document.getElementById('gpsStatus');
            if (!navigator.geolocation) {
                status.className = 'mt-2 p-3 rounded-xl text-xs font-semibold bg-red-50 text-red-600 border border-red-200';
                status.textContent = '❌ Browser kamu tidak mendukung GPS';
                status.classList.remove('hidden');
                return;
            }
            btnText.textContent = 'Mendeteksi lokasi...';
            btn.disabled = true;
            btn.classList.add('opacity-60');
            status.className = 'mt-2 p-3 rounded-xl text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200';
            status.textContent = '📡 Mengakses GPS, mohon tunggu...';
            status.classList.remove('hidden');
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    initMap(lat, lng);
                    fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.display_name) document.getElementById('addressInput').value = data.display_name;
                            status.className = 'mt-2 p-3 rounded-xl text-xs font-semibold bg-green-50 text-green-700 border border-green-200';
                            status.textContent = '✅ Lokasi berhasil dideteksi! Seret pin untuk menyesuaikan.';
                            btnText.textContent = 'Lokasi Terdeteksi ✓';
                            btn.classList.remove('opacity-60');
                            btn.disabled = false;
                        });
                },
                function(error) {
                    let msg = '❌ Gagal mendeteksi lokasi';
                    if (error.code === 1) msg = '❌ Izin lokasi ditolak. Aktifkan GPS di browser.';
                    if (error.code === 2) msg = '❌ Lokasi tidak tersedia. Coba lagi.';
                    if (error.code === 3) msg = '❌ Timeout. Coba lagi.';
                    status.className = 'mt-2 p-3 rounded-xl text-xs font-semibold bg-red-50 text-red-600 border border-red-200';
                    status.textContent = msg;
                    btnText.textContent = 'Deteksi Lokasi Otomatis (GPS)';
                    btn.classList.remove('opacity-60');
                    btn.disabled = false;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    </script>
</x-app-layout>