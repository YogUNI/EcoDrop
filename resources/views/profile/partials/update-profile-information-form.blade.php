<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wide">Nama Lengkap <span class="text-red-400">*</span></label>
            <input id="name" name="name" type="text" autocomplete="name" required autofocus
                   value="{{ old('name', $user->name) }}"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
            @error('name')
                <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wide">Email <span class="text-red-400">*</span></label>
            <input id="email" name="email" type="email" autocomplete="username" required
                   value="{{ old('email', $user->email) }}"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
            @error('email')
                <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 p-3 bg-yellow-50 border border-yellow-100 rounded-xl">
                    <p class="text-xs text-yellow-700 font-semibold">
                        Email belum diverifikasi.
                        <button form="send-verification" class="underline font-bold hover:text-yellow-900 transition">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="text-xs text-green-600 font-bold mt-1">Link verifikasi telah dikirim ke email kamu!</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit"
                class="px-6 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-xs shadow-sm active:scale-[0.98] transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</section>
