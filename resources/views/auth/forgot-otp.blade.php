<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi OTP — EcoDrop</title>
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
                <div class="text-5xl mb-4">📧</div>
                <h2 class="text-white font-black text-2xl mb-3">Cek Email Kamu!</h2>
                <p class="text-green-100 text-sm leading-relaxed">
                    Kami telah mengirimkan kode OTP 6 digit ke email kamu. Masukkan kode tersebut untuk melanjutkan reset password.
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 border border-white/20">
                <p class="text-white text-sm font-bold mb-3">⚠️ Perhatian:</p>
                <ul class="space-y-2">
                    <li class="flex items-center gap-2 text-green-100 text-xs">
                        <span class="w-5 h-5 bg-green-400/30 rounded-full flex items-center justify-center text-xs flex-shrink-0">✓</span>
                        Kode berlaku selama 10 menit
                    </li>
                    <li class="flex items-center gap-2 text-green-100 text-xs">
                        <span class="w-5 h-5 bg-green-400/30 rounded-full flex items-center justify-center text-xs flex-shrink-0">✓</span>
                        Jangan bagikan kode ke siapapun
                    </li>
                    <li class="flex items-center gap-2 text-green-100 text-xs">
                        <span class="w-5 h-5 bg-green-400/30 rounded-full flex items-center justify-center text-xs flex-shrink-0">✓</span>
                        Cek folder spam jika tidak masuk
                    </li>
                </ul>
            </div>
        </div>
        <div class="relative z-10 text-green-200 text-xs">
            © 2026 EcoDrop. Semua hak dilindungi.
        </div>
    </div>

    {{-- Kanan: Form OTP --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-md">

            {{-- Mobile logo --}}
            <div class="lg:hidden flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-xl flex items-center justify-center text-xl">🌱</div>
                <span class="font-black text-xl text-gray-900">EcoDrop</span>
            </div>

            <div class="mb-8">
                <h1 class="text-3xl font-black text-gray-900 mb-2">Verifikasi OTP 🔐</h1>
                <p class="text-gray-500 text-sm">Kode OTP telah dikirim ke:</p>
                <p class="text-green-600 font-black text-sm mt-1">{{ $email }}</p>
            </div>

            {{-- Info --}}
            @if (session('info'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl flex items-start gap-3">
                    <span class="text-xl flex-shrink-0">✅</span>
                    <p class="text-green-700 text-sm font-semibold">{{ session('info') }}</p>
                </div>
            @endif

            {{-- Error --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3">
                    <span class="text-xl flex-shrink-0">❌</span>
                    <p class="text-red-700 text-sm font-semibold">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.otp.verify') }}"
                  x-data="{
                    inputs: ['','','','','',''],
                    get otp() { return this.inputs.join('') },
                    handleInput(index, e) {
                        const val = e.target.value.replace(/\D/g, '');
                        this.inputs[index] = val.slice(-1);
                        if (val && index < 5) {
                            this.$refs['otp' + (index+1)].focus();
                        }
                        if (this.otp.length === 6) {
                            this.$nextTick(() => this.$refs.submitBtn.click());
                        }
                    },
                    handleKeydown(index, e) {
                        if (e.key === 'Backspace' && !this.inputs[index] && index > 0) {
                            this.$refs['otp' + (index-1)].focus();
                        }
                    },
                    handlePaste(e) {
                        e.preventDefault();
                        const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                        text.split('').forEach((char, i) => { this.inputs[i] = char; });
                        if (text.length === 6) {
                            this.$nextTick(() => this.$refs.submitBtn.click());
                        }
                    }
                  }"
                  class="space-y-6">
                @csrf

                <input type="hidden" name="otp" :value="otp">

                {{-- 6 Kotak OTP --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-4 text-center">Masukkan 6 digit kode OTP</label>
                    <div class="flex gap-3 justify-center" @paste="handlePaste">
                        @for($i = 0; $i < 6; $i++)
                        <input type="text" inputmode="numeric" maxlength="1"
                               x-ref="otp{{ $i }}"
                               x-model="inputs[{{ $i }}]"
                               @input="handleInput({{ $i }}, $event)"
                               @keydown="handleKeydown({{ $i }}, $event)"
                               {{ $i === 0 ? 'autofocus' : '' }}
                               class="w-12 h-14 text-center text-2xl font-black border-2 border-gray-200 rounded-xl focus:border-green-500 focus:outline-none focus:bg-green-50 transition duration-200 text-gray-900">
                        @endfor
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" x-ref="submitBtn"
                    class="w-full py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-bold text-base shadow-lg hover:shadow-xl transition duration-300 hover:scale-[1.02] flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Verifikasi OTP
                </button>

                {{-- Resend OTP --}}
                <div x-data="{ countdown: 60, timer: null }"
                     x-init="timer = setInterval(() => { if (countdown > 0) countdown--; else clearInterval(timer); }, 1000)"
                     class="text-center">
                    <p class="text-sm text-gray-500 mb-2">Tidak menerima kode?</p>
                    <div x-show="countdown > 0" class="text-sm text-gray-400">
                        Kirim ulang dalam <span class="font-black text-green-600" x-text="countdown"></span> detik
                    </div>
                    <form x-show="countdown === 0" method="POST" action="{{ route('password.otp.resend') }}">
                        @csrf
                        <button type="submit"
                            class="text-sm text-green-600 hover:text-green-700 font-black underline transition">
                            Kirim Ulang OTP
                        </button>
                    </form>
                </div>

                {{-- Back --}}
                <div class="text-center">
                    <a href="{{ route('password.request') }}"
                       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-600 font-semibold transition duration-300">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Ganti email
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>