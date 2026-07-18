<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center shadow-lg">
                <span class="text-2xl">⭐</span>
            </div>
            <div>
                <h2 class="font-extrabold text-3xl bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">
                    Super Admin Panel
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
        $wasteByType = [];
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

        // Format & Gabung data logs
        $formattedLogs = [];
        foreach($activityLogs as $log) {
            $formattedLogs[] = [
                'type' => 'admin',
                'admin_name' => optional($log->admin)->name ?? 'Admin',
                'action' => $log->action,
                'user_name' => $log->user_name,
                'waste_type' => $log->waste_type,
                'waste_weight' => $log->waste_weight,
                'points_given' => $log->points_given,
                'date' => \Carbon\Carbon::parse($log->created_at)->format('d M Y'),
                'time' => \Carbon\Carbon::parse($log->created_at)->format('H:i'),
                'timestamp' => \Carbon\Carbon::parse($log->created_at)->timestamp
            ];
        }
        foreach($pickups as $pickup) {
            $formattedLogs[] = [
                'type' => 'user',
                'user_name' => optional($pickup->user)->name ?? 'User',
                'waste_type' => $pickup->type,
                'waste_weight' => $pickup->weight,
                'date' => \Carbon\Carbon::parse($pickup->created_at)->format('d M Y'),
                'time' => \Carbon\Carbon::parse($pickup->created_at)->format('H:i'),
                'timestamp' => \Carbon\Carbon::parse($pickup->created_at)->timestamp,
                'status' => $pickup->status
            ];
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

        // Urutkan logs berdasarkan waktu terbaru (descending)
        usort($formattedLogs, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
    @endphp

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <div x-data="{
            activeTab: 'setoran',
            isDetailOpen: false,
            detail: null,
            openApproveId: null,
            openDetail(data) { this.detail = data; this.isDetailOpen = true; },
            isEditRewardOpen: false,
            editRewardData: { id: '', name: '', points_required: '', description: '', stock: '' },
            openEditReward(reward) {
                this.editRewardData = {
                    id: reward.id,
                    name: reward.name,
                    points_required: reward.points_required,
                    description: reward.description || '',
                    stock: reward.stock !== null ? reward.stock : ''
                };
                this.isEditRewardOpen = true;
            },
            logFilter: 'all',
            activityLogs: @js($formattedLogs),
            pickups: @js($formattedPickups),
            stats: {
                totalPickups: {{ $totalPickups }},
                pendingPickups: {{ $pendingPickups }},
                approvedPickups: {{ $approvedPickups }},
                todayWeight: {{ $todayWeight }},
                totalWeight: {{ $totalWeight }},
                verifiedAdminsCount: {{ $verifiedAdmins->count() }}
            },
            get filteredLogs() {
                if (this.logFilter === 'all') {
                    return this.activityLogs;
                }
                return this.activityLogs.filter(log => log.type === this.logFilter);
            },
            init() {
                // Polling data realtime setiap 5 detik
                setInterval(() => {
                    fetch('{{ route('superadmin.realtime') }}')
                        .then(res => res.json())
                        .then(data => {
                            this.activityLogs = data.activityLogs;
                            this.pickups = data.pickups;
                            this.stats.totalPickups = data.totalPickups;
                            this.stats.pendingPickups = data.pendingPickups;
                            this.stats.approvedPickups = data.approvedPickups;
                            this.stats.todayWeight = data.todayWeight;
                            this.stats.totalWeight = data.totalWeight;
                            
                            // Update Chart jika fungsi chart sudah diinisialisasi
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




            {{-- 4 STATS CARDS FIGMA-STYLE --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <!-- Card 1: Total Setoran -->
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 relative flex items-center justify-between min-h-[110px] overflow-hidden">
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-gray-900" x-text="stats.totalPickups">0</span>
                        <span class="text-xs text-gray-400 font-semibold mt-1">Total Setoran</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>

                <!-- Card 2: Admin Pending -->
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 relative flex items-center justify-between min-h-[110px] overflow-hidden">
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-gray-900">{{ $pendingAdmins->count() }}</span>
                        <span class="text-xs text-gray-400 font-semibold mt-1">Admin Pending</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-orange-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Card 3: Total User -->
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 relative flex items-center justify-between min-h-[110px] overflow-hidden">
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-gray-900">{{ $users->count() }}</span>
                        <span class="text-xs text-gray-400 font-semibold mt-1">Total User</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Card 4: Sampah Hari Ini -->
                <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm p-6 relative flex items-center justify-between min-h-[110px] overflow-hidden">
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-gray-900" x-text="parseFloat(stats.todayWeight).toFixed(1)">0.0</span>
                        <span class="text-xs text-gray-400 font-semibold mt-1">Sampah Hari Ini (kg)</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
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
                    <p class="text-xs text-gray-400 font-bold uppercase">Total Admin</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1" x-text="stats.verifiedAdminsCount">0</p>
                </div>
                <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-5">
                    <p class="text-xs text-gray-400 font-bold uppercase">Total Disetujui</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1" x-text="stats.approvedPickups">0</p>
                </div>
            </div>

            {{-- 3 CHARTS IN ROW FIGMA-STYLE --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-[24px] border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm">Jenis Sampah</h3>
                    <div class="h-[200px] w-full"><canvas id="wasteByTypeChart"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-[24px] border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm">Status Distribusi</h3>
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

            {{-- Tab Navigation --}}
            <div class="bg-white rounded-[24px] border border-gray-100 shadow-sm overflow-hidden mb-8">
                <div class="flex border-b border-gray-100 px-6 overflow-x-auto">
                    @foreach([
                        ['tab' => 'setoran',    'label' => '📦 Setoran'],
                        ['tab' => 'admins',     'label' => '🛡️ Admin'],
                        ['tab' => 'users',      'label' => '👥 User'],
                        ['tab' => 'katalog',    'label' => '🎁 Katalog Hadiah'],
                        ['tab' => 'activitylog','label' => '📋 Activity Log'],
                    ] as $t)
                    <button @click="activeTab = '{{ $t['tab'] }}'"
                        :class="activeTab === '{{ $t['tab'] }}' ? 'border-b-2 border-amber-500 text-amber-600 font-bold' : 'text-gray-400 hover:text-gray-600 font-medium'"
                        class="px-5 py-4 text-sm whitespace-nowrap transition duration-200 flex-shrink-0 focus:outline-none">
                        {{ $t['label'] }}
                    @endforeach
                </div>

                {{-- TAB: SETORAN --}}
                <div x-show="activeTab === 'setoran'" x-data="{ searchQuery: '', filterStatus: '', filterType: '', filterDateFrom: '', filterDateTo: '' }">
                    {{-- SEARCH BAR - Admin Style --}}
                    <div class="p-4 border-b border-gray-50">
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
                                @foreach(['Plastik','Kertas','Logam','Kaca','Organik','Elektronik','Lainnya'] as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            <input type="date" x-model="filterDateFrom"
                                class="px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition" />
                            <input type="date" x-model="filterDateTo"
                                class="px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition" />
                            <button type="button" @click="searchQuery=''; filterStatus=''; filterType=''; filterDateFrom=''; filterDateTo=''"
                                class="px-4 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm transition">
                                Reset
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <div x-show="pickups.length > 0">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-xs font-bold text-gray-400 uppercase border-b border-gray-50">
                                        <th class="py-4 pl-6">User</th>
                                        <th class="py-4">Jenis</th>
                                        <th class="py-4">Berat</th>
                                        <th class="py-4">Tanggal</th>
                                        <th class="py-4">Status</th>
                                        <th class="py-4">Ditangani</th>
                                        <th class="py-4 pr-6 text-center">Aksi</th>
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
                                                                  class="origin-top-right absolute right-0 mt-2 w-56 rounded-2xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-[500] p-4 text-left">
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
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center py-12" x-show="pickups.length === 0">
                            <div class="text-6xl mb-4">📭</div>
                            <p class="text-gray-500">Belum ada setoran masuk</p>
                        </div>
                    </div>
                </div>

                {{-- TAB: ADMIN --}}
                <div x-show="activeTab === 'admins'" class="p-6 space-y-8">
                    {{-- Online Status --}}
                    <div>
                        <div class="mb-4">
                            <h3 class="text-base font-bold text-gray-900">Status Online Admin</h3>
                            <p class="text-xs text-gray-400">Admin yang sedang aktif di dashboard</p>
                        </div>
                        @if($verifiedAdmins->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($verifiedAdmins as $admin)
                                    <div class="flex items-center gap-3 p-4 rounded-2xl border border-gray-100 bg-white shadow-sm">
                                        <div class="relative">
                                            <div class="w-10 h-10 rounded-full overflow-hidden bg-blue-50 border border-blue-100 flex-shrink-0">
                                                <img src="{{ $admin->getPhotoUrl() }}" class="w-full h-full object-cover"
                                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($admin->name) }}&background=3b82f6&color=fff&bold=true&size=128'">
                                            </div>
                                            <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white {{ $admin->isOnline() ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-gray-950 text-sm truncate">{{ $admin->name }}</p>
                                            <p class="text-xs text-gray-400 truncate">{{ $admin->email }}</p>
                                            @if($admin->isOnline())
                                                <span class="inline-flex items-center text-[10px] font-bold text-emerald-600 mt-0.5">
                                                    Online sekarang
                                                </span>
                                            @else
                                                <span class="text-[10px] text-gray-400 block mt-0.5">{{ $admin->last_seen_at ? $admin->last_seen_at->diffForHumans() : 'Offline' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center shadow-sm">
                                <p class="text-gray-400 text-xs font-semibold">Belum ada admin</p>
                            </div>
                        @endif
                    </div>

                    {{-- Admin Pending --}}
                    <div>
                        <div class="mb-4">
                            <h3 class="text-base font-bold text-gray-900">Admin Menunggu Verifikasi</h3>
                            <p class="text-xs text-gray-400">Akun admin baru yang perlu disetujui</p>
                        </div>
                        @if($pendingAdmins->count() > 0)
                            <div class="space-y-3">
                                @foreach($pendingAdmins as $admin)
                                    <div class="flex items-center justify-between bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full overflow-hidden bg-blue-50 border border-blue-100 flex-shrink-0">
                                                <img src="{{ $admin->getPhotoUrl() }}" class="w-full h-full object-cover"
                                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($admin->name) }}&background=f59e0b&color=fff&bold=true&size=128'">
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-950 text-sm">{{ $admin->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $admin->email }}</p>
                                                <p class="text-[10px] text-gray-400 mt-0.5 font-bold">Daftar: {{ $admin->created_at->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('superadmin.verify', $admin->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold text-xs shadow-sm transition">Setujui</button>
                                            </form>
                                            <form action="{{ route('superadmin.delete', $admin->id) }}" method="POST" onsubmit="return confirm('Tolak pendaftaran admin ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-bold text-xs transition">Tolak</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center shadow-sm">
                                <p class="text-gray-400 text-xs font-semibold">Tidak ada admin yang menunggu verifikasi</p>
                            </div>
                        @endif
                    </div>

                    {{-- Admin Aktif --}}
                    <div>
                        <div class="mb-4">
                            <h3 class="text-base font-bold text-gray-900">Admin Aktif</h3>
                            <p class="text-xs text-gray-400">Daftar admin yang sudah terverifikasi</p>
                        </div>
                        @if($verifiedAdmins->count() > 0)
                            <div class="space-y-3">
                                @foreach($verifiedAdmins as $admin)
                                    <div class="flex items-center justify-between bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                <div class="w-10 h-10 rounded-full overflow-hidden bg-blue-50 border border-blue-100 flex-shrink-0">
                                                    <img src="{{ $admin->getPhotoUrl() }}" class="w-full h-full object-cover"
                                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($admin->name) }}&background=10b981&color=fff&bold=true&size=128'">
                                                </div>
                                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-white {{ $admin->isOnline() ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-950 text-sm">{{ $admin->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $admin->email }}</p>
                                                <p class="text-[10px] text-gray-400 mt-0.5 font-bold">Bergabung: {{ $admin->created_at->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('superadmin.delete', $admin->id) }}" method="POST" onsubmit="return confirm('Hapus akun admin ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3 0V5a2 2 0 012-2h0a2 2 0 012 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center shadow-sm">
                                <p class="text-gray-400 text-xs font-semibold">Belum ada admin aktif</p>
                            </div>
                        @endif
                    </div>
                </div>
                              {{-- TAB: USER --}}
                <div x-show="activeTab === 'users'" class="p-6 space-y-6">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-gray-900">Semua User Terdaftar</h3>
                        <p class="text-xs text-gray-400">Kelola semua akun user di EcoDrop</p>
                    </div>
                    @if($users->count() > 0)
                        <div class="space-y-3">
                            @foreach($users as $user)
                                <div class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 bg-white shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full overflow-hidden bg-blue-50 border border-blue-100 flex-shrink-0">
                                            <img src="{{ $user->getPhotoUrl() }}" class="w-full h-full object-cover"
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6b7280&color=fff&bold=true&size=128'">
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-gray-950 text-sm">{{ $user->name }}</p>
                                                @if($user->is_banned)
                                                    <span class="text-[10px] bg-red-50 text-red-600 font-bold px-2 py-0.5 rounded-full border border-red-100">Banned</span>
                                                @else
                                                    <span class="text-[10px] bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded-full border border-emerald-100">Aktif</span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                            <p class="text-[10px] text-gray-400 font-bold mt-0.5">Daftar: {{ $user->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-6">
                                        <div class="hidden md:flex items-center gap-4 text-center">
                                            <div>
                                                <p class="text-sm font-bold text-gray-950">{{ $user->points }}</p>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase">Poin</p>
                                            </div>
                                            <div class="w-px h-8 bg-gray-100"></div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-950">{{ $user->pickups_count }}</p>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase">Setoran</p>
                                            </div>
                                        </div>
                                        <form action="{{ route('superadmin.ban', $user->id) }}" method="POST"
                                              onsubmit="return confirm('{{ $user->is_banned ? 'Aktifkan kembali user ini?' : 'Yakin ban user ini?' }}');">
                                            @csrf @method('PATCH')
                                            @if($user->is_banned)
                                                <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold text-xs shadow-sm transition">Aktifkan</button>
                                            @else
                                                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold text-xs shadow-sm transition">Ban User</button>
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center shadow-sm">
                            <p class="text-gray-400 text-xs font-semibold">Belum ada user terdaftar</p>
                        </div>
                        </div>
                    @endif
                </div>

                {{-- TAB: ACTIVITY LOG --}}
                <div x-show="activeTab === 'activitylog'" class="p-6 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Activity Log</h3>
                            <p class="text-xs text-gray-400">Semua riwayat aktivitas operasional EcoDrop</p>
                        </div>
                        {{-- Log Filter Buttons --}}
                        <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl w-fit">
                            <button @click="logFilter = 'all'"
                                :class="logFilter === 'all' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-900 font-medium'"
                                class="px-3.5 py-1.5 text-xs rounded-lg transition duration-150">
                                Semua
                            </button>
                            <button @click="logFilter = 'admin'"
                                :class="logFilter === 'admin' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-900 font-medium'"
                                class="px-3.5 py-1.5 text-xs rounded-lg transition duration-150">
                                Aktivitas Admin
                            </button>
                            <button @click="logFilter = 'user'"
                                :class="logFilter === 'user' ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-900 font-medium'"
                                class="px-3.5 py-1.5 text-xs rounded-lg transition duration-150">
                                Aktivitas User
                            </button>
                        </div>
                    </div>

                    {{-- Alpine Loop Activity Logs --}}
                    <div class="space-y-3" x-show="filteredLogs.length > 0">
                        <template x-for="log in filteredLogs">
                            <div class="flex items-start justify-between p-4 rounded-2xl border border-gray-100 bg-white shadow-sm gap-4">
                                <div class="flex items-start gap-3">
                                    {{-- Render Icon based on Log Type & Action --}}
                                    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center border"
                                        :class="{
                                            'bg-emerald-50 text-emerald-600 border-emerald-100': log.type === 'admin' && log.action === 'approved',
                                            'bg-red-50 text-red-600 border-red-100': log.type === 'admin' && log.action === 'rejected',
                                            'bg-gray-50 text-gray-500 border-gray-100': log.type === 'admin' && log.action !== 'approved' && log.action !== 'rejected',
                                            'bg-blue-50 text-blue-600 border-blue-100': log.type === 'user'
                                        }">
                                        
                                        {{-- Approved Icon --}}
                                        <template x-if="log.type === 'admin' && log.action === 'approved'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                        {{-- Rejected Icon --}}
                                        <template x-if="log.type === 'admin' && log.action === 'rejected'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </template>
                                        {{-- Trash / Delete Icon --}}
                                        <template x-if="log.type === 'admin' && log.action !== 'approved' && log.action !== 'rejected'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3 0V5a2 2 0 012-2h0a2 2 0 012 2v2"/></svg>
                                        </template>
                                        {{-- User Pickup / Setoran Icon --}}
                                        <template x-if="log.type === 'user'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </template>
                                    </div>

                                    <div>
                                        {{-- Text for Admin Log --}}
                                        <template x-if="log.type === 'admin'">
                                            <p class="font-bold text-gray-950 text-sm">
                                                <span class="text-blue-600" x-text="log.admin_name"></span>
                                                <span x-text="log.action === 'approved' ? 'menyetujui' : (log.action === 'rejected' ? 'menolak' : 'menghapus')"></span>
                                                setoran milik <span class="text-gray-900" x-text="log.user_name"></span>
                                            </p>
                                        </template>
                                        {{-- Text for User Log --}}
                                        <template x-if="log.type === 'user'">
                                            <p class="font-bold text-gray-950 text-sm">
                                                <span class="text-emerald-600" x-text="log.user_name"></span> mengajukan setoran baru jenis <span class="text-gray-900" x-text="log.waste_type"></span>
                                            </p>
                                        </template>

                                        <p class="text-xs text-gray-400 mt-0.5">
                                            <span x-text="log.waste_type"></span> · <span x-text="log.waste_weight"></span> Kg
                                            <template x-if="log.type === 'admin' && log.points_given">
                                                <span> · <span class="text-emerald-600 font-bold" x-text="'+' + log.points_given + ' Poin'"></span></span>
                                            </template>
                                            <template x-if="log.type === 'user'">
                                                <span> · Status: <span class="font-bold" :class="{'text-amber-500': log.status === 'pending', 'text-emerald-600': log.status === 'approved', 'text-red-500': log.status === 'rejected'}" x-text="log.status.toUpperCase()"></span></span>
                                            </template>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex-shrink-0 text-right">
                                    <span class="text-xs text-gray-400 font-bold block" x-text="log.date"></span>
                                    <span class="text-[10px] text-gray-400 block mt-0.5" x-text="log.time"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center shadow-sm" x-show="filteredLogs.length === 0">
                        <p class="text-gray-400 text-xs font-semibold">Tidak ada aktivitas tercatat untuk filter ini</p>
                    </div>
                </div>

                {{-- TAB: KATALOG REWARD --}}
                <div x-show="activeTab === 'katalog'" class="p-6 space-y-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {{-- Kiri: Form Tambah Item Baru --}}
                        <div class="bg-gray-50/50 rounded-3xl border border-gray-100 p-6 space-y-5">
                            <div>
                                <h4 class="font-black text-gray-900 text-base">🎁 Tambah Hadiah Baru</h4>
                                <p class="text-xs text-gray-400 mt-0.5">Buat voucher atau item hadiah baru untuk katalog user</p>
                            </div>

                            <form action="{{ route('superadmin.rewards.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Hadiah</label>
                                    <input type="text" name="name" required placeholder="Contoh: Voucher Indomaret 50rb"
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white text-sm font-semibold text-gray-700 transition" />
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Harga Poin</label>
                                        <input type="number" name="points_required" required min="1" placeholder="500"
                                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white text-sm font-semibold text-gray-700 transition" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Stok (Kosong = Unlimited)</label>
                                        <input type="number" name="stock" min="0" placeholder="Unlimited"
                                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white text-sm font-semibold text-gray-700 transition" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1.5">Deskripsi Hadiah</label>
                                    <textarea name="description" rows="3" placeholder="Tulis info detail, masa berlaku voucher, cara klaim, dsb..."
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white text-sm font-semibold text-gray-700 transition"></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1.5">Foto Produk (Opsional)</label>
                                    <input type="file" name="image" accept="image/*"
                                           class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 transition" />
                                </div>

                                <button type="submit"
                                        class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-black text-sm rounded-xl shadow-md shadow-amber-150 transition active:scale-[0.98]">
                                    ➕ Tambahkan Hadiah
                                </button>
                            </form>
                        </div>

                        {{-- Kanan: List Items --}}
                        <div class="lg:col-span-2 space-y-4">
                            <h4 class="font-black text-gray-900 text-base">📋 Daftar Katalog Saat Ini</h4>

                            @if($rewards->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($rewards as $reward)
                                        <div class="bg-white border border-gray-150 rounded-3xl p-4 flex gap-3.5 shadow-sm">
                                            {{-- Thumbnail --}}
                                            <div class="w-20 h-20 bg-gray-50 rounded-2xl overflow-hidden border border-gray-100 flex-shrink-0 flex items-center justify-center">
                                                @if($reward->image)
                                                    <img src="{{ asset('storage/' . $reward->image) }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-3xl">🎁</span>
                                                @endif
                                            </div>

                                            {{-- Details --}}
                                            <div class="flex-1 flex flex-col justify-between min-w-0">
                                                <div>
                                                    <div class="flex items-start justify-between gap-1.5">
                                                        <h5 class="font-black text-gray-950 text-sm truncate">{{ $reward->name }}</h5>
                                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $reward->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100' }}">
                                                            {{ $reward->is_active ? 'Aktif' : 'Nonaktif' }}
                                                        </span>
                                                    </div>
                                                    <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">{{ $reward->description }}</p>
                                                    <div class="flex items-center gap-3 mt-1.5 text-[11px]">
                                                        <span class="text-amber-600 font-bold">✨ {{ $reward->points_required }} Poin</span>
                                                        <span class="text-gray-400 font-medium">📦 Stok: {{ is_null($reward->stock) ? 'Unlimited' : $reward->stock }}</span>
                                                    </div>
                                                          {{-- Actions --}}
                                                <div class="flex items-center justify-end gap-2 mt-2 pt-2 border-t border-gray-50">
                                                    {{-- Active/Inactive Toggle --}}
                                                    <form action="{{ route('superadmin.rewards.toggle', $reward->id) }}" method="POST">
                                                        @csrf @method('PATCH')
                                                        <button type="submit"
                                                                class="px-3 py-1.5 rounded-xl text-[10px] font-bold transition
                                                                {{ $reward->is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                            {{ $reward->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                        </button>
                                                    </form>

                                                    {{-- Edit --}}
                                                    <button type="button" 
                                                            @click="openEditReward({{ json_encode($reward) }})"
                                                            class="p-1.5 bg-gray-50 hover:bg-amber-50 text-gray-400 hover:text-amber-600 rounded-xl transition">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                        </svg>
                                                    </button>

                                                    {{-- Delete --}}
                                                    <form action="{{ route('superadmin.rewards.destroy', $reward->id) }}" method="POST"
                                                          onsubmit="return confirm('Hapus item hadiah ini dari katalog permanen?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="p-1.5 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-600 rounded-xl transition">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3 0V5a2 2 0 012-2h0a2 2 0 012 2v2"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>                                        </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-16 border border-dashed border-gray-150 rounded-3xl bg-white">
                                    <span class="text-4xl">📭</span>
                                    <p class="text-gray-500 font-bold text-sm mt-3">Belum ada item hadiah dalam katalog.</p>
                                </div>
                            @endif
                        </div>
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
                        <a x-show="detail && detail.maps_url" :href="detail ? detail.maps_url : '#'" target="_blank"
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
        </div>

        {{-- Modal Peta --}}
        <div id="mapModal" class="hidden fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/60 backdrop-blur-md p-4">
            <div class="bg-white rounded-3xl w-full max-w-2xl shadow-2xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black text-gray-900" id="mapModalTitle">📍 Lokasi Penjemputan</h3>
                        <p class="text-sm text-gray-500 mt-0.5 max-w-md truncate" id="mapModalSubtitle"></p>
                    </div>
                    <button onclick="closeMap()"
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-600 transition text-xl font-bold">✕</button>
                </div>
                <div id="adminMap" style="height: 400px; width: 100%;"></div>
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-400">Powered by OpenStreetMap — 100% Gratis</p>
                    <a id="openGmapsBtn" href="#" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs transition">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka di Google Maps
                    </a>
                </div>
            </div>
        </div>

        {{-- Chart.js + Leaflet --}}
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
                        options: { responsive: true, maintainAspectRatio: false, animation: { duration: 500 }, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' kg' } } } }
                    });
                }
                const approvalCtx = document.getElementById('approvalRateChart');
                if (approvalCtx) {
                    approvalChart = new Chart(approvalCtx, {
                        type: 'doughnut',
                        data: { labels: ['Approved','Pending','Rejected'], datasets: [{ data: [approvedCount, pendingCount, rejectedCount], backgroundColor: ['#10b981','#f59e0b','#ef4444'], borderColor: '#fff', borderWidth: 2 }] },
                        options: { responsive: true, maintainAspectRatio: false, animation: { duration: 500 }, plugins: { legend: { position: 'bottom' } } }
                    });
                }
                const trendCtx = document.getElementById('sevenDaysTrendChart');
                if (trendCtx) {
                    trendChart = new Chart(trendCtx, {
                        type: 'line',
                        data: { labels: Object.keys(sevenDaysData), datasets: [{ label: 'Sampah Diterima (Kg)', data: Object.values(sevenDaysData), borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', borderWidth: 3, fill: true, tension: 0.4, pointBackgroundColor: '#f59e0b', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 5 }] },
                        options: { responsive: true, maintainAspectRatio: false, animation: { duration: 500 }, scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' kg' } } } }
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
                        html: `<div style="width:40px;height:40px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 4px 15px rgba(245,158,11,0.5);"></div>`,
                        className: '', iconSize: [40,40], iconAnchor: [20,40],
                    });
                    const m = L.marker([lat, lng], { icon }).addTo(adminMap);
                    m.bindPopup(`<div style="font-family:sans-serif;padding:4px;min-width:160px;"><p style="font-weight:800;font-size:14px;margin:0 0 4px;">${userName}</p><p style="font-size:11px;color:#6b7280;margin:0;">${address}</p></div>`).openPopup();
                    L.circle([lat, lng], { color: '#f59e0b', fillColor: '#f59e0b', fillOpacity: 0.1, radius: 50 }).addTo(adminMap);
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

        {{-- Modal Edit Reward/Hadiah --}}
        <div x-show="isEditRewardOpen" 
             class="fixed inset-0 z-[250] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="isEditRewardOpen = false" 
                 class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-gray-100 overflow-hidden animate-[slideUp_0.2s_ease-out]">
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-amber-500 to-orange-500 text-white flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-black">✏️ Edit Item Hadiah</h3>
                        <p class="text-xs text-white/80 mt-0.5">Perbarui informasi katalog voucher / hadiah</p>
                    </div>
                    <button @click="isEditRewardOpen = false" class="text-xl font-bold hover:text-red-200 transition">✕</button>
                </div>

                <form :action="'/superadmin/rewards/' + editRewardData.id + '/update'" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Hadiah</label>
                        <input type="text" name="name" required x-model="editRewardData.name"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white text-sm font-semibold text-gray-700 transition" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Harga Poin</label>
                            <input type="number" name="points_required" required min="1" x-model="editRewardData.points_required"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white text-sm font-semibold text-gray-700 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Stok (Kosong = Unlimited)</label>
                            <input type="number" name="stock" min="0" x-model="editRewardData.stock" placeholder="Unlimited"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white text-sm font-semibold text-gray-700 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Deskripsi Hadiah</label>
                        <textarea name="description" rows="3" x-model="editRewardData.description"
                                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white text-sm font-semibold text-gray-700 transition"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Ganti Foto Produk (Opsional)</label>
                        <input type="file" name="image" accept="image/*"
                               class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 transition" />
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="isEditRewardOpen = false"
                                class="w-1/3 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-black text-sm rounded-xl transition active:scale-95 shadow-md">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</x-app-layout>
