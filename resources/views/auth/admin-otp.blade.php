<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP Admin | EcoDrop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0f172a]">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 relative overflow-hidden">

        {{-- Background blobs --}}
        <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-900 rounded-full filter blur-3xl opacity-30"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-indigo-900 rounded-full filter blur-3xl opacity-30"></div>

        <div class="max-w-5xl w-full bg-[#1e293b] shadow-2xl rounded-[30px] overflow-hidden lg:grid lg:grid-cols-2 border border-slate-700 relative z-10">

            {{-- Left Panel --}}
            <div class="hidden lg:flex flex-col justify-center items-center bg-gradient-to-br from-blue-700 to-indigo-800 p-12 text-center relative">
                <div class="relative z-10">
                    <span class="text-[120px] block mb-6 leading-none">📧</span>
                    <h2 class="text-4xl font-extrabold text-white tracking-tight mb-4">Cek Email Kamu!</h2>
                    <p class="text-blue-100 text-lg font-medium leading-relaxed max-w-sm mx-auto">
                        Kode verifikasi 6 digit telah dikirim ke email admin kamu.
                    </p>

                    {{-- Info cards --}}
                    <div class="mt-8 space-y-3 text-left">
                        @foreach([
                            ['📬', 'Cek folder Inbox atau Spam'],
                            ['⏱️', 'Kode berlaku selama 10 menit'],
                            ['🔐', 'Jangan bagikan kode ke siapapun'],
                        ] as $item)
                        <div class="flex items-center gap-3 bg-white/10 rounded-2xl px-4 py-3">
                            <span class="text-lg">{{ $item[0] }}</span>
                            <p class="text-white text-sm font-semibold">{{ $item[1] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="absolute inset-0 w-full h-full opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>
            </div>

            {{-- Right Panel --}}
            <div class="p-8 sm:p-12 lg:p-16 flex flex-col justify-center">

                {{-- Mobile icon --}}
                <div class="lg:hidden flex justify-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-700 rounded-2xl shadow-lg">
                        <span class="text-3xl">📧</span>
                    </div>
                </div>

                <div class="mb-10 text-center lg:text-left">
                    <h1 class="text-3xl font-extrabold text-white tracking-tight mb-2">Verifikasi OTP</h1>
                    <p class="text-slate-400 font-medium text-sm leading-relaxed">
                        Masukkan 6 digit kode yang dikirim ke<br>
                        @if(session('admin_otp_email'))
                            <span class="font-bold text-slate-200">{{ session('admin_otp_email') }}</span>
                        @else
                            <span class="font-bold text-slate-200">email admin kamu</span>
                        @endif
                    </p>
                </div>

                {{-- Error: session('error') --}}
                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-900/50 border-l-4 border-red-500 rounded-r-2xl">
                        <p class="text-sm font-bold text-red-300">❌ {{ session('error') }}</p>
                    </div>
                @endif

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-900/50 border-l-4 border-red-500 rounded-r-2xl">
                        <p class="text-sm font-bold text-red-300">❌ {{ $errors->first() }}</p>
                    </div>
                @endif

                {{-- Success: session('status') --}}
                @if (session('status'))
                    <div class="mb-6 p-4 bg-blue-900/50 border-l-4 border-blue-500 rounded-r-2xl">
                        <p class="text-sm font-bold text-blue-300">✅ {{ session('status') }}</p>
                    </div>
                @endif

                {{-- OTP Form --}}
                <form method="POST" action="{{ route('admin.otp.verify') }}" id="otp-form" class="space-y-6">
                    @csrf

                    {{-- 6 kotak OTP --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-4 ml-1">Kode Verifikasi</label>
                        <div class="flex items-center justify-between gap-2">
                            @for ($i = 0; $i < 6; $i++)
                            <input
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                pattern="[0-9]"
                                data-index="{{ $i }}"
                                class="otp-box w-full aspect-square max-w-[56px] text-center text-2xl font-black bg-slate-800 border-2 border-slate-600 text-white rounded-2xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 outline-none transition duration-200"
                                autocomplete="off"
                            />
                            @endfor
                        </div>
                        <input type="hidden" name="otp" id="otp-hidden" />
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                        class="w-full py-4 bg-blue-700 hover:bg-blue-600 text-white font-extrabold rounded-2xl shadow-lg shadow-blue-900/50 transition duration-300 transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        <span>🔓</span>
                        Verifikasi & Masuk
                    </button>
                </form>

                {{-- Divider --}}
                <div class="my-6 flex items-center gap-4">
                    <div class="flex-1 h-px bg-slate-700"></div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">atau</p>
                    <div class="flex-1 h-px bg-slate-700"></div>
                </div>

                {{-- Resend --}}
                <div class="text-center">
                    <p class="text-slate-500 text-sm font-medium mb-3">Tidak menerima kode?</p>

                    <form method="POST" action="{{ route('admin.otp.resend') }}" id="resend-form">
                        @csrf
                        <button type="submit" id="btn-resend"
                            class="w-full py-3.5 border-2 border-slate-600 text-slate-300 rounded-2xl font-bold text-sm hover:border-blue-500 hover:text-blue-400 hover:bg-blue-900/20 transition duration-300 flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:border-slate-600 disabled:hover:text-slate-300 disabled:hover:bg-transparent">
                            <span>🔄</span>
                            <span id="resend-text">Kirim Ulang Kode</span>
                        </button>
                    </form>

                    <p id="countdown-text" class="text-xs text-slate-500 font-medium mt-2 hidden">
                        Kirim ulang dalam <span id="countdown-timer" class="font-black text-blue-400">60</span> detik
                    </p>
                </div>

                {{-- Back to login --}}
                <div class="mt-8 pt-6 border-t border-slate-700 text-center">
                    <p class="text-slate-600 text-xs">
                        <a href="{{ route('admin.login') }}" class="text-slate-500 hover:text-slate-300 font-bold transition">
                            ← Kembali ke Login Admin
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── OTP Box Logic ─────────────────────────────────────────────────
    const boxes       = document.querySelectorAll('.otp-box');
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
            if (!/^\d$/.test(e.key)) e.preventDefault();
        });

        box.addEventListener('input', function () {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            syncHidden();
            if (box.value && index < boxes.length - 1) boxes[index + 1].focus();
            if (hiddenInput.value.length === 6) document.getElementById('otp-form').submit();
        });

        box.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            if (!pasted) return;
            [...pasted].slice(0, 6).forEach((char, i) => { if (boxes[i]) boxes[i].value = char; });
            syncHidden();
            const nextEmpty = Array.from(boxes).findIndex(b => !b.value);
            boxes[nextEmpty !== -1 ? nextEmpty : 5].focus();
            if (hiddenInput.value.length === 6) document.getElementById('otp-form').submit();
        });

        box.addEventListener('focus', () => box.select());
    });

    // ── Countdown Resend ─────────────────────────────────────────────
    const btnResend      = document.getElementById('btn-resend');
    const countdownText  = document.getElementById('countdown-text');
    const countdownTimer = document.getElementById('countdown-timer');
    const cooldown       = 60;

    const lastSentAt = {{ session('admin_otp_sent_at') ? (int) session('admin_otp_sent_at') : 'null' }};

    function startCountdown(secondsLeft) {
        if (secondsLeft <= 0) { enableResend(); return; }
        btnResend.disabled = true;
        countdownTimer.textContent = secondsLeft;
        countdownText.classList.remove('hidden');
        const interval = setInterval(() => {
            secondsLeft--;
            countdownTimer.textContent = secondsLeft;
            if (secondsLeft <= 0) { clearInterval(interval); enableResend(); }
        }, 1000);
    }

    function enableResend() {
        btnResend.disabled = false;
        countdownText.classList.add('hidden');
    }

    if (lastSentAt !== null) {
        const elapsed   = Math.floor(Date.now() / 1000) - lastSentAt;
        const remaining = cooldown - elapsed;
        startCountdown(remaining > 0 ? remaining : 0);
    }

    document.getElementById('resend-form').addEventListener('submit', () => startCountdown(cooldown));
});
</script>
</body>
</html>