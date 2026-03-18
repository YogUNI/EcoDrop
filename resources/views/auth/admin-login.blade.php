<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | EcoDrop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0f172a]">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 relative overflow-hidden">

        <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-900 rounded-full filter blur-3xl opacity-30"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-indigo-900 rounded-full filter blur-3xl opacity-30"></div>

        <div class="max-w-5xl w-full bg-[#1e293b] shadow-2xl rounded-[30px] overflow-hidden lg:grid lg:grid-cols-2 border border-slate-700 relative z-10">

            {{-- Left Panel --}}
            <div class="hidden lg:flex flex-col justify-center items-center bg-gradient-to-br from-blue-700 to-indigo-800 p-12 text-center relative">
                <div class="relative z-10">
                    <span class="text-[120px] block mb-6 leading-none">🛡️</span>
                    <h2 class="text-4xl font-extrabold text-white tracking-tight mb-4">Admin Panel</h2>
                    <p class="text-blue-100 text-lg font-medium leading-relaxed max-w-sm mx-auto">
                        Area khusus tim pengelola EcoDrop. Akses terbatas dan terverifikasi.
                    </p>
                    <div class="mt-8 inline-flex items-center gap-2 bg-white/10 rounded-full px-5 py-2 text-blue-100 text-sm font-semibold">
                        <span>🔒</span> Secured Access Only
                    </div>
                </div>
                <div class="absolute inset-0 w-full h-full opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>
            </div>

            {{-- Right Panel --}}
            <div class="p-8 sm:p-12 lg:p-16 flex flex-col justify-center">
                <div class="mb-10 text-center lg:text-left">
                    <div class="lg:hidden inline-flex items-center justify-center w-16 h-16 bg-blue-700 rounded-2xl shadow-lg mb-4">
                        <span class="text-3xl">🛡️</span>
                    </div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight mb-2">Login Admin</h1>
                    <p class="text-slate-400 font-medium">Masuk ke panel pengelola EcoDrop</p>
                </div>

                @if (session('status'))
                    <div class="mb-6 p-4 bg-blue-900/50 border-l-4 border-blue-500 rounded-r-2xl">
                        <p class="text-sm font-bold text-blue-300">{{ session('status') }}</p>
                    </div>
                @endif

                {{-- Error Message --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-900/50 border-l-4 border-red-500 rounded-r-2xl">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm font-bold text-red-300">❌ {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 ml-1">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="admin@ecodrop.com"
                            class="block w-full px-5 py-4 bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500 rounded-2xl transition duration-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 ml-1">Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="block w-full px-5 py-4 bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500 rounded-2xl transition duration-200" />
                    </div>

                    <div class="flex items-center ml-1">
                        <input id="remember_me" type="checkbox" name="remember"
                            class="rounded border-slate-600 bg-slate-800 text-blue-600 focus:ring-blue-500">
                        <span class="ms-2 text-sm font-medium text-slate-400">Ingat perangkat ini</span>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-blue-700 hover:bg-blue-600 text-white font-extrabold rounded-2xl shadow-lg shadow-blue-900/50 transition duration-300 transform hover:-translate-y-1">
                        Masuk ke Panel Admin
                    </button>
                </form>

                <div class="mt-10 pt-8 border-t border-slate-700 text-center">
                    <p class="text-slate-500 font-medium text-sm">
                        Belum punya akun admin?
                        <a href="{{ route('admin.register') }}" class="text-blue-400 font-bold hover:underline underline-offset-4">
                            Daftar sebagai Admin
                        </a>
                    </p>
                    <p class="text-slate-600 text-xs mt-3">
                        Bukan admin? <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-300">Login sebagai User</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>