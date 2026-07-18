<section class="space-y-5">
    <p class="text-sm text-gray-500 leading-relaxed">
        Setelah akun dihapus, semua data dan informasi akan hilang permanen dan tidak dapat dipulihkan kembali.
    </p>

    <div x-data="{ showModal: false }">
        <button @click="showModal = true"
            class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold text-xs shadow-sm active:scale-[0.98] transition">
            Hapus Akun Saya
        </button>

        {{-- Confirmation Modal --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[500] flex items-center justify-center bg-gray-900/50 p-4"
             style="display: none;">
            <div @click.away="showModal = false"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-red-100 overflow-hidden">
                <div class="bg-red-50 px-6 py-5 border-b border-red-100 flex items-center gap-4">
                    <div class="w-10 h-10 bg-red-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.193 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-red-700">Konfirmasi Hapus Akun</h3>
                        <p class="text-xs text-red-400">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>

                <form method="post" action="{{ route('profile.destroy') }}" class="p-6 space-y-5">
                    @csrf
                    @method('delete')

                    <p class="text-sm text-gray-500 leading-relaxed">
                        Seluruh data, riwayat setoran, dan poin kamu akan dihapus permanen.
                        Masukkan password untuk mengkonfirmasi penghapusan.
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wide">Password <span class="text-red-400">*</span></label>
                        <input id="delete_password" name="password" type="password"
                               placeholder="Masukkan password kamu"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none bg-gray-50 text-sm font-semibold text-gray-700 transition">
                        @error('password', 'userDeletion')
                            <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <button type="button" @click="showModal = false"
                            class="flex-1 py-2.5 text-gray-500 hover:bg-gray-100 rounded-xl font-bold text-xs transition active:scale-[0.98]">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold text-xs shadow-sm active:scale-[0.98] transition">
                            Ya, Hapus Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
