<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EcoDrop | Solusi Cerdas Pengelolaan Sampah</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }

        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.4);
        }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }

        /* Blob animation */
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(20px, -30px) scale(1.1); }
            50% { transform: translate(-15px, 20px) scale(0.9); }
            75% { transform: translate(30px, 10px) scale(1.05); }
        }
        .animate-blob { animation: blob 8s infinite; }
        .delay-2000 { animation-delay: 2s; }
        .delay-4000 { animation-delay: 4s; }

        /* Counter animation */
        @keyframes countUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .count-up { animation: countUp 0.5s ease forwards; }

        /* Gradient border card */
        .gradient-border {
            position: relative;
            background: white;
            border-radius: 24px;
        }
        .gradient-border::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 26px;
            background: linear-gradient(135deg, #10b981, #3b82f6, #8b5cf6);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .gradient-border:hover::before { opacity: 1; }

        /* Hero card float */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(2deg); }
            50% { transform: translateY(-12px) rotate(2deg); }
        }
        .float-card { animation: float 4s ease-in-out infinite; }

        /* Typewriter cursor */
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        .cursor { animation: blink 1s infinite; }

        /* Noise texture overlay */
        .noise::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            opacity: 0.4;
            pointer-events: none;
        }

        /* Step connector line */
        .step-line::after {
            content: '';
            position: absolute;
            top: 2rem;
            left: calc(50% + 3rem);
            width: calc(100% - 6rem);
            height: 2px;
            background: linear-gradient(90deg, #10b981, #d1fae5);
        }
    </style>
</head>
<body class="antialiased bg-[#f8fffe] text-gray-900 overflow-x-hidden">

    {{-- ═══════════════════════ NAVBAR ═══════════════════════ --}}
    <nav x-data="{ atTop: true, mobileOpen: false }"
         @scroll.window="atTop = window.pageYOffset < 20"
         :class="atTop ? 'py-5 bg-transparent' : 'py-3 bg-white/90 backdrop-blur-xl shadow-sm border-b border-gray-100'"
         class="fixed w-full z-50 transition-all duration-400 px-6 lg:px-16">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-green-200 transition duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <span class="text-xl font-black text-gray-900">Eco<span class="text-green-600">Drop</span></span>
            </a>

            {{-- Desktop Nav --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="#fitur" class="text-sm font-semibold text-gray-600 hover:text-green-600 transition">Fitur</a>
                <a href="#cara-kerja" class="text-sm font-semibold text-gray-600 hover:text-green-600 transition">Cara Kerja</a>
                <a href="#statistik" class="text-sm font-semibold text-gray-600 hover:text-green-600 transition">Dampak</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-green-600 text-white rounded-xl font-bold text-sm hover:bg-green-700 transition shadow-lg shadow-green-200/50">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-gray-700 hover:text-green-600 transition">Masuk</a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 bg-green-600 text-white rounded-xl font-bold text-sm hover:bg-green-700 transition shadow-lg shadow-green-200/50">
                        Daftar Gratis
                    </a>
                @endauth
            </div>

            {{-- Mobile Toggle --}}
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" x-transition class="md:hidden mt-4 pb-4 space-y-3 px-2">
            <a href="#fitur" @click="mobileOpen=false" class="block px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:bg-green-50">Fitur</a>
            <a href="#cara-kerja" @click="mobileOpen=false" class="block px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:bg-green-50">Cara Kerja</a>
            <a href="#statistik" @click="mobileOpen=false" class="block px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:bg-green-50">Dampak</a>
            @auth
                <a href="{{ url('/dashboard') }}" class="block px-4 py-3 rounded-xl text-sm font-bold text-white bg-green-600">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="block px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 hover:bg-green-50">Masuk</a>
                <a href="{{ route('register') }}" class="block px-4 py-3 rounded-xl text-sm font-bold text-white bg-green-600">Daftar Gratis</a>
            @endauth
        </div>
    </nav>

    {{-- ═══════════════════════ HERO ═══════════════════════ --}}
    <section class="relative min-h-screen flex items-center pt-20 overflow-hidden">
        {{-- Background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-green-50 via-emerald-50/50 to-white"></div>
        <div class="absolute top-20 left-10 w-96 h-96 bg-green-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-40 right-10 w-96 h-96 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob delay-2000"></div>
        <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob delay-4000"></div>

        {{-- Grid pattern --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#10b981 1px, transparent 1px), linear-gradient(90deg, #10b981 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="max-w-7xl mx-auto px-6 lg:px-16 relative z-10 py-20">
            <div class="grid lg:grid-cols-2 gap-16 items-center">

                {{-- Left: Text --}}
                <div class="animate__animated animate__fadeInLeft">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 rounded-full mb-8">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-xs font-bold text-green-700 uppercase tracking-widest">Platform Pengelolaan Sampah #1</span>
                    </div>

                    <h1 class="text-5xl lg:text-6xl xl:text-7xl font-black leading-[1.05] mb-8 text-gray-900">
                        Sampah Jadi
                        <span class="gradient-text block">Nilai & Cuan</span>
                        Untuk Bumi
                    </h1>

                    <p class="text-lg text-gray-500 mb-10 max-w-lg leading-relaxed font-medium">
                        EcoDrop mengubah kebiasaan buang sampah menjadi kontribusi nyata. Setor sampah, kumpulkan poin, dan jadilah bagian dari gerakan hijau Indonesia.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mb-12">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                               class="group inline-flex items-center justify-center gap-3 px-8 py-4 bg-green-600 text-white rounded-2xl font-bold text-lg hover:bg-green-700 transition duration-300 shadow-2xl shadow-green-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                Buka Dashboard
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}"
                               class="group inline-flex items-center justify-center gap-3 px-8 py-4 bg-green-600 text-white rounded-2xl font-bold text-lg hover:bg-green-700 transition duration-300 shadow-2xl shadow-green-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                Mulai Gratis Sekarang
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <a href="#cara-kerja"
                               class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-gray-700 rounded-2xl font-bold text-lg hover:bg-gray-50 transition duration-300 shadow-lg border border-gray-100">
                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Lihat Cara Kerja
                            </a>
                        @endauth
                    </div>

                    {{-- Social proof --}}
                    <div class="flex items-center gap-6">
                        <div class="flex -space-x-3">
                            @foreach(['4ade80', '34d399', '10b981', '059669', '047857'] as $color)
                                <div class="w-10 h-10 rounded-full border-2 border-white bg-gradient-to-br from-green-{{ $loop->index * 100 + 300 }} to-emerald-{{ $loop->index * 100 + 400 }} flex items-center justify-center text-white font-bold text-xs shadow">
                                    {{ chr(65 + $loop->index) }}
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <div class="flex items-center gap-1 mb-0.5">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="text-xs font-semibold text-gray-500">Dipercaya <span class="text-green-600 font-bold">1,200+</span> pengguna aktif</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Dashboard Preview Card --}}
                <div class="animate__animated animate__fadeInRight relative">
                    <div class="float-card relative">
                        {{-- Main card --}}
                        <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                            {{-- Card header --}}
                            <div class="bg-gradient-to-r from-green-600 to-emerald-500 p-6 text-white">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-green-100 font-medium">EcoDrop Dashboard</p>
                                            <p class="text-sm font-bold">Halo, Eco-Warrior!</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-1.5">
                                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-green-100 text-xs uppercase font-bold tracking-widest mb-1">Saldo Poin Kamu</p>
                                    <p class="text-5xl font-black">2,480</p>
                                    <p class="text-green-200 text-xs mt-1">+120 poin minggu ini</p>
                                </div>
                            </div>

                            {{-- Card body --}}
                            <div class="p-6 space-y-4">
                                {{-- Stat mini cards --}}
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-green-50 rounded-2xl p-3 text-center">
                                        <p class="text-2xl font-black text-green-600">12</p>
                                        <p class="text-xs text-gray-500 font-medium">Setoran</p>
                                    </div>
                                    <div class="bg-blue-50 rounded-2xl p-3 text-center">
                                        <p class="text-2xl font-black text-blue-600">48</p>
                                        <p class="text-xs text-gray-500 font-medium">Kg Sampah</p>
                                    </div>
                                    <div class="bg-purple-50 rounded-2xl p-3 text-center">
                                        <p class="text-2xl font-black text-purple-600">95%</p>
                                        <p class="text-xs text-gray-500 font-medium">Approved</p>
                                    </div>
                                </div>

                                {{-- Recent activity --}}
                                <div class="space-y-2">
                                    @foreach([
                                        ['Plastik', '3.5 Kg', 'approved', '+35'],
                                        ['Kertas', '2.0 Kg', 'approved', '+20'],
                                        ['Elektronik', '1.5 Kg', 'pending', '...'],
                                    ] as $item)
                                    <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-800">{{ $item[0] }}</p>
                                                <p class="text-xs text-gray-400">{{ $item[1] }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-bold {{ $item[2] === 'approved' ? 'text-green-600' : 'text-yellow-600' }}">
                                                {{ $item[2] === 'approved' ? $item[3].' pts' : 'Pending' }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Floating badges --}}
                        <div class="absolute -top-4 -right-4 bg-white rounded-2xl shadow-xl p-3 border border-gray-100">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-800">Setoran Acc!</p>
                                    <p class="text-xs text-green-600 font-bold">+50 Poin</p>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl shadow-xl p-3 border border-gray-100">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-800">Total Terkumpul</p>
                                    <p class="text-xs text-blue-600 font-bold">1,240 Kg</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ STATS ═══════════════════════ --}}
    <section id="statistik" class="py-20 bg-gradient-to-r from-green-600 to-emerald-500 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

        <div class="max-w-7xl mx-auto px-6 lg:px-16 relative z-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                @foreach([
                    ['1,240+', 'Kg Sampah Terkumpul', 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
                    ['320+', 'Pengguna Aktif', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['95%', 'Tingkat Kepuasan', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['50+', 'Ton CO₂ Dicegah', 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ] as $stat)
                <div class="text-center reveal">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat[2] }}"/>
                        </svg>
                    </div>
                    <p class="text-4xl font-black text-white mb-2">{{ $stat[0] }}</p>
                    <p class="text-green-100 text-sm font-semibold">{{ $stat[1] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ FITUR ═══════════════════════ --}}
    <section id="fitur" class="py-28 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-16">
            <div class="text-center mb-20 reveal">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 rounded-full text-xs font-bold text-green-700 uppercase tracking-widest mb-6">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    Keunggulan Platform
                </span>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mb-6">Kenapa Pilih <span class="gradient-text">EcoDrop</span>?</h2>
                <p class="text-gray-500 max-w-2xl mx-auto text-lg">Platform terlengkap untuk mengelola sampah anorganik rumah tangga dengan sistem reward yang transparan dan adil.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @foreach([
                    [
                        'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                        'Penjemputan Terjadwal',
                        'Atur jadwal penjemputan sampah langsung dari dashboard. Kurir kami akan datang tepat waktu ke lokasi kamu.',
                        'from-green-400 to-emerald-500',
                        'bg-green-50'
                    ],
                    [
                        'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'Sistem Reward Transparan',
                        'Setiap kilogram sampah yang disetor langsung dikonversi jadi poin. Pantau semua riwayat poin kamu secara real-time.',
                        'from-blue-400 to-indigo-500',
                        'bg-blue-50'
                    ],
                    [
                        'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                        'Dashboard Analytics',
                        'Admin panel lengkap dengan grafik real-time, statistik setoran, dan activity log untuk memantau seluruh operasional.',
                        'from-purple-400 to-pink-500',
                        'bg-purple-50'
                    ],
                    [
                        'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
                        'Multi Jenis Sampah',
                        'Terima berbagai jenis sampah: plastik, kertas, logam, kaca, elektronik, organik, dan lainnya dengan poin berbeda.',
                        'from-orange-400 to-amber-500',
                        'bg-orange-50'
                    ],
                    [
                        'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        'Verifikasi Admin 3 Layer',
                        'Sistem keamanan berlapis: Super Admin, Admin terverifikasi, dan User — memastikan setiap setoran diproses dengan benar.',
                        'from-teal-400 to-cyan-500',
                        'bg-teal-50'
                    ],
                    [
                        'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                        'Notifikasi Real-time',
                        'Dapatkan update status setoran secara langsung. Admin online? Langsung terlihat dari dashboard Super Admin.',
                        'from-rose-400 to-pink-500',
                        'bg-rose-50'
                    ],
                ] as $i => $feat)
                <div class="gradient-border p-8 reveal reveal-delay-{{ ($i % 3) + 1 }} hover:shadow-xl transition duration-500 group">
                    <div class="w-14 h-14 bg-gradient-to-br {{ $feat[3] }} rounded-2xl flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feat[0] }}"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-3">{{ $feat[1] }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $feat[2] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ CARA KERJA ═══════════════════════ --}}
    <section id="cara-kerja" class="py-28 bg-gradient-to-br from-gray-50 to-green-50/30">
        <div class="max-w-7xl mx-auto px-6 lg:px-16">
            <div class="text-center mb-20 reveal">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 rounded-full text-xs font-bold text-green-700 uppercase tracking-widest mb-6">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Simpel & Cepat
                </span>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mb-6">Cara Kerja <span class="gradient-text">EcoDrop</span></h2>
                <p class="text-gray-500 max-w-xl mx-auto text-lg">Cuma 4 langkah mudah untuk mulai mengubah sampah jadi nilai</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8 relative">
                @foreach([
                    ['M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'Daftar Akun', 'Buat akun gratis dalam 2 menit. Tidak perlu kartu kredit atau biaya apapun.', 'from-green-400 to-emerald-500', '01'],
                    ['M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'Ajukan Setoran', 'Isi form setoran dengan jenis sampah, berat, dan jadwal penjemputan yang kamu inginkan.', 'from-blue-400 to-indigo-500', '02'],
                    ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'Admin Verifikasi', 'Tim admin kami akan memproses dan memverifikasi setoran kamu dengan cepat dan transparan.', 'from-purple-400 to-violet-500', '03'],
                    ['M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'Terima Poin', 'Poin langsung masuk ke saldo akunmu setelah disetujui. Kumpulkan dan tukar dengan reward!', 'from-amber-400 to-orange-500', '04'],
                ] as $i => $step)
                <div class="relative reveal reveal-delay-{{ $i + 1 }}">
                    {{-- Connector line --}}
                    @if($i < 3)
                        <div class="hidden md:block absolute top-8 left-[calc(50%+3rem)] w-[calc(100%-1rem)] h-0.5 bg-gradient-to-r from-green-300 to-transparent z-0"></div>
                    @endif

                    <div class="relative z-10 text-center">
                        <div class="relative inline-block mb-6">
                            <div class="w-16 h-16 bg-gradient-to-br {{ $step[3] }} rounded-2xl flex items-center justify-center shadow-xl mx-auto">
                                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step[0] }}"/>
                                </svg>
                            </div>
                            <span class="absolute -top-2 -right-2 w-6 h-6 bg-gray-900 text-white text-xs font-black rounded-full flex items-center justify-center">{{ $i + 1 }}</span>
                        </div>
                        <h3 class="text-lg font-black text-gray-900 mb-3">{{ $step[1] }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ $step[2] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ CTA ═══════════════════════ --}}
    <section class="py-28 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-green-600 via-emerald-600 to-teal-600"></div>
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 25% 50%, white 1px, transparent 1px), radial-gradient(circle at 75% 50%, white 1px, transparent 1px); background-size: 30px 30px;"></div>

        <div class="max-w-4xl mx-auto px-6 text-center relative z-10 reveal">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 rounded-full text-white text-xs font-bold uppercase tracking-widest mb-8">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Mulai Sekarang — Gratis
            </div>
            <h2 class="text-4xl lg:text-6xl font-black text-white mb-8 leading-tight">
                Siap Jadi Bagian dari<br>
                <span class="text-green-200">Gerakan Hijau Indonesia?</span>
            </h2>
            <p class="text-green-100 text-xl mb-12 max-w-2xl mx-auto leading-relaxed">
                Bergabung dengan ribuan pengguna yang sudah merasakan manfaat EcoDrop. Daftar gratis, tidak ada biaya tersembunyi.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="inline-flex items-center gap-3 px-10 py-5 bg-white text-green-700 rounded-2xl font-black text-lg hover:bg-green-50 transition duration-300 shadow-2xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Buka Dashboard Saya
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-3 px-10 py-5 bg-white text-green-700 rounded-2xl font-black text-lg hover:bg-green-50 transition duration-300 shadow-2xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Daftar Gratis Sekarang
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-3 px-10 py-5 bg-white/10 text-white border-2 border-white/30 rounded-2xl font-black text-lg hover:bg-white/20 transition duration-300">
                        Sudah Punya Akun
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endauth
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ FOOTER ═══════════════════════ --}}
    <footer class="py-16 bg-gray-900">
        <div class="max-w-7xl mx-auto px-6 lg:px-16">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </div>
                        <span class="text-xl font-black text-white">Eco<span class="text-green-400">Drop</span></span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed max-w-sm mb-6">Platform pengelolaan sampah berbasis reward yang memudahkan masyarakat berkontribusi untuk lingkungan yang lebih baik.</p>
                    <div class="flex gap-3">
                        @foreach(['M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.328-.216-8.16-2.29-10.726-5.44-1.409 2.418-.669 5.58 1.795 7.177-.886-.031-1.719-.272-2.447-.678-.05 2.452 1.699 4.757 4.252 5.265-.893.243-1.84.031-2.51-.468-.125 2.338 1.621 4.527 4.045 5.009-1.527 1.195-3.451 1.691-5.373 1.467 2.019 1.293 4.416 2.049 6.993 2.049 8.434 0 13.042-6.987 13.042-13.042 0-.198-.005-.396-.014-.592.896-.647 1.674-1.455 2.288-2.376z', 'M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22', 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M4 6a2 2 0 100-4 2 2 0 000 4z'] as $icon)
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-xl flex items-center justify-center transition duration-300">
                            <svg class="w-5 h-5 text-gray-400 hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
                        </a>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 text-sm uppercase tracking-widest">Platform</h4>
                    <ul class="space-y-3">
                        @foreach(['Fitur', 'Cara Kerja', 'Dampak Lingkungan', 'Reward'] as $link)
                        <li><a href="#" class="text-gray-400 hover:text-green-400 text-sm transition duration-200">{{ $link }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 text-sm uppercase tracking-widest">Akun</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('register') }}" class="text-gray-400 hover:text-green-400 text-sm transition duration-200">Daftar Gratis</a></li>
                        <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-green-400 text-sm transition duration-200">Masuk</a></li>
                        <li><a href="{{ route('admin.login') }}" class="text-gray-400 hover:text-green-400 text-sm transition duration-200">Login Admin</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">© 2026 <span class="text-green-400 font-bold">YoHaTo Labs</span>. Crafted with ❤️ for a better Earth.</p>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-gray-500 text-xs font-medium">Semua sistem berjalan normal</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- Scroll reveal script --}}
    <script>
        const revealElements = document.querySelectorAll('.reveal');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        revealElements.forEach(el => observer.observe(el));
    </script>

</body>
</html>