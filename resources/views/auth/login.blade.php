<x-guest-layout>
    <div class="min-h-screen bg-[#f8fafc] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        
        <div class="hidden lg:block absolute -top-20 -left-20 w-80 h-80 bg-green-100 rounded-full filter blur-3xl opacity-60"></div>
        <div class="hidden lg:block absolute -bottom-20 -right-20 w-80 h-80 bg-emerald-100 rounded-full filter blur-3xl opacity-60"></div>

        <div class="max-w-5xl w-full bg-white shadow-2xl rounded-[30px] overflow-hidden lg:grid lg:grid-cols-2 border border-gray-100 relative z-10">
            
            <div class="hidden lg:flex flex-col justify-center items-center bg-green-600 p-12 text-center relative">
                <div class="relative z-10 animate__animated animate__fadeIn">
                    <span class="text-[140px] block mb-6 leading-none">♻️</span>
                    <h2 class="text-4xl font-extrabold text-white tracking-tight mb-4">EcoDrop</h2>
                    <p class="text-green-100 text-lg font-medium leading-relaxed max-w-sm mx-auto">
                        Ubah sampah jadi kebaikan. Masuk dan teruskan kontribusimu untuk bumi yang lebih hijau.
                    </p>
                </div>
                <div class="absolute inset-0 w-full h-full opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>
            </div>

            <div class="p-8 sm:p-12 lg:p-16 flex flex-col justify-center">
                
                <div class="mb-10 text-center lg:text-left">
                    <div class="lg:hidden inline-flex items-center justify-center w-16 h-16 bg-green-600 rounded-2xl shadow-lg mb-4">
                        <span class="text-3xl">♻️</span>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-950 tracking-tight mb-2">Login Dashboard</h1>
                    <p class="text-gray-500 font-medium">Selamat datang kembali, Eco-Warrior!</p>
                </div>

                @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-2xl animate__animated animate__fadeInDown">
                        <div class="flex items-center">
                            <span class="text-green-500 mr-3">✅</span>
                            <p class="text-sm font-bold text-green-800">{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Alamat Email</label>
                        <input id="email" class="block w-full px-5 py-4 bg-gray-50 border-gray-200 focus:bg-white focus:border-green-500 focus:ring-green-500 rounded-2xl transition duration-200" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <div class="flex justify-between mb-2 ml-1">
                            <label class="block text-sm font-bold text-gray-700">Password</label>
                            @if (Route::has('password.request'))
                                <a class="text-xs font-bold text-green-600 hover:text-green-700" href="{{ route('password.request') }}">Lupa Password?</a>
                            @endif
                        </div>
                        <input id="password" class="block w-full px-5 py-4 bg-gray-50 border-gray-200 focus:bg-white focus:border-green-500 focus:ring-green-500 rounded-2xl transition duration-200" type="password" name="password" required placeholder="••••••••" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center ml-1">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500" name="remember">
                        <span class="ms-2 text-sm font-medium text-gray-600 italic">Ingat perangkat ini</span>
                    </div>

                    <button type="submit" class="w-full py-4 bg-green-600 hover:bg-green-700 text-white font-extrabold rounded-2xl shadow-lg shadow-green-200 transition duration-300 transform hover:-translate-y-1">
                        Masuk Sekarang
                    </button>
                </form>

                <div class="mt-10 pt-8 border-t border-gray-100 text-center">
                    <p class="text-gray-600 font-medium">
                        Belum punya akun? 
                        <a href="{{ route('register') }}" class="text-green-600 font-bold hover:underline underline-offset-4">Daftar Gratis</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>