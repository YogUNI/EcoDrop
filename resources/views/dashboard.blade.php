<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center shadow-lg">
                    <span class="text-2xl">{{ Auth::user()->role === 'admin' ? '👑' : '🌱' }}</span>
                </div>
                <div>
                    <h2 class="font-extrabold text-3xl bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                        {{ Auth::user()->role === 'admin' ? 'Admin Panel' : 'EcoDrop Dashboard' }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Selamat datang, {{ Auth::user()->name }}! 👋</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div x-data="{ 
        isModalOpen: false,
        searchQuery: '',
        filterStatus: '',
        filterType: ''
    }" class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-blue-50 py-12 relative overflow-hidden">
        <!-- Floating Background Elements -->
        <div class="fixed inset-0 pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-green-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute top-40 right-10 w-72 h-72 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">
            
            <!-- Success Alert dengan Animasi -->
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
                     class="mb-6 p-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-2xl shadow-xl flex justify-between items-center backdrop-blur-sm border border-emerald-400/50">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl animate-bounce">✨</span>
                        <span class="font-bold text-lg">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="hover:bg-white/20 p-2 rounded-lg transition duration-300">✕</button>
                </div>
            @endif

            <!-- ADMIN DASHBOARD -->
            @if(Auth::user()->role === 'admin')
                @php
                    // Calculate statistics
                    $totalPickups = $pickups->count();
                    $pendingPickups = $pickups->where('status', 'pending')->count();
                    $approvedPickups = $pickups->where('status', 'approved')->count();
                    $rejectedPickups = $pickups->where('status', 'rejected')->count();
                    
                    $totalWeight = $pickups->where('status', 'approved')->sum('weight');
                    $todayWeight = $pickups->where('status', 'approved')
                        ->whereDate('pickup_date', \Carbon\Carbon::today())
                        ->sum('weight');
                    
                    $totalUsers = $pickups->pluck('user_id')->unique()->count();
                    $approvalRate = $totalPickups > 0 ? round(($approvedPickups / $totalPickups) * 100, 1) : 0;
                    
                    // Data untuk Chart
                    $wasteByType = [];
                    foreach(['Plastik', 'Kertas', 'Logam', 'Kaca', 'Organik', 'Elektronik', 'Lainnya'] as $type) {
                        $wasteByType[$type] = $pickups->where('type', $type)->where('status', 'approved')->sum('weight');
                    }
                    
                    // Last 7 days data
                    $last7Days = [];
                    for($i = 6; $i >= 0; $i--) {
                        $date = \Carbon\Carbon::today()->subDays($i)->format('Y-m-d');
                        $weight = $pickups->where('status', 'approved')
                            ->whereDate('pickup_date', $date)
                            ->sum('weight');
                        $last7Days[\Carbon\Carbon::parse($date)->format('d M')] = $weight;
                    }
                @endphp

                <!-- Admin Header Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50 hover:shadow-xl transition duration-300 transform hover:scale-105 hover:-translate-y-1">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Setoran</p>
                                <h3 class="text-4xl font-black text-gray-900 mt-2">{{ $totalPickups }}</h3>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-xl shadow-lg">📦</div>
                        </div>
                        <p class="text-xs text-gray-400">Dari semua user</p>
                    </div>

                    <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50 hover:shadow-xl transition duration-300 transform hover:scale-105 hover:-translate-y-1">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Menunggu Verifikasi</p>
                                <h3 class="text-4xl font-black text-yellow-600 mt-2">{{ $pendingPickups }}</h3>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-xl shadow-lg">⏳</div>
                        </div>
                        <p class="text-xs text-gray-400">Butuh aksi</p>
                    </div>

                    <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50 hover:shadow-xl transition duration-300 transform hover:scale-105 hover:-translate-y-1">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Sudah Disetujui</p>
                                <h3 class="text-4xl font-black text-green-600 mt-2">{{ $approvedPickups }}</h3>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-xl shadow-lg">✅</div>
                        </div>
                        <p class="text-xs text-gray-400">Berhasil</p>
                    </div>

                    <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50 hover:shadow-xl transition duration-300 transform hover:scale-105 hover:-translate-y-1">
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

                <!-- Additional Stats Row -->
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

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Bar Chart - Waste by Type -->
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-gray-100/50">
                        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                            <span>📊</span>
                            Total Sampah per Jenis
                        </h3>
                        <canvas id="wasteByTypeChart"></canvas>
                    </div>

                    <!-- Pie Chart - Approval Rate -->
                    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-gray-100/50">
                        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                            <span>📈</span>
                            Status Setoran
                        </h3>
                        <canvas id="approvalRateChart"></canvas>
                    </div>
                </div>

                <!-- Line Chart - 7 Days Trend -->
                <div class="bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-gray-100/50 mb-8">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <span>📉</span>
                        Tren Sampah 7 Hari Terakhir
                    </h3>
                    <canvas id="sevenDaysTrendChart"></canvas>
                </div>

                <!-- Tabel Antrian Setoran dengan Search & Filter -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden">
                    <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-2xl font-black text-gray-900">📋 Antrian Setor Sampah</h3>
                                <p class="text-sm text-gray-500 mt-1">Kelola semua setoran sampah dari user</p>
                            </div>
                        </div>

                        <!-- Search & Filter -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Search Input -->
                            <div class="relative">
                                <input type="text" x-model="searchQuery" placeholder="🔍 Cari nama user atau email..." class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300">
                            </div>

                            <!-- Filter Status -->
                            <div class="relative">
                                <select x-model="filterStatus" class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300 cursor-pointer appearance-none" style="background-image: url('data:image/svg+xml;utf8,<svg fill=\'%23059669\' height=\'24\' viewBox=\'0 0 24 24\' width=\'24\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M7 10l5 5 5-5z\'/></svg>'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                    <option value="">📊 Status (Semua)</option>
                                    <option value="pending">⏳ Pending</option>
                                    <option value="approved">✅ Approved</option>
                                    <option value="rejected">❌ Rejected</option>
                                </select>
                            </div>

                            <!-- Filter Type -->
                            <div class="relative">
                                <select x-model="filterType" class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300 cursor-pointer appearance-none" style="background-image: url('data:image/svg+xml;utf8,<svg fill=\'%23059669\' height=\'24\' viewBox=\'0 0 24 24\' width=\'24\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M7 10l5 5 5-5z\'/></svg>'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
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
                        </div>
                    </div>
                    
                    <div class="p-8 overflow-x-auto">
                        @if($pickups->count() > 0)
                            <table class="w-full text-left" id="pickupsTable">
                                <thead>
                                    <tr class="text-xs font-bold text-gray-600 uppercase tracking-widest border-b-2 border-gray-200">
                                        <th class="pb-4 pl-4">👤 User</th>
                                        <th class="pb-4">♻️ Jenis Sampah</th>
                                        <th class="pb-4">📍 Alamat</th>
                                        <th class="pb-4">📅 Tanggal</th>
                                        <th class="pb-4 text-center">📊 Status</th>
                                        <th class="pb-4 text-right">⚙️ Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pickups as $pickup)
                                        <tr class="border-b border-gray-100 hover:bg-gradient-to-r hover:from-green-50 hover:to-transparent transition duration-300 group searchable-row" data-name="{{ strtolower($pickup->user->name) }}" data-email="{{ strtolower($pickup->user->email) }}" data-status="{{ $pickup->status }}" data-type="{{ $pickup->type }}">
                                            <td class="py-5 pl-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                                                        {{ substr($pickup->user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-gray-900">{{ $pickup->user->name }}</p>
                                                        <p class="text-xs text-gray-500">{{ $pickup->user->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-5">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 py-2 px-4 rounded-full text-sm font-bold shadow-sm">
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
                                                        {{ $pickup->type }}
                                                    </span>
                                                    <span class="text-gray-900 font-bold">{{ $pickup->weight }} Kg</span>
                                                </div>
                                            </td>
                                            <td class="py-5">
                                                <span class="text-xs text-gray-600 line-clamp-2 max-w-xs" title="{{ $pickup->address }}">
                                                    {{ Str::limit($pickup->address, 40, '...') }}
                                                </span>
                                            </td>
                                            <td class="py-5">
                                                <span class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-4 py-2 rounded-lg font-semibold text-sm">
                                                    📆 {{ \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y') }}
                                                </span>
                                            </td>
                                            <td class="py-5 text-center">
                                                @if($pickup->status === 'pending')
                                                    <span class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-bold text-sm shadow-sm">
                                                        <span class="w-2 h-2 bg-yellow-600 rounded-full animate-pulse"></span>
                                                        ⏳ Pending
                                                    </span>
                                                @elseif($pickup->status === 'approved')
                                                    <span class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-full font-bold text-sm shadow-sm">
                                                        <span class="w-2 h-2 bg-green-600 rounded-full animate-pulse"></span>
                                                        ✅ Approved
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-2 bg-red-100 text-red-800 px-4 py-2 rounded-full font-bold text-sm shadow-sm">
                                                        <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                                                        ❌ Rejected
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-5 text-right">
                                                @if($pickup->status === 'pending')
                                                    <div class="flex justify-end items-center gap-3 flex-wrap">
                                                        <form action="{{ route('pickups.update', $pickup->id) }}" method="POST" class="flex items-center gap-2">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="relative group">
                                                                <input type="number" name="points_earned" placeholder="Poin" required class="w-20 px-3 py-2 text-sm font-bold rounded-lg border-2 border-gray-300 focus:border-green-500 focus:outline-none transition duration-300 bg-gray-50 focus:bg-white" />
                                                                <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap">Masukkan poin</span>
                                                            </div>
                                                            <button type="submit" name="status" value="approved" class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-105 flex items-center gap-2">
                                                                <span>✅</span>
                                                                <span class="hidden sm:inline">Setuju</span>
                                                            </button>
                                                            <button type="submit" name="status" value="rejected" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-105 flex items-center gap-2">
                                                                <span>❌</span>
                                                                <span class="hidden sm:inline">Tolak</span>
                                                            </button>
                                                        </form>

                                                        <form action="{{ route('pickups.destroy', $pickup->id) }}" method="POST" onsubmit="return confirm('⚠️ Yakin mau hapus data ini dari database? Tindakan ini tidak bisa dibatalkan!');" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 font-bold text-sm px-3 py-2 rounded-lg transition duration-300 transform hover:scale-110">
                                                                🗑️
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <div class="text-right">
                                                        <p class="text-sm text-gray-600 font-semibold">Selesai</p>
                                                        <p class="text-lg font-black text-green-600">+{{ $pickup->points_earned }} 🏆</p>
                                                    </div>
                                                @endif
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

            <!-- USER DASHBOARD -->
            @else
                <!-- Points Card dengan Animasi -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <!-- Points Card -->
                    <div class="group relative bg-gradient-to-br from-green-400 via-emerald-500 to-green-600 rounded-3xl shadow-2xl overflow-hidden cursor-pointer transform transition duration-300 hover:scale-105 hover:shadow-3xl">
                        <!-- Animated Background -->
                        <div class="absolute inset-0 opacity-10">
                            <div class="absolute inset-0 bg-gradient-to-r from-white to-transparent transform -skew-x-12 group-hover:skew-x-12 transition duration-500"></div>
                        </div>
                        
                        <div class="relative p-8 text-white">
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

                    <!-- CTA Card -->
                    <div class="md:col-span-2 group bg-white/80 backdrop-blur-sm p-8 rounded-3xl shadow-2xl border border-gray-100/50 hover:shadow-3xl transition duration-300 flex flex-col justify-between transform hover:scale-102 hover:-translate-y-1">
                        <div class="mb-6">
                            <h3 class="text-3xl font-black bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">♻️ Setor Sampah</h3>
                            <p class="text-gray-600 mt-2 font-medium">Ubah sampah Anda menjadi poin reward! Setiap setor adalah kontribusi untuk planet yang lebih hijau.</p>
                        </div>
                        <button @click="isModalOpen = true" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-8 py-4 rounded-2xl font-bold shadow-lg hover:shadow-2xl transition duration-300 transform hover:scale-105 flex items-center justify-center gap-3 text-lg">
                            <span class="text-2xl">➕</span>
                            <span>Setor Baru Sekarang</span>
                            <span class="text-2xl animate-pulse">→</span>
                        </button>
                    </div>
                </div>

                <!-- Riwayat Setoran -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden">
                    <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                        <h3 class="text-2xl font-black text-gray-900">📜 Riwayat Setoran Anda</h3>
                        <p class="text-sm text-gray-500 mt-1">Pantau status semua setoran sampah Anda</p>
                    </div>
                    
                    <div class="p-8">
                        @if($pickups->count() > 0)
                            <div class="space-y-4">
                                @foreach($pickups as $pickup)
                                    <div class="group bg-gradient-to-r from-gray-50 to-gray-50 hover:from-green-50 hover:to-emerald-50 rounded-2xl p-6 border border-gray-200 hover:border-green-300 transition duration-300 transform hover:scale-102 hover:shadow-lg">
                                        <div class="flex justify-between items-start flex-col md:flex-row md:items-center gap-4">
                                            <!-- Left Side Info -->
                                            <div class="flex-1">
                                                <div class="flex items-center gap-4 mb-3">
                                                    <div class="text-3xl">
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
                                                    <div>
                                                        <p class="font-black text-gray-900 text-lg">{{ $pickup->type }}</p>
                                                        <p class="text-sm text-gray-500 flex items-center gap-2">
                                                            <span>⚖️</span>
                                                            <span>{{ $pickup->weight }} Kg</span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-400 flex items-center gap-2 mb-2">
                                                    <span>📅</span>
                                                    <span>{{ \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y') }}</span>
                                                </p>
                                                <p class="text-xs text-gray-500 flex items-center gap-2">
                                                    <span>📍</span>
                                                    <span class="line-clamp-1">{{ Str::limit($pickup->address, 50, '...') }}</span>
                                                </p>
                                            </div>

                                            <!-- Right Side Status & Points -->
                                            <div class="flex-shrink-0 text-right">
                                                <div class="mb-3">
                                                    @if($pickup->status === 'pending')
                                                        <span class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-bold text-sm shadow-sm">
                                                            <span class="w-2 h-2 bg-yellow-600 rounded-full animate-pulse"></span>
                                                            ⏳ Menunggu
                                                        </span>
                                                    @elseif($pickup->status === 'approved')
                                                        <span class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-full font-bold text-sm shadow-sm">
                                                            <span class="w-2 h-2 bg-green-600 rounded-full animate-pulse"></span>
                                                            ✅ Disetujui
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-2 bg-red-100 text-red-800 px-4 py-2 rounded-full font-bold text-sm shadow-sm">
                                                            <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                                                            ❌ Ditolak
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mb-2">
                                                    @if($pickup->status === 'approved')
                                                        <p class="text-2xl font-black text-green-600 drop-shadow-lg">+{{ $pickup->points_earned }} 🏆</p>
                                                    @else
                                                        <p class="text-sm text-gray-400">Poin: Pending</p>
                                                    @endif
                                                </div>

                                                <!-- Batalkan Button -->
                                                @if($pickup->status === 'pending')
                                                    <form action="{{ route('pickups.destroy', $pickup->id) }}" method="POST" onsubmit="return confirm('⚠️ Yakin ingin membatalkan setoran ini? Tindakan ini tidak bisa dibatalkan!');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 font-bold px-3 py-2 border border-red-200 rounded-lg transition duration-300 transform hover:scale-110 flex items-center gap-2">
                                                            <span>🗑️</span>
                                                            <span>Batalkan</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-16">
                                <div class="text-6xl mb-4">🌱</div>
                                <p class="text-gray-500 text-lg font-semibold mb-2">Belum ada setoran sampah</p>
                                <p class="text-gray-400 text-sm">Mulai sekarang dengan menekan tombol "Setor Baru" untuk mendapatkan poin pertama Anda!</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal Form Setor Sampah -->
                <div x-show="isModalOpen" 
                     style="display: none;" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-md overflow-y-auto">
                    
                    <div @click.away="isModalOpen = false" 
                         x-show="isModalOpen"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-90"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-90"
                         class="bg-white rounded-3xl p-8 w-full max-w-lg shadow-2xl relative border border-gray-100 overflow-hidden my-8">
                        
                        <!-- Decorative Top Border -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 via-emerald-500 to-blue-500"></div>

                        <!-- Header -->
                        <div class="flex justify-between items-center mb-8">
                            <div>
                                <h3 class="text-2xl font-black bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">📦 Ajukan Setor Sampah</h3>
                                <p class="text-sm text-gray-500 mt-1">Isi form untuk mengajukan setor sampah Anda</p>
                            </div>
                            <button @click="isModalOpen = false" class="text-3xl font-bold text-gray-400 hover:text-red-500 transition duration-300 transform hover:rotate-90 hover:scale-110">&times;</button>
                        </div>

                        <!-- Form -->
                        <form method="POST" action="{{ route('pickups.store') }}" class="space-y-5">
                            @csrf
                            
                            <!-- Jenis Sampah Select -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                    <span class="text-lg">♻️</span>
                                    <span>Pilih Jenis Sampah</span>
                                </label>
                                <select name="type" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-200 transition duration-300 font-semibold bg-gray-50 focus:bg-white appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;utf8,<svg fill=\'%23059669\' height=\'24\' viewBox=\'0 0 24 24\' width=\'24\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M7 10l5 5 5-5z\'/></svg>'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                    <option value="" disabled selected class="text-gray-400">Pilih jenis sampah...</option>
                                    <option value="Plastik" class="bg-white text-gray-900">🪴 Plastik</option>
                                    <option value="Kertas" class="bg-white text-gray-900">📄 Kertas</option>
                                    <option value="Logam" class="bg-white text-gray-900">🔩 Logam</option>
                                    <option value="Kaca" class="bg-white text-gray-900">🥛 Kaca</option>
                                    <option value="Organik" class="bg-white text-gray-900">🍂 Organik</option>
                                    <option value="Elektronik" class="bg-white text-gray-900">⚡ Elektronik</option>
                                    <option value="Lainnya" class="bg-white text-gray-900">📦 Lainnya</option>
                                </select>
                                @error('type')
                                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Berat Input -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                    <span class="text-lg">⚖️</span>
                                    <span>Berat Sampah (Kg)</span>
                                </label>
                                <input type="number" name="weight" step="0.1" required placeholder="Contoh: 5.5" value="{{ old('weight') }}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-200 transition duration-300 font-semibold bg-gray-50 focus:bg-white @error('weight') border-red-500 @enderror" />
                                <p class="text-xs text-gray-400 mt-2">Masukkan berat dalam kilogram (minimal 0.1 Kg)</p>
                                @error('weight')
                                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tanggal Pickup Input -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                    <span class="text-lg">📅</span>
                                    <span>Tanggal Penjemputan</span>
                                </label>
                                <input type="date" name="pickup_date" required min="{{ date('Y-m-d') }}" value="{{ old('pickup_date') }}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-200 transition duration-300 font-semibold bg-gray-50 focus:bg-white cursor-pointer @error('pickup_date') border-red-500 @enderror" />
                                <p class="text-xs text-gray-400 mt-2">Pilih tanggal hari ini atau tanggal mendatang</p>
                                @error('pickup_date')
                                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Alamat Input -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                    <span class="text-lg">📍</span>
                                    <span>Alamat Penjemputan</span>
                                </label>
                                <textarea name="address" required placeholder="Contoh: Jl. Merdeka No. 123, Kota Bandung, Jawa Barat 40123" rows="3" class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-200 transition duration-300 font-semibold bg-gray-50 focus:bg-white @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                                <p class="text-xs text-gray-400 mt-2">Alamat lengkap untuk memudahkan penjemput</p>
                                @error('address')
                                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nomor Telepon Input -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                    <span class="text-lg">📱</span>
                                    <span>Nomor Telepon</span>
                                </label>
                                <input type="tel" name="phone" required placeholder="Contoh: 0812345678 atau +6212345678" value="{{ old('phone') }}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-200 transition duration-300 font-semibold bg-gray-50 focus:bg-white @error('phone') border-red-500 @enderror" />
                                <p class="text-xs text-gray-400 mt-2">Nomor WhatsApp atau telepon yang aktif</p>
                                @error('phone')
                                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
                                <button type="button" 
                                        @click="isModalOpen = false" 
                                        class="flex-1 px-5 py-3 text-gray-700 border-2 border-gray-300 rounded-xl font-bold hover:bg-gray-100 hover:border-gray-400 transition duration-300 transform hover:scale-105">
                                    Batal
                                </button>
                                <button type="submit" class="flex-1 px-5 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-105 flex items-center justify-center gap-2">
                                    <span>✅</span>
                                    <span>Ajukan Sekarang</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Chart.js Script -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Data dari PHP
            const wasteByType = @json($wasteByType ?? []);
            const approvedCount = {{ $approvedPickups ?? 0 }};
            const pendingCount = {{ $pendingPickups ?? 0 }};
            const rejectedCount = {{ $rejectedPickups ?? 0 }};
            const sevenDaysData = @json($last7Days ?? []);

            // Bar Chart - Waste by Type
            const wasteCtx = document.getElementById('wasteByTypeChart');
            if (wasteCtx) {
                new Chart(wasteCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(wasteByType),
                        datasets: [{
                            label: 'Total Berat (Kg)',
                            data: Object.values(wasteByType),
                            backgroundColor: [
                                '#10b981',
                                '#14b8a6',
                                '#06b6d4',
                                '#8b5cf6',
                                '#ec4899',
                                '#f59e0b',
                                '#6b7280'
                            ],
                            borderRadius: 8,
                            hoverBackgroundColor: 'rgba(0, 0, 0, 0.1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + ' kg';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Pie Chart - Approval Rate
            const approvalCtx = document.getElementById('approvalRateChart');
            if (approvalCtx) {
                new Chart(approvalCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Approved', 'Pending', 'Rejected'],
                        datasets: [{
                            data: [approvedCount, pendingCount, rejectedCount],
                            backgroundColor: [
                                '#10b981',
                                '#f59e0b',
                                '#ef4444'
                            ],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Line Chart - 7 Days Trend
            const trendCtx = document.getElementById('sevenDaysTrendChart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: Object.keys(sevenDaysData),
                        datasets: [{
                            label: 'Sampah Diterima (Kg)',
                            data: Object.values(sevenDaysData),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + ' kg';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Search & Filter Functionality
            const searchInput = document.querySelector('input[placeholder*="Cari"]');
            const filterStatus = document.querySelector('select:nth-of-type(1)');
            const filterType = document.querySelector('select:nth-of-type(2)');

            function filterTable() {
                const rows = document.querySelectorAll('.searchable-row');
                const search = searchInput.value.toLowerCase();
                const status = filterStatus.value;
                const type = filterType.value;

                rows.forEach(row => {
                    const name = row.getAttribute('data-name');
                    const email = row.getAttribute('data-email');
                    const rowStatus = row.getAttribute('data-status');
                    const rowType = row.getAttribute('data-type');

                    const matchSearch = name.includes(search) || email.includes(search);
                    const matchStatus = !status || rowStatus === status;
                    const matchType = !type || rowType === type;

                    row.style.display = matchSearch && matchStatus && matchType ? '' : 'none';
                });
            }

            if (searchInput) searchInput.addEventListener('input', filterTable);
            if (filterStatus) filterStatus.addEventListener('change', filterTable);
            if (filterType) filterType.addEventListener('change', filterTable);
        </script>

        <!-- Tailwind Keyframe Animation di CSS -->
        <style>
            @keyframes blob {
                0%, 100% { transform: translate(0, 0) scale(1); }
                25% { transform: translate(20px, -50px) scale(1.1); }
                50% { transform: translate(-20px, 20px) scale(0.9); }
                75% { transform: translate(50px, 50px) scale(1.05); }
            }
            
            .animate-blob {
                animation: blob 7s infinite;
            }
            
            .animation-delay-2000 {
                animation-delay: 2s;
            }
            
            .animation-delay-4000 {
                animation-delay: 4s;
            }

            .hover\:scale-102:hover {
                transform: scale(1.02);
            }

            .hover\:-translate-y-1:hover {
                transform: translateY(-0.25rem);
            }

            .line-clamp-1 {
                display: -webkit-box;
                -webkit-line-clamp: 1;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
        </style>
    </div>
</x-app-layout>