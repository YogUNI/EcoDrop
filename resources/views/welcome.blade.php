<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EcoDrop | Solusi Cerdas Pengelolaan Sampah</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .bg-pattern {
            background-color: #f0fdf4;
            background-image: radial-gradient(#10b981 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            background-opacity: 0.1;
        }
    </style>
</head>
<body class="antialiased bg-pattern text-gray-900">

    <nav x-data="{ atTop: true }" 
         @scroll.window="atTop = (window.pageYOffset > 10 ? false : true)"
         :class="{ 'glass py-4 shadow-lg': !atTop, 'py-6': atTop }"
         class="fixed w-full z-50 transition-all duration-300 px-6 lg:px-12 flex justify-between items-center">
        
        <div class="flex items-center space-x-2">
            <div class="bg-green-600 p-2 rounded-lg shadow-inner">
                <span class="text-2xl text-white">♻️</span>
            </div>
            <span class="text-2xl font-extrabold tracking-tight text-green-800">EcoDrop</span>
        </div>

        <div class="hidden md:flex items-center space-x-8">
            <a href="#fitur" class="font-medium hover:text-green-600 transition">Fitur</a>
            <a href="#cara-kerja" class="font-medium hover:text-green-600 transition">Cara Kerja</a>
            @if (Route::has('login'))
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-green-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-green-700 transition shadow-lg shadow-green-200">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-bold text-gray-700 hover:text-green-600 transition">Masuk</a>
                        <a href="{{ route('register') }}" class="bg-green-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-green-700 transition shadow-lg shadow-green-200">Mulai Sekarang</a>
                    @endauth
                </div>
            @endif
        </div>
    </nav>

    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 overflow-hidden">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center relative z-10">
            <div class="lg:w-1/2 text-center lg:text-left animate__animated animate__fadeInLeft">
                <span class="inline-block px-4 py-1.5 mb-6 text-sm font-bold tracking-wider text-green-700 uppercase bg-green-100 rounded-full">
                    🌱 Inisiatif Lingkungan Digital
                </span>
                <h1 class="text-5xl lg:text-7xl font-extrabold leading-[1.1] mb-8 text-gray-900">
                    Bikin Sampah Jadi <br>
                    <span class="gradient-text italic underline decoration-green-300">Cuan & Kebaikan</span>
                </h1>
                <p class="text-lg lg:text-xl text-gray-600 mb-10 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    EcoDrop memudahkan kamu mengelola sampah anorganik rumah tangga secara cerdas. Jemput gratis, dapat poin, bumi makin asri!
                </p>
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('register') }}" class="group relative px-8 py-4 bg-green-600 text-white rounded-2xl font-bold text-lg hover:bg-green-700 transition duration-300 shadow-xl shadow-green-200 overflow-hidden">
                        <span class="relative z-10 text-white">Daftar Sekarang — Gratis</span>
                    </a>
                    <a href="#fitur" class="px-8 py-4 glass text-green-700 rounded-2xl font-bold text-lg hover:bg-white transition duration-300">
                        Lihat Keunggulan
                    </a>
                </div>
            </div>

            <div class="lg:w-1/2 mt-16 lg:mt-0 relative animate__animated animate__fadeInRight lg:pl-10">
                <div class="relative w-full max-w-lg mx-auto">
                    <div class="absolute -top-10 -left-10 w-32 h-32 bg-yellow-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
                    <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-green-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
                    
                    <div class="relative glass p-4 rounded-[2.5rem] shadow-2xl rotate-3 hover:rotate-0 transition duration-500">
                        <div class="bg-green-600 w-full h-80 rounded-[2rem] flex flex-col items-center justify-center text-white overflow-hidden relative">
                            <span class="text-9xl animate-bounce">📦</span>
                            <div class="absolute bottom-4 left-0 right-0 text-center">
                                <p class="text-sm font-medium opacity-80 uppercase tracking-widest">Total Sampah Terkumpul</p>
                                <p class="text-2xl font-bold italic">1.240 KG Bulan Ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-20">
                <h2 class="text-sm font-bold text-green-600 uppercase tracking-[0.2em] mb-4">Kenapa EcoDrop?</h2>
                <p class="text-4xl font-extrabold text-gray-900">Kelola Sampah Tanpa Ribet</p>
            </div>

            <div class="grid md:grid-cols-3 gap-10">
                <div class="group p-10 rounded-3xl bg-green-50 border border-transparent hover:border-green-200 hover:bg-white hover:shadow-2xl transition duration-500">
                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-3xl shadow-sm mb-8 group-hover:scale-110 transition duration-500">🚚</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-900 italic">Jemput di Rumah</h3>
                    <p class="text-gray-600 leading-relaxed text-sm">Nggak perlu repot keluar rumah. Jadwalkan penjemputan lewat aplikasi, kurir kami langsung meluncur.</p>
                </div>

                <div class="group p-10 rounded-3xl bg-green-50 border border-transparent hover:border-green-200 hover:bg-white hover:shadow-2xl transition duration-500">
                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-3xl shadow-sm mb-8 group-hover:scale-110 transition duration-500">💎</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-900 italic">Eco-Reward</h3>
                    <p class="text-gray-600 leading-relaxed text-sm">Sampahmu punya nilai. Kumpulkan poin dari setiap KG sampah yang disetor dan tukar dengan hadiah menarik.</p>
                </div>

                <div class="group p-10 rounded-3xl bg-green-50 border border-transparent hover:border-green-200 hover:bg-white hover:shadow-2xl transition duration-500">
                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-3xl shadow-sm mb-8 group-hover:scale-110 transition duration-500">📊</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-900 italic">Pantau Kontribusi</h3>
                    <p class="text-gray-600 leading-relaxed text-sm">Lihat statistik berapa banyak CO2 yang berhasil kamu kurangi dengan menyetor sampah secara rutin.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-12 border-t border-gray-100 bg-white">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-gray-400 text-sm font-medium">
                &copy; 2026 <span class="text-green-600 font-bold">YoHaTo Labs</span>. Crafted with ❤️ for a better Earth.
            </p>
        </div>
    </footer>

</body>
</html>