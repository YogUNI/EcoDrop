<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center shadow-lg">
                <span class="text-2xl">👑</span>
            </div>
            <div>
                <h2 class="font-extrabold text-3xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    Admin Panel
                </h2>
                <p class="text-sm text-gray-500 mt-1">Selamat datang, {{ Auth::user()->name }}! 👋</p>
            </div>
        </div>
    </x-slot>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <div x-data="{
            isDetailOpen: false,
            detail: null,
            openDetail(data) { this.detail = data; this.isDetailOpen = true; }
         }"
         class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-slate-50 py-12 relative overflow-hidden">

        <div class="fixed inset-0 pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
            <div class="absolute -bottom-8 right-20 w-72 h-72 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-90"
                     class="mb-6 p-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl shadow-xl flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">✅</span>
                        <span class="font-bold text-lg">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="hover:bg-white/20 p-2 rounded-lg transition">✕</button>
                </div>
            @endif

            @php
                $totalPickups    = $pickups->count();
                $pendingPickups  = $pickups->where('status', 'pending')->count();
                $approvedPickups = $pickups->where('status', 'approved')->count();
                $rejectedPickups = $pickups->where('status', 'rejected')->count();
                $totalWeight     = $pickups->where('status', 'approved')->sum('weight');
                $todayStr        = \Carbon\Carbon::today()->format('Y-m-d');
                $todayWeight     = $pickups->where('status', 'approved')
                    ->filter(fn($i) => \Carbon\Carbon::parse($i->pickup_date)->format('Y-m-d') === $todayStr)
                    ->sum('weight');
                $totalUsers      = $pickups->pluck('user_id')->unique()->count();
                $approvalRate    = $totalPickups > 0 ? round(($approvedPickups / $totalPickups) * 100, 1) : 0;
                $wasteByType     = [];
                foreach(['Plastik','Kertas','Logam','Kaca','Organik','Elektronik','Lainnya'] as $type) {
                    $wasteByType[$type] = $pickups->where('type', $type)->where('status','approved')->sum('weight');
                }
                $last7Days = [];
                for($i = 6; $i >= 0; $i--) {
                    $date = \Carbon\Carbon::today()->subDays($i)->format('Y-m-d');
                    $last7Days[\Carbon\Carbon::parse($date)->format('d M')] = $pickups->where('status','approved')
                        ->filter(fn($item) => \Carbon\Carbon::parse($item->pickup_date)->format('Y-m-d') === $date)
                        ->sum('weight');
                }
            @endphp

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50 hover:shadow-xl transition duration-300 hover:scale-105">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Setoran</p>
                            <h3 class="text-4xl font-black text-gray-900 mt-2">{{ $totalPickups }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-xl shadow-lg">📦</div>
                    </div>
                    <p class="text-xs text-gray-400">Dari semua user</p>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50 hover:shadow-xl transition duration-300 hover:scale-105">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Menunggu Verifikasi</p>
                            <h3 class="text-4xl font-black text-yellow-600 mt-2">{{ $pendingPickups }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-xl shadow-lg">⏳</div>
                    </div>
                    <p class="text-xs text-gray-400">Butuh aksi</p>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50 hover:shadow-xl transition duration-300 hover:scale-105">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Sudah Disetujui</p>
                            <h3 class="text-4xl font-black text-green-600 mt-2">{{ $approvedPickups }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-xl shadow-lg">✅</div>
                    </div>
                    <p class="text-xs text-gray-400">Berhasil</p>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50 hover:shadow-xl transition duration-300 hover:scale-105">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Sampah Diterima</p>
                            <h3 class="text-4xl font-black text-purple-600 mt-2">{{ number_format($totalWeight, 1) }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-xl shadow-lg">⚖️</div>
                    </div>
                    <p class="text-xs text-gray-400">Kg</p>
                </div>
            </div>

            {{-- Additional Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Hari Ini</p>
                            <h3 class="text-3xl font-black text-blue-600 mt-2">{{ number_format($todayWeight, 1) }} Kg</h3>
                        </div>
                        <span class="text-4xl">📅</span>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total User</p>
                            <h3 class="text-3xl font-black text-indigo-600 mt-2">{{ $totalUsers }}</h3>
                        </div>
                        <span class="text-4xl">👥</span>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Approval Rate</p>
                            <h3 class="text-3xl font-black text-green-600 mt-2">{{ $approvalRate }}%</h3>
                        </div>
                        <span class="text-4xl">📊</span>
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-gray-100/50">
                    <h3 class="text-xl font-black text-gray-900 mb-6">📊 Total Sampah per Jenis</h3>
                    <canvas id="wasteByTypeChart"></canvas>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-gray-100/50">
                    <h3 class="text-xl font-black text-gray-900 mb-6">📈 Status Setoran</h3>
                    <canvas id="approvalRateChart"></canvas>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-gray-100/50 mb-8">
                <h3 class="text-xl font-black text-gray-900 mb-6">📉 Tren Sampah 7 Hari Terakhir</h3>
                <canvas id="sevenDaysTrendChart"></canvas>
            </div>

            {{-- Tabel --}}
            <div x-data="{ searchQuery: '', filterStatus: '', filterType: '', filterDateFrom: '', filterDateTo: '' }"
                 class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="text-2xl font-black text-gray-900 mb-6">📋 Antrian Setor Sampah</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <input type="text" x-model="searchQuery" placeholder="🔍 Cari nama user atau email..."
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:outline-none transition duration-300">
                        <select x-model="filterStatus" class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:outline-none transition duration-300">
                            <option value="">📊 Status (Semua)</option>
                            <option value="pending">⏳ Pending</option>
                            <option value="approved">✅ Approved</option>
                            <option value="rejected">❌ Rejected</option>
                        </select>
                        <select x-model="filterType" class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:outline-none transition duration-300">
                            <option value="">♻️ Jenis (Semua)</option>
                            <option value="Plastik">🪴 Plastik</option>
                            <option value="Kertas">📄 Kertas</option>
                            <option value="Logam">🔩 Logam</option>
                            <option value="Kaca">🥛 Kaca</option>
                            <option value="Organik">🍂 Organik</option>
                            <option value="Elektronik">⚡ Elektronik</option>
                            <option value="Lainnya">📦 Lainnya</option>
                        </select>
                    </div>
                    {{-- Filter Tanggal --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">📅 Dari Tanggal</label>
                            <input type="date" x-model="filterDateFrom"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:outline-none transition duration-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">📅 Sampai Tanggal</label>
                            <input type="date" x-model="filterDateTo"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:outline-none transition duration-300 text-sm">
                        </div>
                        <button @click="searchQuery=''; filterStatus=''; filterType=''; filterDateFrom=''; filterDateTo=''"
                            class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg font-bold text-sm transition duration-300 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset Filter
                        </button>
                    </div>
                </div>

                <div class="p-8 overflow-x-auto">
                    @if($pickups->count() > 0)
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs font-bold text-gray-600 uppercase tracking-widest border-b-2 border-gray-200">
                                    <th class="pb-4 pl-4">👤 User</th>
                                    <th class="pb-4">♻️ Jenis</th>
                                    <th class="pb-4">📍 Lokasi</th>
                                    <th class="pb-4">📅 Tanggal</th>
                                    <th class="pb-4 text-center">📊 Status</th>
                                    <th class="pb-4">🛡️ Ditangani</th>
                                    <th class="pb-4 text-right">⚙️ Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pickups as $pickup)
                                    @php
                                        $pickupDateStr = \Carbon\Carbon::parse($pickup->pickup_date)->format('Y-m-d');
                                        $detailData = json_encode([
                                            'id'         => $pickup->id,
                                            'user_name'  => $pickup->user->name,
                                            'user_email' => $pickup->user->email,
                                            'user_photo' => $pickup->user->getPhotoUrl(),
                                            'type'       => $pickup->type,
                                            'weight'     => $pickup->weight,
                                            'pickup_date'=> \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y'),
                                            'address'    => $pickup->address,
                                            'phone'      => $pickup->phone,
                                            'status'     => $pickup->status,
                                            'points'     => $pickup->points_earned,
                                            'notes'      => $pickup->notes,
                                            'photo'      => $pickup->photo ? Storage::url($pickup->photo) : null,
                                            'latitude'   => $pickup->latitude,
                                            'longitude'  => $pickup->longitude,
                                            'handled_by' => $pickup->handledBy?->name,
                                            'maps_url'   => $pickup->latitude ? "https://maps.google.com/?q={$pickup->latitude},{$pickup->longitude}" : null,
                                        ]);
                                    @endphp
                                    <tr class="border-b border-gray-100 hover:bg-blue-50/50 transition duration-300"
                                        x-show="
                                            (searchQuery === '' || '{{ strtolower($pickup->user->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($pickup->user->email) }}'.includes(searchQuery.toLowerCase())) &&
                                            (filterStatus === '' || filterStatus === '{{ $pickup->status }}') &&
                                            (filterType === '' || filterType === '{{ $pickup->type }}') &&
                                            (filterDateFrom === '' || '{{ $pickupDateStr }}' >= filterDateFrom) &&
                                            (filterDateTo === '' || '{{ $pickupDateStr }}' <= filterDateTo)
                                        ">
                                        <td class="py-5 pl-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white shadow flex-shrink-0">
                                                    <img src="{{ $pickup->user->getPhotoUrl() }}" class="w-full h-full object-cover" alt="{{ $pickup->user->name }}">
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900">{{ $pickup->user->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $pickup->user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-5">
                                            <span class="inline-flex items-center gap-2 bg-purple-100 text-purple-700 py-2 px-4 rounded-full text-sm font-bold">
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
                                        </td>
                                        <td class="py-5">
                                            <div class="space-y-1.5">
                                                <span class="text-xs text-gray-600 block leading-relaxed">{{ Str::limit($pickup->address, 35, '...') }}</span>
                                                @if($pickup->latitude && $pickup->longitude)
                                                    <button onclick="showMap({{ $pickup->latitude }}, {{ $pickup->longitude }}, '{{ addslashes($pickup->user->name) }}', '{{ addslashes(Str::limit($pickup->address, 60)) }}')"
                                                        class="inline-flex items-center gap-1.5 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold px-3 py-1.5 rounded-lg border border-blue-200 transition duration-200">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        Lihat Peta
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-xs text-gray-400 italic">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                        </svg>
                                                        Tanpa GPS
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-5">
                                            <span class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-lg font-semibold text-sm">
                                                📆 {{ \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td class="py-5 text-center">
                                            @if($pickup->status === 'pending')
                                                <span class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-bold text-sm">
                                                    <span class="w-2 h-2 bg-yellow-600 rounded-full animate-pulse"></span>⏳ Pending
                                                </span>
                                            @elseif($pickup->status === 'approved')
                                                <span class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-full font-bold text-sm">
                                                    <span class="w-2 h-2 bg-green-600 rounded-full animate-pulse"></span>✅ Approved
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-2 bg-red-100 text-red-800 px-4 py-2 rounded-full font-bold text-sm">
                                                    <span class="w-2 h-2 bg-red-600 rounded-full"></span>❌ Rejected
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-5">
                                            @if($pickup->handledBy)
                                                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">
                                                    🛡️ {{ $pickup->handledBy->name }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 italic">Belum ditangani</span>
                                            @endif
                                        </td>
                                        <td class="py-5 text-right">
                                            <div class="flex justify-end items-center gap-2">
                                                {{-- Tombol Detail --}}
                                                <button @click="openDetail({{ $detailData }})"
                                                    class="text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-bold px-3 py-2 border border-indigo-200 rounded-lg transition duration-200">
                                                    🔍 Detail
                                                </button>

                                                @if($pickup->status === 'pending')
                                                    <form action="{{ route('pickups.update', $pickup->id) }}" method="POST" class="flex items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="number" name="points_earned" placeholder="Poin" required
                                                            class="w-20 px-3 py-2 text-sm font-bold rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:outline-none bg-gray-50" />
                                                        <button type="submit" name="status" value="approved"
                                                            class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-lg font-bold text-sm shadow hover:shadow-lg transition duration-300">
                                                            ✅
                                                        </button>
                                                        <button type="submit" name="status" value="rejected"
                                                            class="bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-lg font-bold text-sm shadow hover:shadow-lg transition duration-300">
                                                            ❌
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('pickups.destroy.admin', $pickup->id) }}" method="POST"
                                                          onsubmit="return confirm('Yakin hapus data ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold px-3 py-2 rounded-lg transition">🗑️</button>
                                                    </form>
                                                @else
                                                    <div class="text-right">
                                                        <p class="text-lg font-black text-green-600">+{{ $pickup->points_earned }} 🏆</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">📭</div>
                            <p class="text-gray-500 text-lg">Belum ada setoran sampah masuk</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══ MODAL DETAIL SETORAN ═══ --}}
        <div x-show="isDetailOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[300] flex items-center justify-center bg-gray-900/60 backdrop-blur-md p-4"
             style="display:none;">
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
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 flex justify-between items-center flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/50">
                            <img :src="detail ? detail.user_photo : ''" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="text-white font-black text-lg" x-text="detail ? detail.user_name : ''"></h3>
                            <p class="text-blue-100 text-xs" x-text="detail ? detail.user_email : ''"></p>
                        </div>
                    </div>
                    <button @click="isDetailOpen = false"
                        class="w-9 h-9 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center text-white text-xl font-bold transition">✕</button>
                </div>

                <div class="overflow-y-auto flex-1 p-6 space-y-4">

                    {{-- Foto sampah --}}
                    <div x-show="detail && detail.photo">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">📸 Foto Sampah</p>
                        <img :src="detail ? detail.photo : ''"
                             class="w-full rounded-2xl object-cover max-h-56 shadow-sm border border-gray-100 cursor-pointer"
                             onclick="window.open(this.src, '_blank')">
                        <p class="text-xs text-gray-400 mt-1 text-center">Klik foto untuk perbesar</p>
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
                        <p class="text-xs text-amber-600 font-bold mb-1">📝 Catatan dari User</p>
                        <p class="text-sm text-gray-700" x-text="detail ? detail.notes : ''"></p>
                    </div>

                    {{-- Ditangani --}}
                    <div x-show="detail && detail.handled_by" class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4">
                        <p class="text-xs text-indigo-500 font-bold mb-1">🛡️ Ditangani Oleh</p>
                        <p class="font-black text-indigo-800 text-sm" x-text="detail ? detail.handled_by : ''"></p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <button @click="isDetailOpen = false"
                        class="w-full py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-bold transition duration-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Peta --}}
        <div id="mapModal" class="hidden fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/60 backdrop-blur-md p-4">
            <div class="bg-white rounded-3xl w-full max-w-2xl shadow-2xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black text-gray-900" id="mapModalTitle">📍 Lokasi Penjemputan</h3>
                        <p class="text-sm text-gray-500 mt-0.5 max-w-md truncate" id="mapModalSubtitle"></p>
                    </div>
                    <button onclick="closeMap()"
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-600 transition duration-200 text-xl font-bold">✕</button>
                </div>
                <div id="adminMap" style="height: 400px; width: 100%;"></div>
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-400">Powered by OpenStreetMap — 100% Gratis</p>
                    <a id="openGmapsBtn" href="#" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs transition duration-200">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka di Google Maps
                    </a>
                </div>
            </div>
        </div>

        {{-- Chart.js --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script>
            const wasteByType   = @json($wasteByType);
            const approvedCount = {{ $approvedPickups }};
            const pendingCount  = {{ $pendingPickups }};
            const rejectedCount = {{ $rejectedPickups }};
            const sevenDaysData = @json($last7Days);
            let chartsInitialized = false;

            function initCharts() {
                if (chartsInitialized) return;
                chartsInitialized = true;
                const wasteCtx = document.getElementById('wasteByTypeChart');
                if (wasteCtx) {
                    new Chart(wasteCtx, {
                        type: 'bar',
                        data: { labels: Object.keys(wasteByType), datasets: [{ label: 'Total Berat (Kg)', data: Object.values(wasteByType), backgroundColor: ['#10b981','#14b8a6','#06b6d4','#8b5cf6','#ec4899','#f59e0b','#6b7280'], borderRadius: 8 }] },
                        options: { responsive: true, animation: { duration: 500 }, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' kg' } } } }
                    });
                }
                const approvalCtx = document.getElementById('approvalRateChart');
                if (approvalCtx) {
                    new Chart(approvalCtx, {
                        type: 'doughnut',
                        data: { labels: ['Approved','Pending','Rejected'], datasets: [{ data: [approvedCount, pendingCount, rejectedCount], backgroundColor: ['#10b981','#f59e0b','#ef4444'], borderColor: '#fff', borderWidth: 2 }] },
                        options: { responsive: true, animation: { duration: 500 }, plugins: { legend: { position: 'bottom' } } }
                    });
                }
                const trendCtx = document.getElementById('sevenDaysTrendChart');
                if (trendCtx) {
                    new Chart(trendCtx, {
                        type: 'line',
                        data: { labels: Object.keys(sevenDaysData), datasets: [{ label: 'Sampah Diterima (Kg)', data: Object.values(sevenDaysData), borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', borderWidth: 3, fill: true, tension: 0.4, pointBackgroundColor: '#6366f1', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5 }] },
                        options: { responsive: true, animation: { duration: 500 }, scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' kg' } } } }
                    });
                }
            }

            const firstChart = document.getElementById('wasteByTypeChart');
            if (firstChart) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => { if (entry.isIntersecting) { initCharts(); observer.disconnect(); } });
                }, { threshold: 0.1 });
                observer.observe(firstChart);
            }

            let adminMap = null;

            function showMap(lat, lng, userName, address) {
                document.getElementById('mapModal').classList.remove('hidden');
                document.getElementById('mapModalTitle').textContent = '📍 Lokasi: ' + userName;
                document.getElementById('mapModalSubtitle').textContent = address;
                document.getElementById('openGmapsBtn').href = `https://maps.google.com/?q=${lat},${lng}`;
                setTimeout(() => {
                    if (adminMap) { adminMap.remove(); adminMap = null; }
                    adminMap = L.map('adminMap').setView([lat, lng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(adminMap);
                    const icon = L.divIcon({
                        html: `<div style="width:40px;height:40px;background:linear-gradient(135deg,#3b82f6,#6366f1);border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 4px 15px rgba(99,102,241,0.5);"></div>`,
                        className: '', iconSize: [40,40], iconAnchor: [20,40],
                    });
                    const m = L.marker([lat, lng], { icon }).addTo(adminMap);
                    m.bindPopup(`<div style="font-family:sans-serif;padding:4px;min-width:160px;"><p style="font-weight:800;font-size:14px;margin:0 0 4px;">${userName}</p><p style="font-size:11px;color:#6b7280;margin:0;">${address}</p></div>`).openPopup();
                    L.circle([lat, lng], { color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.1, radius: 50 }).addTo(adminMap);
                }, 150);
            }

            function closeMap() {
                document.getElementById('mapModal').classList.add('hidden');
                if (adminMap) { adminMap.remove(); adminMap = null; }
            }

            document.getElementById('mapModal').addEventListener('click', function(e) {
                if (e.target === this) closeMap();
            });
        </script>
    </div>
</x-app-layout>