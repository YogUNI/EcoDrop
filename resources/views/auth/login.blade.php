<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | EcoDrop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, rgba(240,253,244,0.6) 50%, #ecfdf5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            margin: 0;
        }

        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.22;
            pointer-events: none;
            z-index: 0;
        }
        .blob-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #6ee7b7, #059669);
            top: -150px; left: -150px;
        }
        .blob-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #a7f3d0, #10b981);
            bottom: -100px; right: -100px;
        }

        .auth-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 64rem;
            border-radius: 32px;
            box-shadow: 0 32px 64px -12px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr;
            background: #fff;
        }
        @media (min-width: 1024px) {
            .auth-card { grid-template-columns: 1fr 1fr; }
        }

        .left-panel {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            background: linear-gradient(145deg, #065f46 0%, #047857 45%, #0f766e 100%);
            position: relative;
            overflow: hidden;
        }
        @media (min-width: 1024px) {
            .left-panel { display: flex; }
        }
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.07) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }
        .left-inner { position: relative; z-index: 1; }

        .logo-wrap { display: flex; align-items: center; gap: 0.625rem; margin-bottom: 2.5rem; }
        .logo-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-text { font-size: 1.375rem; font-weight: 900; color: #fff; letter-spacing: -0.5px; }

        .stat-pill {
            display: flex; align-items: center; gap: 0.625rem;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 999px;
            padding: 0.625rem 1.125rem;
            margin-bottom: 0.75rem;
            transition: background 0.2s;
            cursor: default;
        }
        .stat-pill:hover { background: rgba(255,255,255,0.16); }
        .stat-dot { width: 8px; height: 8px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; }
        .stat-pill span { font-size: 0.8125rem; font-weight: 600; color: rgba(255,255,255,0.92); }

        .right-panel {
            padding: 2.5rem 1.75rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fff;
        }
        @media (min-width: 640px) { .right-panel { padding: 3.5rem; } }

        .input-group { margin-bottom: 1.25rem; }
        .input-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 0.5rem;
            letter-spacing: 0.01em;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            display: flex; align-items: center;
        }
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.875rem;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 16px;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #111827;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .form-input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
            background: #fff;
        }
        .form-input::placeholder { color: #9ca3af; font-weight: 400; }
        .form-input-pr { padding-right: 3rem; }

        .toggle-btn {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #6b7280; padding: 2px;
            display: flex; align-items: center;
            transition: color 0.2s;
        }
        .toggle-btn:hover { color: #10b981; }

        .checkbox-wrap { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
        .checkbox-wrap input[type="checkbox"] {
            width: 18px; height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 6px; cursor: pointer;
            accent-color: #10b981;
        }
        .checkbox-label { font-size: 0.875rem; font-weight: 500; color: #6b7280; cursor: pointer; }

        .btn-primary {
            width: 100%; padding: 1rem;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #fff; font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem; font-weight: 900;
            border: none; border-radius: 16px; cursor: pointer;
            box-shadow: 0 8px 24px rgba(16,185,129,0.28);
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
            letter-spacing: 0.01em;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(16,185,129,0.38);
        }
        .btn-primary:active { transform: translateY(0); }

        .divider { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; }
        .divider-line { flex: 1; height: 1px; background: #e5e7eb; }
        .divider-text { font-size: 0.8125rem; font-weight: 600; color: #9ca3af; white-space: nowrap; }

        .btn-secondary {
            display: block; width: 100%;
            padding: 0.875rem 1rem;
            background: transparent;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            text-align: center;
            font-size: 0.9375rem; font-weight: 700;
            color: #374151; text-decoration: none;
            transition: border-color 0.2s, background 0.2s, color 0.2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .btn-secondary:hover { border-color: #10b981; background: #f0fdf4; color: #059669; }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 14px;
            font-size: 0.875rem; font-weight: 600;
            display: flex; align-items: flex-start; gap: 0.625rem;
            margin-bottom: 1.25rem;
        }
        .alert-success { background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #166534; }
        .alert-error { background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; }
        .alert-icon { flex-shrink: 0; margin-top: 1px; }

        .mobile-logo {
            display: flex; align-items: center; gap: 0.5rem;
            margin-bottom: 2rem;
        }
        .mobile-logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .mobile-logo-text { font-size: 1.25rem; font-weight: 900; color: #065f46; letter-spacing: -0.5px; }
        @media (min-width: 1024px) { .mobile-logo { display: none; } }

        .link-green { color: #059669; font-weight: 700; text-decoration: none; }
        .link-green:hover { text-decoration: underline; }
        .link-blue { color: #3b82f6; font-weight: 700; text-decoration: none; }
        .link-blue:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="auth-card">

        {{-- ===== LEFT PANEL ===== --}}
        <div class="left-panel">
            <div class="left-inner">
                <div class="logo-wrap">
                    <div class="logo-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <polyline points="1 20 1 14 7 14"></polyline>
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                        </svg>
                    </div>
                    <span class="logo-text">EcoDrop</span>
                </div>

                <h2 style="font-size:2.25rem;font-weight:900;color:#fff;line-height:1.15;margin:0 0 0.75rem;letter-spacing:-1px;">
                    Selamat Datang<br>Kembali! 👋
                </h2>
                <p style="font-size:0.9375rem;font-weight:500;color:#a7f3d0;margin:0 0 2rem;line-height:1.65;">
                    Masuk dan lanjutkan perjalananmu dalam mengelola sampah secara cerdas &amp; berkelanjutan.
                </p>

                <div>
                    <div class="stat-pill">
                        <div class="stat-dot"></div>
                        <span>1,200+ Pengguna Aktif</span>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-dot"></div>
                        <span>1,240 Kg Sampah Terkumpul</span>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-dot"></div>
                        <span>Reward Transparan &amp; Terpercaya</span>
                    </div>
                </div>
            </div>

            <p style="position:relative;z-index:1;font-size:0.75rem;font-weight:500;color:#6ee7b7;margin:2rem 0 0;">
                © 2026 EcoDrop — YoHaTo Labs
            </p>
        </div>

        {{-- ===== RIGHT PANEL ===== --}}
        <div class="right-panel">

            {{-- Mobile Logo --}}
            <div class="mobile-logo">
                <div class="mobile-logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                </div>
                <span class="mobile-logo-text">EcoDrop</span>
            </div>

            <h1 style="font-size:1.875rem;font-weight:900;color:#111827;margin:0 0 0.375rem;letter-spacing:-0.5px;">
                Masuk ke Akun
            </h1>
            <p style="font-size:0.9375rem;font-weight:500;color:#6b7280;margin:0 0 1.75rem;">
                Belum punya akun? <a href="{{ route('register') }}" class="link-green">Daftar sekarang</a>
            </p>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="alert alert-success">
                    <span class="alert-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Error Alert --}}
            @if ($errors->any())
                <div class="alert alert-error">
                    <span class="alert-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- FORM --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="input-group">
                    <label class="input-label" for="email">Alamat Email</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="nama@email.com"
                            class="form-input"
                            autocomplete="email"
                        >
                    </div>
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                {{-- Password --}}
                <div class="input-group">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                        <label class="input-label" for="password" style="margin-bottom:0;">Kata Sandi</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="link-green" style="font-size:0.8125rem;">
                                Lupa kata sandi?
                            </a>
                        @endif
                    </div>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Masukkan kata sandi"
                            class="form-input form-input-pr"
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-btn" onclick="togglePassword()" id="eye-icon" aria-label="Tampilkan kata sandi">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                {{-- Remember Me --}}
                <div style="margin-bottom:1.5rem;">
                    <label class="checkbox-wrap" for="remember_me">
                        <input type="checkbox" id="remember_me" name="remember">
                        <span class="checkbox-label">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary">
                    Masuk Sekarang
                </button>
            </form>

            <div class="divider">
                <div class="divider-line"></div>
                <span class="divider-text">atau</span>
                <div class="divider-line"></div>
            </div>

            <a href="{{ route('register') }}" class="btn-secondary">
                Belum punya akun? Daftar Gratis
            </a>

            <p style="text-align:center;margin-top:1.25rem;font-size:0.8125rem;font-weight:500;color:#9ca3af;">
                Login sebagai admin?
                <a href="{{ route('admin.login') }}" class="link-blue">Portal Admin →</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const btn = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
            } else {
                input.type = 'password';
                btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle></svg>`;
            }
        }
    </script>
</body>
</html>