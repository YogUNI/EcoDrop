<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password — EcoDrop</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen flex">

    {{-- Kiri: Branding --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-green-500 via-emerald-600 to-teal-700 flex-col justify-between p-12 relative overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 left-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-80 h-80 bg-emerald-300/20 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-teal-400/10 rounded-full blur-3xl"></div>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-2xl shadow-lg">🌱</div>
                <span class="text-white font-black text-2xl tracking-tight">EcoDrop</span>
            </div>
            <p class="text-green-100 text-sm font-medium">Platform Manajemen Sampah Berbasis Reward</p>
        </div>
        <div class="relative z-10">
            <div class="bg-white/10 backdrop-blur-sm rounded-3xl p-8 border border-white/20 mb-8">
                <div class="text-5xl mb-4">🔑</div>
                <h2 class="text-white font-black text-2xl mb-3">Lupa Password?</h2>
                <p class="text-green-100 text-sm leading-relaxed">
                    Tenang! Masukkan email kamu dan kami akan mengirimkan link untuk reset password ke inbox kamu.
                </p>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center border border-white/20">
                    <div class="text-2xl mb-1">📧</div>
                    <p class="text-white text-xs font-bold">Cek Email</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center border border-white/20">
                    <div class="text-2xl mb-1">🔗</div>
                    <p class="text-white text-xs font-bold">Klik Link</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 text-center border border-white/20">
                    <div class="text-2xl mb-1">✅</div>
                    <p class="text-white text-xs font-bold">Reset Done</p>
                </div>
            </div>
        </div>
        <div class="relative z-10 text-green-200 text-xs">
            © 2026 EcoDrop. Semua hak dilindungi.
        </div>
    </div>

    {{-- Kanan: Form --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-md">

            {{-- Mobile logo --}}
            <div class="lg:hidden flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-xl flex items-center justify-center text-xl">🌱</div>
                <span class="font-black text-xl text-gray-900">EcoDrop</span>
            </div>

            <div class="mb-8">
                <h1 class="text-3xl font-black text-gray-900 mb-2">Lupa Password 🔑</h1>
                <p class="text-gray-500 text-sm">Masukkan email akun EcoDrop kamu, kami akan kirimkan link reset password.</p>
            </div>

            {{-- Status sukses --}}
            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl flex items-start gap-3">
                    <span class="text-2xl flex-shrink-0">✅</span>
                    <div>
                        <p class="font-bold text-green-800 text-sm">Link terkirim!</p>
                        <p class="text-green-700 text-xs mt-0.5">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                        📧 Alamat Email
                    </label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           required autofocus
                           placeholder="contoh@gmail.com"
                           class="w-full px-4 py-3 rounded-xl border-2 {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-green-500' }} focus:outline-none transition duration-300 font-medium text-gray-900 bg-gray-50 focus:bg-white">
                    @error('email')
                        <p class="text-red-500 text-xs mt-2 flex items-center gap-1">
                            <span>⚠️</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-bold text-base shadow-lg hover:shadow-xl transition duration-300 hover:scale-[1.02] flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Kirim Link Reset Password
                </button>

                {{-- Back to login --}}
                <div class="text-center pt-2">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-600 font-semibold transition duration-300">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Kembali ke halaman login
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>