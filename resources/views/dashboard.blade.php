<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-xl text-gray-900">Halo, {{ Auth::user()->name }} 👋</h2>
                <p class="text-xs text-gray-400 mt-0.5">Selamat datang kembali di EcoDrop</p>
            </div>
        </div>
    </x-slot>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    {{-- Custom Styles --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        .dashboard-bg {
            background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 50%, #ecfdf5 100%);
            min-height: 100vh;
        }

        /* Hero Card */
        .hero-card {
            background: linear-gradient(135deg, #059669 0%, #047857 40%, #0f766e 100%);
            position: relative;
            overflow: hidden;
        }
        .hero-card::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }
        .hero-card::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -40px;
            width: 280px; height: 280px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        /* Stat pills inside hero */
        .stat-pill {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.15);
            transition: background 0.2s;
        }
        .stat-pill:hover { background: rgba(255,255,255,0.18); }

        /* Action buttons */
        .btn-primary {
            background: rgba(255,255,255,0.95);
            color: #065f46;
            font-weight: 800;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        }
        .btn-primary:hover {
            background: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.16);
        }
        .btn-primary:active { transform: scale(0.97); }

        .btn-outline-white {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(8px);
            border: 1.5px solid rgba(255,255,255,0.25);
            color: white;
            font-weight: 700;
            transition: all 0.2s;
        }
        .btn-outline-white:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-1px);
        }
        .btn-outline-white:active { transform: scale(0.97); }

        /* Pickup Cards */
        .pickup-card {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 18px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .pickup-card:hover {
            border-color: #a7f3d0;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);
            transform: translateY(-1px);
        }

        /* Status Badges */
        .badge-pending {
            background: #fef9c3;
            color: #854d0e;
            border: 1px solid #fde68a;
        }
        .badge-approved {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Type icon container */
        .type-icon {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #a7f3d0;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            font-size: 22px;
            transition: transform 0.2s;
        }
        .pickup-card:hover .type-icon { transform: scale(1.08); }

        /* Section Card */
        .section-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 1px 12px rgba(0,0,0,0.04);
        }

        /* Modal Backdrop */
        .modal-backdrop {
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
        }

        /* Modal Panel */
        .modal-panel {
            background: white;
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        }

        /* Form Inputs */
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            font-size: 14px;
            color: #374151;
            background: #fafafa;
            transition: all 0.2s;
            outline: none;
        }
        .form-input:focus {
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        /* Upload Zone */
        .upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 16px;
            transition: all 0.2s;
            background: #fafafa;
        }
        .upload-zone:hover {
            border-color: #10b981;
            background: #f0fdf4;
        }

        /* Submit Button */
        .btn-submit {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-weight: 800;
            border-radius: 14px;
            padding: 14px;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
        }
        .btn-submit:active { transform: scale(0.98); }

        /* Point display */
        .points-number {
            font-size: 52px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -2px;
        }

        /* Empty state */
        .empty-icon-wrap {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        /* Notification toast */
        .toast-success {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #a7f3d0;
            color: #065f46;
            border-radius: 16px;
        }

        /* Map container */
        #mapContainer { border-radius: 16px; overflow: hidden; }
        #map { height: 190px; width: 100%; }

        /* Scrollbar */
        .modal-scroll::-webkit-scrollbar { width: 6px; }
        .modal-scroll::-webkit-scrollbar-track { background: transparent; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 999px; }
        .modal-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        /* Pulse animation for pending */
        @keyframes softPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .pending-dot { animation: softPulse 2s infinite; }
    </style>

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
         class="dashboard-bg py-8 relative">

        <div class="max-w-2xl mx-auto px-4 sm:px-6 relative z-10 space-y-5">

            {{-- Toast Notification --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="toast-success p-4 flex justify-between items-center shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="font-bold text-sm">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 transition ml-4 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- ═══ HERO CARD ═══ --}}
            <div class="hero-card rounded-[28px] p-7 shadow-xl shadow-emerald-900/20 relative">
                <div class="relative z-10">

                    {{-- Top Row: Points + Label --}}
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-7 h-7 rounded-xl bg-white/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <p class="text-white/70 text-xs font-semibold tracking-wide uppercase">Total Poin Anda</p>
                            </div>
                            <p class="points-number text-white">{{ number_format(Auth::user()->points) }}</p>
                            <p class="text-white/55 text-xs mt-2 leading-relaxed">Tukarkan poin dengan hadiah menarik atau saldo digital</p>
                        </div>

                        {{-- Eco badge --}}
                        <div class="flex-shrink-0 w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center border border-white/15">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Stat Pills --}}
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <div class="stat-pill rounded-2xl p-3 text-center">
                            <p class="text-white font-black text-2xl leading-none">{{ $pickups->where('status','approved')->count() }}</p>
                            <p class="text-white/60 text-[10px] font-medium mt-1">✅ Disetujui</p>
                        </div>
                        <div class="stat-pill rounded-2xl p-3 text-center">
                            <p class="text-white font-black text-2xl leading-none pending-dot">{{ $pickups->where('status','pending')->count() }}</p>
                            <p class="text-white/60 text-[10px] font-medium mt-1">⏳ Pending</p>
                        </div>
                        <div class="stat-pill rounded-2xl p-3 text-center">
                            <p class="text-white font-black text-2xl leading-none">{{ $pickups->count() }}</p>
                            <p class="text-white/60 text-[10px] font-medium mt-1">📦 Total</p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-3">
                        <button @click="isModalOpen = true"
                            class="btn-primary flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            Setor Sampah
                        </button>
                        <a href="{{ route('rewards.index') }}"
                            class="btn-outline-white flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0h4m-4 0H8"/>
                            </svg>
                            Tukar Poin
                        </a>
                    </div>
                </div>
            </div>

            {{-- ═══ RIWAYAT SETORAN ═══ --}}
            <div class="section-card p-6">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-base font-black text-gray-900">Riwayat Setoran</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $pickups->count() }} total pengajuan</p>
                    </div>
                    @if($pickups->count() > 0)
                        <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    @endif
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

                            <div class="pickup-card p-4 flex items-center justify-between gap-3">

                                {{-- Clickable area: Icon + Info --}}
                                <div @click="openDetail({{ $detailData }})"
                                     class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer">

                                    {{-- Type Icon --}}
                                    <div class="type-icon">{{ $icon }}</div>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                            <span class="font-bold text-gray-900 text-sm">{{ $pickup->type }}</span>
                                            @if($pickup->status === 'pending')
                                                <span class="badge-pending text-[10px] font-bold px-2.5 py-0.5 rounded-full">Pending</span>
                                            @elseif($pickup->status === 'approved')
                                                <span class="badge-approved text-[10px] font-bold px-2.5 py-0.5 rounded-full">Disetujui</span>
                                            @else
                                                <span class="badge-rejected text-[10px] font-bold px-2.5 py-0.5 rounded-full">Ditolak</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-gray-400 font-medium">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                                </svg>
                                                {{ $pickup->weight }} kg
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($pickup->pickup_date)->format('d M Y') }}
                                            </span>
                                            <span class="flex items-center gap-1 max-w-[140px] truncate">
                                                <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                </svg>
                                                <span class="truncate">{{ $pickup->address }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Right: Points / Cancel --}}
                                <div class="flex-shrink-0 text-right">
                                    @if($pickup->status === 'approved' && $pickup->points_earned)
                                        <div class="flex flex-col items-end">
                                            <p class="text-emerald-600 font-black text-xl leading-none">+{{ $pickup->points_earned }}</p>
                                            <p class="text-gray-400 text-[10px] mt-0.5 font-medium">poin</p>
                                        </div>
                                    @elseif($pickup->status === 'pending')
                                        <form action="{{ route('pickups.destroy', $pickup->id) }}" method="POST"
                                              onsubmit="return confirm('Batalkan pengajuan setoran ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-xs text-red-400 hover:text-red-600 font-bold hover:underline transition px-2 py-1 rounded-lg hover:bg-red-50">
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                @else
                    {{-- Empty State --}}
                    <div class="text-center py-14 px-6">
                        <div class="empty-icon-wrap">
                            <svg class="w-9 h-9 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                        <p class="text-gray-800 text-sm font-bold">Belum Ada Setoran</p>
                        <p class="text-gray-400 text-xs mt-1 mb-5">Mulai kontribusi lingkungan Anda sekarang!</p>
                        <button @click="isModalOpen = true"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-6 py-3 rounded-2xl transition shadow-md shadow-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            Setor Sampah Pertama
                        </button>
                    </div>
                @endif
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- MODAL DETAIL --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="isDetailOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[300] flex items-end sm:items-center justify-center modal-backdrop p-4" style="display:none;">
            <div @click.away="isDetailOpen = false" x-show="isDetailOpen"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-5 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-5"
                 class="modal-panel w-full max-w-lg flex flex-col max-h-[88vh]">

                {{-- Detail Modal Header --}}
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5" x-text="detail ? 'ID Setoran #' + detail.id : ''"></p>
                        <h3 class="font-black text-gray-900 text-lg" x-text="detail ? detail.type : ''"></h3>
                    </div>
                    <button @click="isDetailOpen = false"
                        class="w-9 h-9 bg-gray-100 hover:bg-red-50 hover:text-red-500 text-gray-400 rounded-xl flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Detail Modal Body --}}
                <div class="overflow-y-auto flex-1 p-6 space-y-4 modal-scroll">

                    {{-- Photo --}}
                    <div x-show="detail && detail.photo" class="rounded-2xl overflow-hidden">
                        <img :src="detail ? detail.photo : ''" loading="lazy"
                             class="w-full object-cover max-h-56 rounded-2xl border border-gray-100">
                    </div>

                    {{-- Status Banner --}}
                    <div class="rounded-2xl p-4 flex items-center justify-between"
                         :class="{
                             'bg-yellow-50 border border-yellow-100': detail && detail.status === 'pending',
                             'bg-emerald-50 border border-emerald-100': detail && detail.status === 'approved',
                             'bg-red-50 border border-red-100': detail && detail.status === 'rejected'
                         }">
                        <div class="flex items-center gap-2">
                            <span x-show="detail && detail.status === 'pending'" class="text-base">⏳</span>
                            <span x-show="detail && detail.status === 'approved'" class="text-base">✅</span>
                            <span x-show="detail && detail.status === 'rejected'" class="text-base">❌</span>
                            <span class="font-bold text-sm"
                                  :class="{
                                      'text-yellow-800': detail && detail.status === 'pending',
                                      'text-emerald-800': detail && detail.status === 'approved',
                                      'text-red-800': detail && detail.status === 'rejected'
                                  }"
                                  x-text="detail ? (detail.status === 'pending' ? 'Menunggu Verifikasi' : detail.status === 'approved' ? 'Setoran Disetujui' : 'Setoran Ditolak') : ''">
                            </span>
                        </div>
                        <span x-show="detail && detail.status === 'approved' && detail.points"
                              class="font-black text-emerald-700 text-lg"
                              x-text="detail ? '+' + detail.points + ' poin' : ''">
                        </span>
                    </div>

                    {{-- Detail Info Grid --}}
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Jenis Sampah</p>
                                <p class="font-bold text-gray-800 text-sm" x-text="detail ? detail.type : ''"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Berat</p>
                                <p class="font-bold text-gray-800 text-sm" x-text="detail ? detail.weight + ' kg' : ''"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Tanggal Jemput</p>
                                <p class="font-bold text-gray-800 text-sm" x-text="detail ? detail.pickup_date : ''"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">No. WhatsApp</p>
                                <p class="font-bold text-gray-800 text-sm" x-text="detail ? detail.phone : ''"></p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Alamat Penjemputan</p>
                            <p class="font-semibold text-gray-700 text-sm leading-relaxed" x-text="detail ? detail.address : ''"></p>
                            <a x-show="detail && detail.maps_url"
                               :href="detail ? detail.maps_url : '#'" target="_blank"
                               class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 hover:underline mt-2 font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                Lihat di Google Maps →
                            </a>
                        </div>
                        <div x-show="detail && detail.handled_by">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Ditangani Oleh</p>
                            <p class="font-bold text-gray-800 text-sm" x-text="detail ? detail.handled_by : ''"></p>
                        </div>
                        <div x-show="detail && detail.notes">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Catatan Tambahan</p>
                            <p class="font-semibold text-gray-700 text-sm leading-relaxed" x-text="detail ? detail.notes : ''"></p>
                        </div>
                    </div>
                </div>

                {{-- Detail Modal Footer --}}
                <div class="px-6 py-4 border-t border-gray-100 flex-shrink-0">
                    <button @click="isDetailOpen = false"
                        class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-bold transition text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- MODAL FORM SETORAN --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div x-show="isModalOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[300] flex items-end sm:items-center justify-center modal-backdrop p-4" style="display:none;">
            <div @click.away="isModalOpen = false" x-show="isModalOpen"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-5 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-5"
                 class="modal-panel w-full max-w-lg flex flex-col max-h-[92vh]">

                {{-- Form Modal Header --}}
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Setor Sampah</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Isi data penjemputan dengan lengkap dan benar</p>
                    </div>
                    <button type="button" @click="isModalOpen = false"
                        class="w-9 h-9 bg-gray-100 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form Modal Body --}}
                <div class="overflow-y-auto flex-1 p-6 modal-scroll">
                    <form method="POST" action="{{ route('pickups.store') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        {{-- Jenis Sampah --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2">
                                Jenis Sampah <span class="text-red-500">*</span>
                            </label>
                            <select name="type" required class="form-input">
                                <option value="" disabled selected>Pilih jenis sampah...</option>
                                @foreach(['Plastik' => '🥤', 'Kertas' => '📄', 'Logam' => '🔧', 'Kaca' => '🥛', 'Organik' => '🍂', 'Elektronik' => '⚡', 'Lainnya' => '📦'] as $type => $emoji)
                                    <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ $emoji }} {{ $type }}</option>
                                @endforeach
                            </select>
                            @error('type') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Berat & Tanggal --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-2">
                                    Berat (Kg) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="weight" step="0.1" min="0.1" required
                                       placeholder="Contoh: 5.0" value="{{ old('weight') }}"
                                       class="form-input">
                                @error('weight') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-2">
                                    Tanggal Jemput <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="pickup_date" required min="{{ date('Y-m-d') }}"
                                       value="{{ old('pickup_date') }}"
                                       class="form-input">
                                @error('pickup_date') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Alamat + GPS --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2">
                                Alamat Lengkap <span class="text-red-500">*</span>
                            </label>
                            <textarea name="address" id="addressInput" required rows="2"
                                      placeholder="Nama jalan, No. rumah, RT/RW, Kelurahan..."
                                      class="form-input resize-none">{{ old('address') }}</textarea>
                            @error('address') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror

                            <button type="button" id="detectLocationBtn" onclick="detectLocation()"
                                class="mt-2.5 w-full flex items-center justify-center gap-2 py-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-2xl font-bold text-xs transition active:scale-[0.98]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                <span id="detectBtnText">Deteksi Lokasi GPS</span>
                            </button>
                            <div id="gpsStatus" class="hidden mt-2.5 p-3 rounded-xl text-xs font-semibold"></div>
                            <div id="mapContainer" class="hidden mt-3">
                                <div id="map"></div>
                                <p class="bg-emerald-50 px-4 py-2 text-xs text-emerald-700 font-semibold mt-0 border-t border-emerald-100">
                                    📌 Seret pin untuk menyesuaikan lokasi
                                </p>
                            </div>
                        </div>

                        <input type="hidden" name="latitude" id="latInput" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="lngInput" value="{{ old('longitude') }}">

                        {{-- No. Telepon --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2">
                                No. Telepon (WhatsApp) <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="phone" required placeholder="08123456789"
                                   value="{{ old('phone') }}" class="form-input">
                            @error('phone') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Foto Sampah --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2">
                                Foto Sampah <span class="text-red-500">*</span>
                                <span class="text-gray-400 font-normal">(Maks 5MB)</span>
                            </label>
                            <div class="relative">
                                <input type="file" name="photo" accept="image/*" required
                                       @change="handlePhoto($event)"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="upload-zone p-5 text-center">
                                    <div x-show="photoPreview" class="mb-3">
                                        <img :src="photoPreview" class="w-full max-h-36 object-cover rounded-xl border border-gray-100 shadow-sm">
                                    </div>
                                    <div x-show="!photoPreview" class="py-2">
                                        <div class="w-10 h-10 bg-gray-200 rounded-xl flex items-center justify-center mx-auto mb-2">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs text-gray-500 font-semibold">Klik atau seret foto di sini</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">JPG, PNG, WEBP • Maks 5MB</p>
                                    </div>
                                    <p x-show="photoName" x-text="'✓ ' + photoName"
                                       class="text-xs text-emerald-600 font-bold mt-2 truncate"></p>
                                </div>
                            </div>
                            @error('photo') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Catatan --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-2">
                                Catatan <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <textarea name="notes" rows="2" maxlength="500"
                                      placeholder="Contoh: Titip di pos satpam, hubungi dulu sebelum datang..."
                                      class="form-input resize-none">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Submit --}}
                        <div class="flex gap-3 pt-3 border-t border-gray-100">
                            <button type="button" @click="isModalOpen = false"
                                class="flex-1 py-3.5 text-gray-500 hover:bg-gray-100 rounded-2xl font-bold transition text-sm">
                                Batal
                            </button>
                            <button type="submit" class="btn-submit flex-1">
                                Ajukan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = null, marker = null;
        function detectLocation() {
            const btn = document.getElementById('detectLocationBtn');
            const btnText = document.getElementById('detectBtnText');
            const status = document.getElementById('gpsStatus');
            if (!navigator.geolocation) {
                status.className = 'mt-2.5 p-3 rounded-xl text-xs font-semibold bg-red-50 text-red-600 border border-red-200';
                status.textContent = 'GPS tidak didukung browser ini';
                status.classList.remove('hidden'); return;
            }
            btnText.textContent = 'Mengakses GPS...'; btn.disabled = true;
            status.className = 'mt-2.5 p-3 rounded-xl text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200';
            status.textContent = 'Mendeteksi koordinat...'; status.classList.remove('hidden');
            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude, lng = pos.coords.longitude;
                document.getElementById('mapContainer').classList.remove('hidden');
                if (map) { map.remove(); map = null; }
                setTimeout(() => {
                    map = L.map('map').setView([lat, lng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
                    const icon = L.divIcon({
                        html: `<div style="width:28px;height:28px;background:#10b981;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 4px 12px rgba(16,185,129,0.5);"></div>`,
                        className:'', iconSize:[28,28], iconAnchor:[14,28]
                    });
                    marker = L.marker([lat, lng], { draggable: true, icon }).addTo(map);
                    marker.on('dragend', function(e) {
                        const p = e.target.getLatLng();
                        document.getElementById('latInput').value = p.lat.toFixed(8);
                        document.getElementById('lngInput').value = p.lng.toFixed(8);
                        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${p.lat}&lon=${p.lng}&format=json`)
                            .then(r=>r.json()).then(d=>{ if(d.display_name) document.getElementById('addressInput').value = d.display_name; });
                    });
                    document.getElementById('latInput').value = lat.toFixed(8);
                    document.getElementById('lngInput').value = lng.toFixed(8);
                }, 150);
                fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
                    .then(r=>r.json()).then(d=>{ if(d.display_name) document.getElementById('addressInput').value = d.display_name; });
                status.className = 'mt-2.5 p-3 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                status.textContent = '✓ Koordinat berhasil dideteksi! Seret pin untuk menyesuaikan.';
                btnText.textContent = '✓ Lokasi Terkunci'; btn.disabled = false;
            }, function(err) {
                let msg = 'Gagal mendeteksi lokasi';
                if (err.code === 1) msg = 'Izin GPS ditolak. Aktifkan di pengaturan browser.';
                status.className = 'mt-2.5 p-3 rounded-xl text-xs font-semibold bg-red-50 text-red-600 border border-red-200';
                status.textContent = msg; btnText.textContent = 'Coba Lagi'; btn.disabled = false;
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        }
    </script>
</x-app-layout>