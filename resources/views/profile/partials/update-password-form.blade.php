<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wide">Password Saat Ini <span class="text-red-400">*</span></label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   placeholder="Masukkan password lama"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
            @error('current_password', 'updatePassword')
                <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wide">Password Baru <span class="text-red-400">*</span></label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   placeholder="Minimal 8 karakter"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
            @error('password', 'updatePassword')
                <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wide">Konfirmasi Password Baru <span class="text-red-400">*</span></label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   placeholder="Ulangi password baru"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
            @error('password_confirmation', 'updatePassword')
                <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button type="submit"
                class="px-6 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold text-xs shadow-sm active:scale-[0.98] transition">
                Perbarui Password
            </button>
        </div>
    </form>
</section>
