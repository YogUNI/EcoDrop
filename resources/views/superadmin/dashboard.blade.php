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

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <div class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-slate-50 py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 p-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-2xl shadow-xl flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">✅</span>
                        <span class="font-bold text-lg">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="hover:bg-white/20 p-2 rounded-lg">✕</button>
                </div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Admin Pending</p>
                            <h3 class="text-4xl font-black text-orange-600 mt-2">{{ $pendingAdmins->count() }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-xl shadow-lg">⏳</div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Menunggu verifikasi</p>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Admin Aktif</p>
                            <h3 class="text-4xl font-black text-green-600 mt-2">{{ $verifiedAdmins->count() }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-xl shadow-lg">✅</div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Sudah terverifikasi</p>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total User</p>
                            <h3 class="text-4xl font-black text-blue-600 mt-2">{{ $users->count() }}</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-xl shadow-lg">👥</div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Terdaftar</p>
                </div>
                <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-gray-100/50">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Admin Online</p>
                            <h3 class="text-4xl font-black text-emerald-600 mt-2">
                                {{ $verifiedAdmins->filter(fn($a) => $a->isOnline())->count() }}
                            </h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-xl shadow-lg">🟢</div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Sedang aktif</p>
                </div>
            </div>

            {{-- Online Status Admin --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden mb-8">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
                    <h3 class="text-2xl font-black text-gray-900">🟢 Status Online Admin</h3>
                    <p class="text-sm text-gray-500 mt-1">Admin yang sedang aktif di dashboard</p>
                </div>
                <div class="p-8">
                    @if($verifiedAdmins->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($verifiedAdmins as $admin)
                                <div class="flex items-center gap-4 p-4 rounded-2xl border
                                    {{ $admin->isOnline() ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200' }}">
                                    <div class="relative">
                                        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow flex-shrink-0">
                                            <img src="{{ $admin->getPhotoUrl() }}" class="w-full h-full object-cover" alt="{{ $admin->name }}">
                                        </div>
                                        <span class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full border-2 border-white
                                            {{ $admin->isOnline() ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-900 truncate">{{ $admin->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $admin->email }}</p>
                                        @if($admin->isOnline())
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 mt-1">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                                Online sekarang
                                            </span>
                                        @else
                                            <p class="text-xs text-gray-400 mt-1">
                                                {{ $admin->last_seen_at ? 'Terakhir: ' . $admin->last_seen_at->diffForHumans() : 'Belum pernah login' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">👥</div>
                            <p class="text-gray-500">Belum ada admin terdaftar</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Semua Setoran Sampah --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden mb-8">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                    <h3 class="text-2xl font-black text-gray-900">📦 Semua Setoran Sampah</h3>
                    <p class="text-sm text-gray-500 mt-1">Super admin dapat approve/reject setoran langsung</p>
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
                                    <tr class="border-b border-gray-100 hover:bg-amber-50/50 transition duration-300">
                                        <td class="py-4 pl-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full overflow-hidden border-2 border-white shadow flex-shrink-0">
                                                    <img src="{{ $pickup->user->getPhotoUrl() }}" class="w-full h-full object-cover" alt="{{ $pickup->user->name }}">
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900 text-sm">{{ $pickup->user->name }}</p>
                                                    <p class="text-xs text-gray-400">{{ $pickup->user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4">
                                            <span class="text-sm font-bold text-gray-700">{{ $pickup->type }} · {{ $pickup->weight }} Kg</span>
                                        </td>

                                        {{-- Kolom Lokasi --}}
                                        <td class="py-4">
                                            <div class="space-y-1.5">
                                                <span class="text-xs text-gray-600 block">{{ Str::limit($pickup->address, 35, '...') }}</span>
                                                @if($pickup->latitude && $pickup->longitude)
                                                    <button onclick="showMap({{ $pickup->latitude }}, {{ $pickup->longitude }}, '{{ addslashes($pickup->user->name) }}', '{{ addslashes(Str::limit($pickup->address, 60)) }}')"
                                                        class="inline-flex items-center gap-1.5 text-xs bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold px-3 py-1.5 rounded-lg border border-amber-200 transition duration-200">
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

                                        <td class="py-4">
                                            <span class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y') }}</span>
                                        </td>
                                        <td class="py-4 text-center">
                                            @if($pickup->status === 'pending')
                                                <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-bold text-xs">
                                                    <span class="w-1.5 h-1.5 bg-yellow-600 rounded-full animate-pulse"></span>⏳ Pending
                                                </span>
                                            @elseif($pickup->status === 'approved')
                                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-3 py-1 rounded-full font-bold text-xs">
                                                    <span class="w-1.5 h-1.5 bg-green-600 rounded-full"></span>✅ Approved
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 px-3 py-1 rounded-full font-bold text-xs">
                                                    <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>❌ Rejected
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4">
                                            @if($pickup->handledBy)
                                                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">
                                                    👑 {{ $pickup->handledBy->name }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 italic">Belum ditangani</span>
                                            @endif
                                        </td>
                                        <td class="py-4 text-right">
                                            @if($pickup->status === 'pending')
                                                <div class="flex justify-end items-center gap-2">
                                                    <form action="{{ route('pickups.update', $pickup->id) }}" method="POST" class="flex items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="number" name="points_earned" placeholder="Poin" required
                                                            class="w-16 px-2 py-1.5 text-sm font-bold rounded-lg border-2 border-gray-300 focus:border-amber-500 focus:outline-none bg-gray-50" />
                                                        <button type="submit" name="status" value="approved"
                                                            class="bg-green-500 text-white px-3 py-1.5 rounded-lg font-bold text-xs hover:bg-green-600 transition">✅</button>
                                                        <button type="submit" name="status" value="rejected"
                                                            class="bg-red-500 text-white px-3 py-1.5 rounded-lg font-bold text-xs hover:bg-red-600 transition">❌</button>
                                                    </form>
                                                    <form action="{{ route('pickups.destroy.admin', $pickup->id) }}" method="POST"
                                                          onsubmit="return confirm('Yakin hapus?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-400 hover:text-red-600 font-bold px-2 py-1.5 rounded-lg transition">🗑️</button>
                                                    </form>
                                                </div>
                                            @else
                                                <p class="text-sm font-black text-green-600">+{{ $pickup->points_earned }} 🏆</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">📭</div>
                            <p class="text-gray-500">Belum ada setoran masuk</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden mb-8">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50">
                    <h3 class="text-2xl font-black text-gray-900">📋 Activity Log — Realtime</h3>
                    <p class="text-sm text-gray-500 mt-1">Semua aktivitas admin tercatat di sini</p>
                </div>
                <div class="p-8">
                    @if($activityLogs->count() > 0)
                        <div class="space-y-3">
                            @foreach($activityLogs as $log)
                                <div class="flex items-start gap-4 p-4 rounded-2xl border
                                    @if($log->action === 'approved') bg-green-50 border-green-200
                                    @elseif($log->action === 'rejected') bg-red-50 border-red-200
                                    @else bg-gray-50 border-gray-200
                                    @endif">
                                    <div class="text-2xl flex-shrink-0">
                                        @if($log->action === 'approved') ✅
                                        @elseif($log->action === 'rejected') ❌
                                        @else 🗑️
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-900 text-sm">
                                            <span class="text-indigo-600">{{ $log->admin->name }}</span>
                                            @if($log->action === 'approved') menyetujui setoran
                                            @elseif($log->action === 'rejected') menolak setoran
                                            @else menghapus setoran
                                            @endif
                                            milik <span class="text-gray-700">{{ $log->user_name }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $log->waste_type }} · {{ $log->waste_weight }} Kg
                                            @if($log->points_given)
                                                · <span class="text-green-600 font-bold">+{{ $log->points_given }} poin</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 text-right">
                                        <p class="text-xs text-gray-400 font-semibold">{{ $log->created_at->format('d M Y') }}</p>
                                        <p class="text-xs text-gray-400">{{ $log->created_at->format('H:i') }}</p>
                                        <p class="text-xs text-gray-300 mt-1">{{ $log->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">📝</div>
                            <p class="text-gray-500">Belum ada aktivitas tercatat</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- List Semua User --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden mb-8">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="text-2xl font-black text-gray-900">👥 Semua User Terdaftar</h3>
                    <p class="text-sm text-gray-500 mt-1">Kelola semua akun user di EcoDrop</p>
                </div>
                <div class="p-8">
                    @if($users->count() > 0)
                        <div class="space-y-4">
                            @foreach($users as $user)
                                <div class="flex items-center justify-between p-5 rounded-2xl border
                                    {{ $user->is_banned ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200' }}">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow flex-shrink-0">
                                            <img src="{{ $user->getPhotoUrl() }}" class="w-full h-full object-cover" alt="{{ $user->name }}">
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-black text-gray-900">{{ $user->name }}</p>
                                                @if($user->is_banned)
                                                    <span class="text-xs bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded-full">🚫 Banned</span>
                                                @else
                                                    <span class="text-xs bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">✅ Aktif</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                            <p class="text-xs text-gray-400 mt-1">Daftar: {{ $user->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="hidden md:flex items-center gap-6 text-center">
                                        <div>
                                            <p class="text-2xl font-black text-amber-600">{{ $user->points }}</p>
                                            <p class="text-xs text-gray-400">Poin</p>
                                        </div>
                                        <div>
                                            <p class="text-2xl font-black text-blue-600">{{ $user->pickups_count }}</p>
                                            <p class="text-xs text-gray-400">Setoran</p>
                                        </div>
                                    </div>
                                    <form action="{{ route('superadmin.ban', $user->id) }}" method="POST"
                                          onsubmit="return confirm('{{ $user->is_banned ? 'Aktifkan kembali akun ini?' : 'Yakin banned akun ini?' }}');">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->is_banned)
                                            <button type="submit" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow hover:shadow-lg transition duration-300 hover:scale-105">
                                                ✅ Aktifkan
                                            </button>
                                        @else
                                            <button type="submit" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow hover:shadow-lg transition duration-300 hover:scale-105">
                                                🚫 Ban
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">👤</div>
                            <p class="text-gray-500 text-lg font-semibold">Belum ada user terdaftar</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Admin Pending --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden mb-8">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-amber-50">
                    <h3 class="text-2xl font-black text-gray-900">⏳ Admin Menunggu Verifikasi</h3>
                    <p class="text-sm text-gray-500 mt-1">Akun admin yang perlu disetujui</p>
                </div>
                <div class="p-8">
                    @if($pendingAdmins->count() > 0)
                        <div class="space-y-4">
                            @foreach($pendingAdmins as $admin)
                                <div class="flex items-center justify-between bg-orange-50 rounded-2xl p-6 border border-orange-200">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow flex-shrink-0">
                                            <img src="{{ $admin->getPhotoUrl() }}" class="w-full h-full object-cover" alt="{{ $admin->name }}">
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-900 text-lg">{{ $admin->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $admin->email }}</p>
                                            <p class="text-xs text-gray-400">Daftar: {{ $admin->created_at->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <form action="{{ route('superadmin.verify', $admin->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow hover:shadow-lg transition duration-300 hover:scale-105">
                                                ✅ Verifikasi
                                            </button>
                                        </form>
                                        <form action="{{ route('superadmin.delete', $admin->id) }}" method="POST"
                                              onsubmit="return confirm('Yakin hapus akun admin ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-3 rounded-xl font-bold shadow hover:shadow-lg transition duration-300 hover:scale-105">
                                                ❌ Tolak
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">🎉</div>
                            <p class="text-gray-500 text-lg font-semibold">Tidak ada admin yang menunggu verifikasi</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Admin Aktif --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl border border-gray-100/50 overflow-hidden">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-2xl font-black text-gray-900">✅ Admin Aktif</h3>
                    <p class="text-sm text-gray-500 mt-1">Daftar admin yang sudah terverifikasi</p>
                </div>
                <div class="p-8">
                    @if($verifiedAdmins->count() > 0)
                        <div class="space-y-4">
                            @foreach($verifiedAdmins as $admin)
                                <div class="flex items-center justify-between bg-green-50 rounded-2xl p-6 border border-green-200">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow flex-shrink-0">
                                                <img src="{{ $admin->getPhotoUrl() }}" class="w-full h-full object-cover" alt="{{ $admin->name }}">
                                            </div>
                                            <span class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full border-2 border-white
                                                {{ $admin->isOnline() ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-900 text-lg">{{ $admin->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $admin->email }}</p>
                                            <p class="text-xs text-gray-400">Bergabung: {{ $admin->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @if($admin->isOnline())
                                            <span class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-800 px-4 py-2 rounded-full font-bold text-sm">
                                                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>Online
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 bg-gray-100 text-gray-600 px-4 py-2 rounded-full font-bold text-sm">
                                                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>Offline
                                            </span>
                                        @endif
                                        <form action="{{ route('superadmin.delete', $admin->id) }}" method="POST"
                                              onsubmit="return confirm('Yakin hapus akun admin ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold px-3 py-2 border border-red-200 rounded-lg transition duration-300">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">👥</div>
                            <p class="text-gray-500 text-lg font-semibold">Belum ada admin aktif</p>
                        </div>
                    @endif
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
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-600 transition duration-200 text-xl font-bold">✕</button>
                </div>
                <div id="adminMap" style="height: 400px; width: 100%;"></div>
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-400">Powered by OpenStreetMap — 100% Gratis</p>
                    <a id="openGmapsBtn" href="#" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs transition duration-200">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka di Google Maps
                    </a>
                </div>
            </div>
        </div>

        {{-- Leaflet JS --}}
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            let adminMap = null;

            function showMap(lat, lng, userName, address) {
                document.getElementById('mapModal').classList.remove('hidden');
                document.getElementById('mapModalTitle').textContent = '📍 Lokasi: ' + userName;
                document.getElementById('mapModalSubtitle').textContent = address;
                document.getElementById('openGmapsBtn').href = `https://maps.google.com/?q=${lat},${lng}`;

                setTimeout(() => {
                    if (adminMap) { adminMap.remove(); adminMap = null; }

                    adminMap = L.map('adminMap').setView([lat, lng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap'
                    }).addTo(adminMap);

                    const icon = L.divIcon({
                        html: `<div style="
                            width:40px;height:40px;
                            background:linear-gradient(135deg,#f59e0b,#d97706);
                            border-radius:50% 50% 50% 0;
                            transform:rotate(-45deg);
                            border:3px solid white;
                            box-shadow:0 4px 15px rgba(245,158,11,0.5);
                        "></div>`,
                        className: '', iconSize: [40,40], iconAnchor: [20,40],
                    });

                    const m = L.marker([lat, lng], { icon }).addTo(adminMap);
                    m.bindPopup(`
                        <div style="font-family:sans-serif;padding:4px;min-width:160px;">
                            <p style="font-weight:800;font-size:14px;margin:0 0 4px;">${userName}</p>
                            <p style="font-size:11px;color:#6b7280;margin:0;">${address}</p>
                        </div>
                    `).openPopup();

                    L.circle([lat, lng], {
                        color: '#f59e0b', fillColor: '#f59e0b',
                        fillOpacity: 0.1, radius: 50
                    }).addTo(adminMap);
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