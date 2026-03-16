<x-guest-layout>
    <div class="min-h-screen bg-[#f8fafc] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-5xl w-full bg-white shadow-2xl rounded-[30px] overflow-hidden lg:grid lg:grid-cols-2 border border-gray-100">
            
            <div class="hidden lg:flex flex-col justify-center items-center bg-green-600 p-12 text-center relative">
                <div class="relative z-10">
                    <span class="text-[120px] block mb-4">🌱</span>
                    <h2 class="text-4xl font-extrabold text-white mb-4">Gabung EcoDrop</h2>
                    <p class="text-green-100 text-lg font-medium">Mulailah langkah kecilmu untuk bumi yang lebih bersih dan hijau hari ini.</p>
                </div>
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-green-500 rounded-full opacity-20"></div>
            </div>

            <div class="p-8 sm:p-12 lg:p-16">
                <div class="mb-10 lg:text-left text-center">
                    <h1 class="text-3xl font-extrabold text-gray-900">Buat Akun Baru</h1>
                    <p class="text-gray-500 mt-2 font-medium">Lengkapi data diri untuk mulai setor sampah.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Nama Lengkap</label>
                        <input id="name" class="block w-full px-5 py-4 bg-gray-50 border-transparent focus:bg-white focus:border-green-500 focus:ring-green-500 rounded-2xl transition duration-200" type="text" name="name" :value="old('name')" required autofocus placeholder="Contoh: Yoga Habil" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Alamat Email</label>
                        <input id="email" class="block w-full px-5 py-4 bg-gray-50 border-transparent focus:bg-white focus:border-green-500 focus:ring-green-500 rounded-2xl transition duration-200" type="email" name="email" :value="old('email')" required placeholder="nama@email.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Password</label>
                            <input id="password" class="block w-full px-5 py-4 bg-gray-50 border-transparent focus:bg-white focus:border-green-500 focus:ring-green-500 rounded-2xl transition duration-200" type="password" name="password" required placeholder="••••••••" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Konfirmasi</label>
                            <input id="password_confirmation" class="block w-full px-5 py-4 bg-gray-50 border-transparent focus:bg-white focus:border-green-500 focus:ring-green-500 rounded-2xl transition duration-200" type="password" name="password_confirmation" required placeholder="••••••••" />
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />

                    <div class="pt-4">
                        <button type="submit" class="w-full py-4 bg-green-600 hover:bg-green-700 text-white font-extrabold rounded-2xl shadow-lg shadow-green-200 transition duration-200">
                            Daftar Sekarang
                        </button>
                    </div>
                </form>

                <p class="mt-8 text-center text-sm font-medium text-gray-600">
                    Sudah punya akun? <a href="{{ route('login') }}" class="text-green-600 font-bold hover:underline">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>