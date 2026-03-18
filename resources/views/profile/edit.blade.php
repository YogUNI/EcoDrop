<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center shadow-lg">
                <span class="text-2xl">⚙️</span>
            </div>
            <div>
                <h2 class="font-extrabold text-3xl bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                    Profile & Pengaturan
                </h2>
                <p class="text-sm text-gray-500 mt-1">Kelola informasi akun kamu</p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-blue-50 py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Success Alerts --}}
            @if (session('status') === 'profile-updated')
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="p-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-2xl shadow-xl flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">✅</span>
                        <span class="font-bold">Profil berhasil diperbarui!</span>
                    </div>
                    <button @click="show = false" class="hover:bg-white/20 p-2 rounded-lg">✕</button>
                </div>
            @endif

            @if (session('status') === 'photo-updated')
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="p-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-2xl shadow-xl flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📸</span>
                        <span class="font-bold">Foto profil berhasil diperbarui!</span>
                    </div>
                    <button @click="show = false" class="hover:bg-white/20 p-2 rounded-lg">✕</button>
                </div>
            @endif

            @if (session('status') === 'photo-deleted')
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="p-4 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-2xl shadow-xl flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🗑️</span>
                        <span class="font-bold">Foto profil berhasil dihapus!</span>
                    </div>
                    <button @click="show = false" class="hover:bg-white/20 p-2 rounded-lg">✕</button>
                </div>
            @endif

            @if (session('status') === 'password-updated')
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="p-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl shadow-xl flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🔐</span>
                        <span class="font-bold">Password berhasil diperbarui!</span>
                    </div>
                    <button @click="show = false" class="hover:bg-white/20 p-2 rounded-lg">✕</button>
                </div>
            @endif

            {{-- FOTO PROFILE --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg border border-gray-100/50 overflow-hidden">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-xl font-black text-gray-900">📸 Foto Profil</h3>
                    <p class="text-sm text-gray-500 mt-1">Upload foto profil kamu (maks. 2MB)</p>
                </div>
                <div class="p-8">
                    <div x-data="{ preview: null }" class="flex flex-col sm:flex-row items-center gap-8">

                        {{-- Preview Foto --}}
                        <div class="relative flex-shrink-0">
                            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-xl ring-4 ring-green-200">
                                <img x-show="preview" :src="preview"
                                     class="w-full h-full object-cover" alt="Preview" style="display:none;">
                                <img x-show="!preview"
                                     src="{{ $user->getPhotoUrl() }}"
                                     class="w-full h-full object-cover" alt="Foto Profil">
                            </div>
                            {{-- Role badge --}}
                            <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 whitespace-nowrap">
                                @if($user->role === 'super_admin')
                                    <span class="bg-amber-500 text-white text-xs font-black px-3 py-1 rounded-full shadow">⭐ Owner</span>
                                @elseif($user->role === 'admin')
                                    <span class="bg-blue-500 text-white text-xs font-black px-3 py-1 rounded-full shadow">👑 Admin</span>
                                @else
                                    <span class="bg-green-500 text-white text-xs font-black px-3 py-1 rounded-full shadow">🌿 User</span>
                                @endif
                            </div>
                        </div>

                        {{-- Upload Form --}}
                        <div class="flex-1 w-full">
                            <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="space-y-4">
                                @csrf

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Foto Baru</label>
                                    <input type="file" name="profile_photo" accept="image/*" required
                                           @change="preview = URL.createObjectURL($event.target.files[0])"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-green-100 file:text-green-700 hover:file:bg-green-200 transition duration-300 border-2 border-dashed border-gray-300 rounded-xl p-3 cursor-pointer hover:border-green-400" />
                                    @error('profile_photo')
                                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-400 mt-2">Format: JPG, PNG, WEBP. Maks: 2MB</p>
                                </div>

                                <div class="flex gap-3">
                                    <button type="submit"
                                        class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold shadow hover:shadow-lg transition duration-300 hover:scale-105">
                                        📸 Upload Foto
                                    </button>

                                    @if($user->profile_photo)
                                        <form method="POST" action="{{ route('profile.photo.delete') }}" class="inline"
                                              onsubmit="return confirm('Yakin hapus foto profil?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-6 py-3 bg-red-50 text-red-600 border-2 border-red-200 rounded-xl font-bold hover:bg-red-100 transition duration-300">
                                                🗑️ Hapus Foto
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- INFO PROFIL --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg border border-gray-100/50 overflow-hidden">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h3 class="text-xl font-black text-gray-900">👤 Informasi Profil</h3>
                    <p class="text-sm text-gray-500 mt-1">Perbarui nama dan email akun kamu</p>
                </div>
                <div class="p-8">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- GANTI PASSWORD --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg border border-gray-100/50 overflow-hidden">
                <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="text-xl font-black text-gray-900">🔐 Keamanan & Password</h3>
                    <p class="text-sm text-gray-500 mt-1">Pastikan akun kamu menggunakan password yang kuat</p>
                </div>
                <div class="p-8">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- HAPUS AKUN --}}
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg border border-red-100/50 overflow-hidden">
                <div class="p-8 border-b border-red-100 bg-gradient-to-r from-red-50 to-pink-50">
                    <h3 class="text-xl font-black text-red-700">⚠️ Hapus Akun</h3>
                    <p class="text-sm text-red-500 mt-1">Tindakan ini tidak dapat dibatalkan</p>
                </div>
                <div class="p-8">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>