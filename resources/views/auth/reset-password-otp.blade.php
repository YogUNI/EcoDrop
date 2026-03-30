<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Password Baru — EcoDrop</title>
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
                <div class="text-5xl mb-4">🔒</div>
                <h2 class="text-white font-black text-2xl mb-3">Buat Password Baru</h2>
                <p class="text-green-100 text-sm leading-relaxed">
                    Hampir selesai! Buat password baru yang kuat untuk mengamankan akun EcoDrop kamu.
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 border border-white/20">
                <p class="text-white text-sm font-bold mb-3">💡 Tips Password Kuat:</p>
                <ul class="space-y-2">
                    <li class="flex items-center gap-2 text-green-100 text-xs">
                        <span class="w-5 h-5 bg-green-400/30 rounded-full flex items-center justify-center text-xs flex-shrink-0">✓</span>
                        Minimal 8 karakter
                    </li>
                    <li class="flex items-center gap-2 text-green-100 text-xs">
                        <span class="w-5 h-5 bg-green-400/30 rounded-full flex items-center justify-center text-xs flex-shrink-0">✓</span>
                        Kombinasi huruf besar & kecil
                    </li>
                    <li class="flex items-center gap-2 text-green-100 text-xs">
                        <span class="w-5 h-5 bg-green-400/30 rounded-full flex items-center justify-center text-xs flex-shrink-0">✓</span>
                        Tambahkan angka atau simbol
                    </li>
                </ul>
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
                <h1 class="text-3xl font-black text-gray-900 mb-2">Password Baru 🔑</h1>
                <p class="text-gray-500 text-sm">Buat password baru yang kuat untuk akun EcoDrop kamu.</p>
            </div>

            {{-- Error --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3">
                    <span class="text-xl flex-shrink-0">❌</span>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p class="text-red-700 text-sm font-semibold">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.otp.update') }}"
                  x-data="{ showPass: false, showConfirm: false }"
                  class="space-y-5">
                @csrf

                {{-- Password Baru --}}
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">🔑 Password Baru</label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'"
                               id="password" name="password"
                               required autofocus autocomplete="new-password"
                               placeholder="Minimal 8 karakter"
                               class="w-full px-4 py-3 pr-12 rounded-xl border-2 {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-green-500' }} focus:outline-none transition duration-300 font-medium text-gray-900 bg-gray-50 focus:bg-white">
                        <button type="button" @click="showPass = !showPass"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-2">⚠️ {{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">🔑 Konfirmasi Password</label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'"
                               id="password_confirmation" name="password_confirmation"
                               required autocomplete="new-password"
                               placeholder="Ulangi password baru"
                               class="w-full px-4 py-3 pr-12 rounded-xl border-2 {{ $errors->has('password_confirmation') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-green-500' }} focus:outline-none transition duration-300 font-medium text-gray-900 bg-gray-50 focus:bg-white">
                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <svg x-show="!showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-2">⚠️ {{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-bold text-base shadow-lg hover:shadow-xl transition duration-300 hover:scale-[1.02] flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Simpan Password Baru
                </button>

                {{-- Back --}}
                <div class="text-center pt-2">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-600 font-semibold transition duration-300">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Kembali ke login
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>