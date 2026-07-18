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

        // Format pickups data
        $formattedPickups = $pickups->map(function($pickup) {
            return [
                'id' => $pickup->id,
                'user_name' => optional($pickup->user)->name ?? 'User',
                'user_email' => optional($pickup->user)->email ?? '',
                'user_photo' => optional($pickup->user)->getPhotoUrl() ?? '',
                'type' => $pickup->type,
                'weight' => $pickup->weight,
                'pickup_date' => \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y'),
                'pickup_date_raw' => \Carbon\Carbon::parse($pickup->pickup_date)->format('Y-m-d'),
                'address' => $pickup->address,
                'phone' => $pickup->phone,
                'status' => $pickup->status,
                'points' => $pickup->points_earned,
                'notes' => $pickup->notes,
                'photo' => $pickup->photo ? \Illuminate\Support\Facades\Storage::url($pickup->photo) : null,
                'latitude' => $pickup->latitude,
                'longitude' => $pickup->longitude,
                'handled_by' => optional($pickup->handledBy)->name,
                'maps_url' => $pickup->latitude ? "https://maps.google.com/?q={$pickup->latitude},{$pickup->longitude}" : null,
            ];
        });
    @endphp

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <div x-data="{
            isDetailOpen: false,
            detail: null,
            openApproveId: null,
            openDetail(data) { this.detail = data; this.isDetailOpen = true; },
            pickups: @js($formattedPickups),
            stats: {
                totalPickups: {{ $totalPickups }},
                pendingPickups: {{ $pendingPickups }},
                approvedPickups: {{ $approvedPickups }},
                totalWeight: {{ $totalWeight }},
                todayWeight: {{ $todayWeight }},
                totalUsers: {{ $totalUsers }},
                approvalRate: {{ $approvalRate }}
            },
            init() {
                // Polling data realtime setiap 5 detik
                setInterval(() => {
                    fetch('{{ route('admin.realtime') }}')
                        .then(res => res.json())
                        .then(data => {
                            this.pickups = data.pickups;
                            this.stats.totalPickups = data.totalPickups;
                            this.stats.pendingPickups = data.pendingPickups;
                            this.stats.approvedPickups = data.approvedPickups;
                            this.stats.totalWeight = data.totalWeight;
                            this.stats.todayWeight = data.todayWeight;
                            this.stats.totalUsers = data.totalUsers;
                            this.stats.approvalRate = data.approvalRate;
                            
                            // Update Chart jika fungsi chart sudah didefinisikan
                            if (window.updateRealtimeCharts) {
                                window.updateRealtimeCharts(data.wasteByType, data.approvedPickups, data.pendingPickups, data.rejectedPickups, data.last7Days);
                            }
                        })
                        .catch(err => console.error('Realtime sync error:', err));
                }, 5000);
            }
         }"
         class="min-h-screen bg-transparent py-8 relative">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex justify-between items-center shadow-sm">
                    <span class="font-bold text-sm">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">✕</button>
                </div>
            @endif



            {{-- 4 STATS CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <!-- Card 1: Total Setoran -->
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 relative flex items-center justify-between min-h-[110px] overflow-hidden">
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-gray-900" x-text="stats.totalPickups">0</span>
                        <span class="text-xs text-gray-400 font-semibold mt-1">Total Setoran</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>

                <!-- Card 2: Pending -->
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 relative flex items-center justify-between min-h-[110px] overflow-hidden">
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-gray-900" x-text="stats.pendingPickups">0</span>
                        <span class="text-xs text-gray-400 font-semibold mt-1">Menunggu Verifikasi</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Card 3: Disetujui -->
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 relative flex items-center justify-between min-h-[110px] overflow-hidden">
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-gray-900" x-text="stats.approvedPickups">0</span>
                        <span class="text-xs text-gray-400 font-semibold mt-1">Sudah Disetujui</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Card 4: Total Berat -->
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 relative flex items-center justify-between min-h-[110px] overflow-hidden">
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-gray-900" x-text="parseFloat(stats.totalWeight).toFixed(1)">0.0</span>
                        <span class="text-xs text-gray-400 font-semibold mt-1">Total Berat Diterima (kg)</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-violet-500 flex items-center justify-center text-white shadow-lg shadow-violet-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- 3 mini stat cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-5">
                    <p class="text-xs text-gray-400 font-bold uppercase">Hari Ini</p>
                    <p class="text-2xl font-black text-blue-600 mt-1" x-text="parseFloat(stats.todayWeight).toFixed(1) + ' kg'">0.0 kg</p>
                </div>
                <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-5">
                    <p class="text-xs text-gray-400 font-bold uppercase">Total User Aktif</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1" x-text="stats.totalUsers">0</p>
                </div>
                <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-5">
                    <p class="text-xs text-gray-400 font-bold uppercase">Approval Rate</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1" x-text="parseFloat(stats.approvalRate).toFixed(1) + '%'">0%</p>
                </div>
            </div>

            {{-- 3 CHARTS IN ROW --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-[24px] border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm">Jenis Sampah</h3>
                    <div class="h-[200px] w-full"><canvas id="wasteByTypeChart"></canvas></div>
                </div>
                <div class="bg-white p-6 rounded-[24px] border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm">Status Setoran</h3>
                    <div class="flex justify-center items-center h-[200px]">
                        <div class="relative w-40 h-40">
                            <canvas id="approvalRateChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[24px] border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm">Tren 7 Hari</h3>
                    <div class="h-[200px] w-full"><canvas id="sevenDaysTrendChart"></canvas></div>
                </div>
            </div>

            {{-- Tabel Setoran --}}
            <div x-data="{ searchQuery: '', filterStatus: '', filterType: '', filterDateFrom: '', filterDateTo: '' }"
                 class="bg-white rounded-[24px] border border-gray-100 shadow-sm overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-50">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Antrian Setor Sampah</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-3">
                        <div class="relative md:col-span-3 xl:col-span-2">
                            <input type="text" x-model="searchQuery" placeholder="Cari nama atau email user..."
                                class="w-full pl-10 pr-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition" />
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <select x-model="filterStatus"
                            class="px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                        <select x-model="filterType"
                            class="px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
                            <option value="">Semua Jenis</option>
                            <option value="Plastik">Plastik</option>
                            <option value="Kertas">Kertas</option>
                            <option value="Logam">Logam</option>
                            <option value="Kaca">Kaca</option>
                            <option value="Organik">Organik</option>
                            <option value="Elektronik">⚡ Elektronik</option>
                            <option value="Lainnya">📦 Lainnya</option>
                        </select>
                        <input type="date" x-model="filterDateFrom"
                            class="px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
                        <input type="date" x-model="filterDateTo"
                            class="px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
                        <button @click="searchQuery=''; filterStatus=''; filterType=''; filterDateFrom=''; filterDateTo=''"
                            class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-2xl font-bold text-sm transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div x-show="pickups.length > 0">
                        <table class="w-full text-left">
                                                       <tr class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                    <th class="pb-3 px-6">User</th>
                                    <th class="pb-3 px-2">Jenis</th>
                                    <th class="pb-3 px-2">Berat</th>
                                    <th class="pb-3 px-2">Tanggal</th>
                                    <th class="pb-3 px-2">Status</th>
                                    <th class="pb-3 px-2">Ditangani</th>
                                    <th class="pb-3 pr-6 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="pickup in pickups" :key="pickup.id">
                                    <tr class="hover:bg-gray-50/50 transition"
                                        x-show="
                                            (searchQuery === '' || pickup.user_name.toLowerCase().includes(searchQuery.toLowerCase()) || pickup.user_email.toLowerCase().includes(searchQuery.toLowerCase())) &&
                                            (filterStatus === '' || filterStatus === pickup.status) &&
                                            (filterType === '' || filterType === pickup.type) &&
                                            (filterDateFrom === '' || pickup.pickup_date_raw >= filterDateFrom) &&
                                            (filterDateTo === '' || pickup.pickup_date_raw <= filterDateTo)
                                        ">
                                        <td class="py-4 pl-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full overflow-hidden bg-blue-50 border border-blue-100 flex-shrink-0">
                                                    <img :src="pickup.user_photo" :alt="pickup.user_name" class="w-full h-full object-cover"
                                                        :onerror="`this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(pickup.user_name) + '&background=10b981&color=fff&bold=true&size=128'`">
                                                </div>
                                                <span class="font-bold text-gray-900 text-sm" x-text="pickup.user_name"></span>
                                            </div>
                                        </td>
                                        <td class="py-4 text-gray-500 font-semibold text-sm" x-text="pickup.type"></td>
                                        <td class="py-4 text-gray-900 font-semibold text-sm" x-text="pickup.weight + ' kg'"></td>
                                        <td class="py-4 text-gray-400 text-xs font-bold" x-text="pickup.pickup_date"></td>
                                        <td class="py-4">
                                            <template x-if="pickup.status === 'pending'">
                                                <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100">Pending</span>
                                            </template>
                                            <template x-if="pickup.status === 'approved'">
                                                <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">Disetujui</span>
                                            </template>
                                            <template x-if="pickup.status === 'rejected'">
                                                <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-full bg-red-50 text-red-600 border border-red-100">Ditolak</span>
                                            </template>
                                        </td>
                                        <td class="py-4 text-gray-500 text-xs font-semibold" x-text="pickup.handled_by || '-'"></td>
                                        <td class="py-4 pr-6 text-center">
                                            <div class="flex justify-center items-center gap-2">
                                                <button @click="openDetail(pickup)" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-xl transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </button>
                                                <template x-if="pickup.latitude && pickup.longitude">
                                                    <button type="button"
                                                        @click="showMap(pickup.latitude, pickup.longitude, pickup.user_name, pickup.address.slice(0, 80))"
                                                        class="p-1.5 text-sky-500 hover:bg-sky-50 rounded-xl transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                                        </svg>
                                                    </button>
                                                </template>
                                                <template x-if="pickup.status === 'pending'">
                                                    <div class="relative inline-block text-left">
                                                        <button @click="openApproveId = (openApproveId === pickup.id ? null : pickup.id)" class="p-1.5 text-emerald-500 hover:bg-emerald-50 rounded-xl transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </button>
                                                        <div x-show="openApproveId === pickup.id" @click.away="openApproveId = null" style="display: none;"
                                                             class="origin-top-right absolute right-0 mt-2 w-56 rounded-2xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-[500] p-4 text-left font-sans">
                                                            <form :action="`/pickups/${pickup.id}`" method="POST">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="approved">
                                                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Beri Poin Reward</label>
                                                                <input type="number" name="points_earned" required min="1" placeholder="Misal: 50"
                                                                       class="w-full px-3 py-2 border border-gray-200 focus:border-blue-500 rounded-xl text-sm focus:outline-none mb-3">
                                                                <button type="submit" class="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold text-xs transition">
                                                                    Konfirmasi Setuju
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="pickup.status === 'pending'">
                                                    <form :action="`/pickups/${pickup.id}`" method="POST" class="inline" onsubmit="return confirm('Tolak setoran ini?');">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-xl transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </template>
                                                <form :action="`/pickups/${pickup.id}/admin`" method="POST" class="inline" onsubmit="return confirm('Hapus data setoran ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3 0V5a2 2 0 012-2h0a2 2 0 012 2v2"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center py-12" x-show="pickups.length === 0">
                        <p class="text-gray-300 text-4xl mb-3">📭</p>
                        <p class="text-gray-400 text-sm">Belum ada setoran sampah masuk</p>
                    </div>
                </div>
            </div>
        {{-- ═══ MODAL DETAIL SETORAN ═══ --}}
        <div x-show="isDetailOpen"
             class="fixed inset-0 z-[300] flex items-center justify-center p-4"
             style="display:none;">
            <!-- Backdrop Blur (Ditempatkan di elemen terpisah & statis agar tidak ter-redraw saat modal di-scroll) -->
            <div x-show="isDetailOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-gray-950/40 backdrop-blur-[6px]"
                 @click="isDetailOpen = false"></div>

            <!-- Konten Modal -->
            <div @click.away="isDetailOpen = false"
                 x-show="isDetailOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col max-h-[85vh] relative z-10">

                {{-- Header --}}
                <div class="bg-white border-b border-gray-100 px-6 py-4 flex justify-between items-center flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full overflow-hidden border border-gray-100">
                            <img :src="detail ? detail.user_photo : ''" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="text-gray-900 font-bold text-base" x-text="detail ? detail.user_name : ''"></h3>
                            <p class="text-gray-400 text-xs" x-text="detail ? detail.user_email : ''"></p>
                        </div>
                    </div>
                    <button @click="isDetailOpen = false"
                        class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center text-gray-500 font-bold transition">✕</button>
                </div>

                <div class="overflow-y-auto flex-1 p-6 space-y-4">

                    {{-- Foto sampah --}}
                    <div x-show="detail && detail.photo">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Foto Sampah</p>
                        <img :src="detail ? detail.photo : ''"
                             class="w-full rounded-2xl object-cover max-h-56 shadow-sm border border-gray-100 cursor-pointer"
                             onclick="window.open(this.src, '_blank')">
                        <p class="text-xs text-gray-400 mt-1.5 text-center">Klik foto untuk memperbesar</p>
                    </div>

                    {{-- Status --}}
                    <div class="flex items-center justify-between p-4 rounded-2xl border"
                         :class="{
                            'bg-amber-50/50 border-amber-100 text-amber-700': detail && detail.status === 'pending',
                            'bg-emerald-50/50 border-emerald-100 text-emerald-700': detail && detail.status === 'approved',
                            'bg-red-50/50 border-red-100 text-red-700': detail && detail.status === 'rejected'
                         }">
                        <span class="font-bold text-sm">
                            <span x-text="detail ? (detail.status === 'pending' ? '⏳ Menunggu Verifikasi' : detail.status === 'approved' ? '✅ Disetujui' : '❌ Ditolak') : ''"></span>
                        </span>
                        <span x-show="detail && detail.status === 'approved'"
                              class="text-emerald-700 font-black text-sm"
                              x-text="detail ? '+' + detail.points + ' poin 🏆' : ''">
                        </span>
                    </div>

                    {{-- Info grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50/70 border border-gray-100 rounded-2xl p-4">
                            <p class="text-xs text-gray-400 font-bold mb-0.5">Jenis Sampah</p>
                            <p class="font-bold text-gray-900 text-sm" x-text="detail ? detail.type : ''"></p>
                        </div>
                        <div class="bg-gray-50/70 border border-gray-100 rounded-2xl p-4">
                            <p class="text-xs text-gray-400 font-bold mb-0.5">Berat</p>
                            <p class="font-bold text-gray-900 text-sm" x-text="detail ? detail.weight + ' kg' : ''"></p>
                        </div>
                        <div class="bg-gray-50/70 border border-gray-100 rounded-2xl p-4">
                            <p class="text-xs text-gray-400 font-bold mb-0.5">Tanggal Jemput</p>
                            <p class="font-bold text-gray-900 text-xs" x-text="detail ? detail.pickup_date : ''"></p>
                        </div>
                        <div class="bg-gray-50/70 border border-gray-100 rounded-2xl p-4">
                            <p class="text-xs text-gray-400 font-bold mb-0.5">No. Telepon</p>
                            <p class="font-bold text-gray-900 text-xs" x-text="detail ? detail.phone : ''"></p>
                        </div>
                    </div>

                    {{-- Alamat --}}
                    <div class="bg-gray-50/70 border border-gray-100 rounded-2xl p-4">
                        <p class="text-xs text-gray-400 font-bold mb-1">📍 Alamat Penjemputan</p>
                        <p class="font-semibold text-gray-700 text-xs leading-relaxed" x-text="detail ? detail.address : ''"></p>
                        <a x-show="detail && detail.maps_url"
                           :href="detail ? detail.maps_url : '#'"
                           target="_blank"
                           class="inline-flex items-center gap-1 text-[11px] text-blue-600 hover:underline mt-2 font-bold">
                            Lihat di Google Maps
                        </a>
                    </div>

                    {{-- Catatan --}}
                    <div x-show="detail && detail.notes" class="bg-amber-50/30 border border-amber-100 rounded-2xl p-4">
                        <p class="text-xs text-amber-700 font-bold mb-1">📝 Catatan dari User</p>
                        <p class="text-xs text-gray-600 leading-relaxed" x-text="detail ? detail.notes : ''"></p>
                    </div>

                    {{-- Ditangani --}}
                    <div x-show="detail && detail.handled_by" class="bg-blue-50/30 border border-blue-100 rounded-2xl p-4">
                        <p class="text-xs text-blue-700 font-bold mb-1">🛡️ Ditangani Oleh</p>
                        <p class="font-bold text-blue-800 text-xs" x-text="detail ? detail.handled_by : ''"></p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <button @click="isDetailOpen = false"
                        class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl font-bold text-sm transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>>
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

            let wasteChart = null, approvalChart = null, trendChart = null;

            function initCharts() {
                if (chartsInitialized) return;
                chartsInitialized = true;
                const wasteCtx = document.getElementById('wasteByTypeChart');
                if (wasteCtx) {
                    wasteChart = new Chart(wasteCtx, {
                        type: 'bar',
                        data: { labels: Object.keys(wasteByType), datasets: [{ label: 'Total Berat (Kg)', data: Object.values(wasteByType), backgroundColor: ['#10b981','#14b8a6','#06b6d4','#8b5cf6','#ec4899','#f59e0b','#6b7280'], borderRadius: 8 }] },
                        options: { responsive: true, animation: { duration: 500 }, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' kg' } } } }
                    });
                }
                const approvalCtx = document.getElementById('approvalRateChart');
                if (approvalCtx) {
                    approvalChart = new Chart(approvalCtx, {
                        type: 'doughnut',
                        data: { labels: ['Approved','Pending','Rejected'], datasets: [{ data: [approvedCount, pendingCount, rejectedCount], backgroundColor: ['#10b981','#f59e0b','#ef4444'], borderColor: '#fff', borderWidth: 2 }] },
                        options: { responsive: true, animation: { duration: 500 }, plugins: { legend: { position: 'bottom' } } }
                    });
                }
                const trendCtx = document.getElementById('sevenDaysTrendChart');
                if (trendCtx) {
                    trendChart = new Chart(trendCtx, {
                        type: 'line',
                        data: { labels: Object.keys(sevenDaysData), datasets: [{ label: 'Sampah Diterima (Kg)', data: Object.values(sevenDaysData), borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', borderWidth: 3, fill: true, tension: 0.4, pointBackgroundColor: '#6366f1', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5 }] },
                        options: { responsive: true, animation: { duration: 500 }, scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' kg' } } } }
                    });
                }
            }

            // Realtime chart update function - dipanggil dari polling interval Alpine.js
            window.updateRealtimeCharts = function(wasteByTypeNew, approvedNew, pendingNew, rejectedNew, last7DaysNew) {
                if (wasteChart) {
                    wasteChart.data.datasets[0].data = Object.values(wasteByTypeNew);
                    wasteChart.update('none');
                }
                if (approvalChart) {
                    approvalChart.data.datasets[0].data = [approvedNew, pendingNew, rejectedNew];
                    approvalChart.update('none');
                }
                if (trendChart) {
                    trendChart.data.labels = Object.keys(last7DaysNew);
                    trendChart.data.datasets[0].data = Object.values(last7DaysNew);
                    trendChart.update('none');
                }
            };

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