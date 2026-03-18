<x-guest-layout>
    <div class="min-h-screen bg-[#0f172a] flex items-center justify-center py-12 px-4 relative overflow-hidden">

        <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-900 rounded-full filter blur-3xl opacity-30"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-indigo-900 rounded-full filter blur-3xl opacity-30"></div>

        <div class="max-w-5xl w-full bg-[#1e293b] shadow-2xl rounded-[30px] overflow-hidden lg:grid lg:grid-cols-2 border border-slate-700 relative z-10">

            {{-- Left Panel --}}
            <div class="hidden lg:flex flex-col justify-center items-center bg-gradient-to-br from-blue-700 to-indigo-800 p-12 text-center relative">
                <div class="relative z-10">
                    <span class="text-[120px] block mb-6 leading-none">📋</span>
                    <h2 class="text-4xl font-extrabold text-white tracking-tight mb-4">Daftar Admin</h2>
                    <p class="text-blue-100 text-lg font-medium leading-relaxed max-w-sm mx-auto">
                        Daftarkan dirimu sebagai pengelola EcoDrop. Akun akan aktif setelah diverifikasi Super Admin.
                    </p>
                    <div class="mt-8 p-4 bg-white/10 rounded-2xl text-left text-sm text-blue-100 space-y-2">
                        <p class="flex items-center gap-2"><span>1️⃣</span> Isi form pendaftaran</p>
                        <p class="flex items-center gap-2"><span>2️⃣</span> Tunggu verifikasi Super Admin</p>
                        <p class="flex items-center gap-2"><span>3️⃣</span> Login & kelola platform</p>
                    </div>
                </div>
                <div class="absolute inset-0 w-full h-full opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>
            </div>

            {{-- Right Panel / Form --}}
            <div class="p-8 sm:p-12 lg:p-16 flex flex-col justify-center">
                <div class="mb-8 text-center lg:text-left">
                    <h1 class="text-3xl font-extrabold text-white tracking-tight mb-2">Daftar Akun Admin</h1>
                    <p class="text-slate-400 font-medium">Akun aktif setelah diverifikasi Super Admin</p>
                </div>

                @if (session('status'))
                    <div class="mb-6 p-4 bg-blue-900/50 border-l-4 border-blue-500 rounded-r-2xl">
                        <p class="text-sm font-bold text-blue-300">{{ session('status') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 ml-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="John Doe"
                            class="block w-full px-5 py-4 bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500 rounded-2xl transition duration-200" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 ml-1">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            placeholder="admin@ecodrop.com"
                            class="block w-full px-5 py-4 bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500 rounded-2xl transition duration-200" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 ml-1">Password</label>
                        <input type="password" name="password" required placeholder="Minimal 8 karakter"
                            class="block w-full px-5 py-4 bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500 rounded-2xl transition duration-200" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 ml-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required placeholder="Ulangi password"
                            class="block w-full px-5 py-4 bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500 rounded-2xl transition duration-200" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-blue-700 hover:bg-blue-600 text-white font-extrabold rounded-2xl shadow-lg shadow-blue-900/50 transition duration-300 transform hover:-translate-y-1 mt-2">
                        Daftar Sekarang
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-700 text-center">
                    <p class="text-slate-500 font-medium text-sm">
                        Sudah punya akun admin?
                        <a href="{{ route('admin.login') }}" class="text-blue-400 font-bold hover:underline underline-offset-4">
                            Login di sini
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>