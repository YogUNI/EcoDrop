<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi OTP | EcoDrop</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="antialiased bg-[#f8fffe]">
<div class="min-h-screen flex items-center justify-center py-12 px-4 relative overflow-hidden">

    {{-- Background blobs --}}
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-green-100 rounded-full filter blur-3xl opacity-60"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-100 rounded-full filter blur-3xl opacity-60"></div>
    <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(#10b981 1px, transparent 1px), linear-gradient(90deg, #10b981 1px, transparent 1px); background-size: 40px 40px;"></div>

    <div class="max-w-5xl w-full bg-white shadow-2xl rounded-[32px] overflow-hidden lg:grid lg:grid-cols-2 border border-gray-100 relative z-10">

        {{-- Left Panel --}}
        <div class="hidden lg:flex flex-col justify-between bg-gradient-to-br from-green-600 via-emerald-600 to-teal-600 p-12 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 24px 24px;"></div>

            {{-- Logo --}}
            <a href="/" class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <span class="text-xl font-black text-white">EcoDrop</span>
            </a>

            {{-- Center content --}}
            <div class="relative z-10 my-8">
                <div class="w-20 h-20 bg-white/20 rounded-3xl flex items-center justify-center mb-8 shadow-xl">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-4xl font-black text-white mb-4 leading-tight">
                    Cek Email<br>Kamu!
                </h2>
                <p class="text-green-100 text-base font-medium leading-relaxed mb-8">
                    Kami telah mengirimkan kode verifikasi 6 digit ke email kamu. Masukkan kode tersebut untuk melanjutkan.
                </p>

                {{-- Info cards --}}
                <div class="space-y-3">
                    @foreach([
                        ['M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'Cek folder Inbox atau Spam'],
                        ['M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'Kode berlaku selama 10 menit'],
                        ['M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'Jangan bagikan kode ke siapapun'],
                    ] as $item)
                    <div class="flex items-center gap-3 bg-white/10 rounded-2xl px-4 py-3">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item[0] }}"/>
                            </svg>
                        </div>
                        <p class="text-white text-sm font-semibold">{{ $item[1] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Bottom --}}
            <p class="text-green-200 text-xs relative z-10">© 2026 EcoDrop — YoHaTo Labs</p>
        </div>

        {{-- Right Panel --}}
        <div class="p-8 sm:p-12 lg:p-14 flex flex-col justify-center">

            {{-- Mobile logo --}}
            <div class="lg:hidden flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <span class="text-xl font-black text-gray-900">Eco<span class="text-green-600">Drop</span></span>
            </div>

            {{-- Header --}}
            <div class="mb-8">
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-gray-900 mb-2">Verifikasi OTP</h1>
                <p class="text-gray-500 font-medium text-sm leading-relaxed">
                    Masukkan 6 digit kode yang telah dikirim ke<br>
                    @if(session('otp_email'))
                        <span class="font-bold text-gray-700">{{ session('otp_email') }}</span>
                    @else
                        <span class="font-bold text-gray-700">email kamu</span>
                    @endif
                </p>
            </div>

            {{-- Flash: error dari session --}}
            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-red-700">{{ $errors->first() }}</p>
                </div>
            @endif

            {{-- Flash: success / status --}}
            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-green-800">{{ session('status') }}</p>
                </div>
            @endif

            {{-- OTP Form --}}
            <form method="POST" action="{{ route('otp.verify') }}" id="otp-form" class="space-y-6">
                @csrf

                {{-- 6 kotak OTP --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-4">Kode Verifikasi</label>
                    <div class="flex items-center justify-between gap-2">
                        @for ($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            pattern="[0-9]"
                            data-index="{{ $i }}"
                            class="otp-box w-full aspect-square max-w-[56px] text-center text-2xl font-black bg-gray-50 border-2 border-gray-200 rounded-2xl focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none transition duration-200 text-gray-900"
                            autocomplete="off"
                        />
                        @endfor
                    </div>
                    {{-- Hidden input yang dikirim sebagai field 'otp' --}}
                    <input type="hidden" name="otp" id="otp-hidden" />
                </div>

                {{-- Tombol verifikasi --}}
                <button type="submit" id="btn-verify"
                    class="w-full py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-black rounded-2xl shadow-lg shadow-green-200 transition duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 text-base">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Verifikasi Sekarang
                </button>
            </form>

            {{-- Divider --}}
            <div class="my-6 flex items-center gap-4">
                <div class="flex-1 h-px bg-gray-100"></div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">atau</p>
                <div class="flex-1 h-px bg-gray-100"></div>
            </div>

            {{-- Resend OTP --}}
            <div class="text-center">
                <p class="text-gray-500 text-sm font-medium mb-3">Tidak menerima kode?</p>

                <form method="POST" action="{{ route('otp.resend') }}" id="resend-form">
                    @csrf
                    <button type="submit" id="btn-resend"
                        class="w-full inline-flex items-center justify-center gap-2 py-3.5 border-2 border-gray-200 text-gray-700 rounded-2xl font-bold text-sm hover:border-green-400 hover:text-green-600 hover:bg-green-50 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-gray-200 disabled:hover:text-gray-700 disabled:hover:bg-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span id="resend-text">Kirim Ulang Kode</span>
                    </button>
                </form>

                <p id="countdown-text" class="text-xs text-gray-400 font-medium mt-2 hidden">
                    Kirim ulang dalam <span id="countdown-timer" class="font-black text-green-600">60</span> detik
                </p>
            </div>

            {{-- Back to login --}}
            <p class="text-center text-xs text-gray-400 mt-6">
                <a href="{{ route('login') }}" class="text-green-600 font-bold hover:underline inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke halaman login
                </a>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── OTP Box Logic ─────────────────────────────────────────────────
    const boxes      = document.querySelectorAll('.otp-box');
    const hiddenInput = document.getElementById('otp-hidden');

    function syncHidden() {
        hiddenInput.value = Array.from(boxes).map(b => b.value).join('');
    }

    boxes.forEach((box, index) => {
        box.addEventListener('keydown', function (e) {
            const allowed = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'];
            if (allowed.includes(e.key)) {
                if (e.key === 'Backspace' && !box.value && index > 0) {
                    boxes[index - 1].focus();
                    boxes[index - 1].value = '';
                    syncHidden();
                }
                return;
            }
            if (!/^\d$/.test(e.key)) {
                e.preventDefault();
            }
        });

        box.addEventListener('input', function () {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            syncHidden();

            // Auto focus ke kotak berikutnya
            if (box.value && index < boxes.length - 1) {
                boxes[index + 1].focus();
            }

            // Auto submit saat 6 digit lengkap
            if (hiddenInput.value.length === 6) {
                document.getElementById('otp-form').submit();
            }
        });

        // Handle paste "123456"
        box.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            if (!pasted) return;
            [...pasted].slice(0, 6).forEach((char, i) => {
                if (boxes[i]) boxes[i].value = char;
            });
            syncHidden();
            const nextEmpty = Array.from(boxes).findIndex(b => !b.value);
            const focusTarget = nextEmpty !== -1 ? nextEmpty : 5;
            boxes[focusTarget].focus();
            if (hiddenInput.value.length === 6) {
                document.getElementById('otp-form').submit();
            }
        });

        box.addEventListener('focus', () => box.select());
    });

    // ── Countdown Resend ─────────────────────────────────────────────
    const btnResend      = document.getElementById('btn-resend');
    const countdownText  = document.getElementById('countdown-text');
    const countdownTimer = document.getElementById('countdown-timer');
    const cooldown       = 60;

    // otp_sent_at dikirim dari controller via session (unix timestamp)
    const lastSentAt = {{ session('otp_sent_at') ? (int) session('otp_sent_at') : 'null' }};

    function startCountdown(secondsLeft) {
        if (secondsLeft <= 0) { enableResend(); return; }

        btnResend.disabled = true;
        countdownTimer.textContent = secondsLeft;
        countdownText.classList.remove('hidden');

        const interval = setInterval(() => {
            secondsLeft--;
            countdownTimer.textContent = secondsLeft;
            if (secondsLeft <= 0) {
                clearInterval(interval);
                enableResend();
            }
        }, 1000);
    }

    function enableResend() {
        btnResend.disabled = false;
        countdownText.classList.add('hidden');
    }

    // Hitung sisa waktu dari timestamp session
    if (lastSentAt !== null) {
        const elapsed   = Math.floor(Date.now() / 1000) - lastSentAt;
        const remaining = cooldown - elapsed;
        startCountdown(remaining > 0 ? remaining : 0);
    }

    // Saat form resend di-submit → mulai countdown lagi
    document.getElementById('resend-form').addEventListener('submit', function () {
        startCountdown(cooldown);
    });
});
</script>
</body>
</html>