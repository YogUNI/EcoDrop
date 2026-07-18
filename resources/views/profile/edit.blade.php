<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-xl text-gray-900">Profil Saya</h2>
    </x-slot>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent py-8">
        <div class="max-w-lg mx-auto px-4 sm:px-6 space-y-5">

            {{-- Success Alerts --}}
            @foreach(['profile-updated' => 'Profil berhasil diperbarui!', 'photo-updated' => 'Foto profil berhasil diperbarui!', 'password-updated' => 'Password berhasil diperbarui!'] as $key => $msg)
                @if(session('status') === $key)
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="font-bold text-sm">{{ $msg }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 transition ml-3">✕</button>
                    </div>
                @endif
            @endforeach
            @if(session('status') === 'photo-deleted')
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex justify-between items-center">
                    <span class="font-bold text-sm">Foto profil berhasil dihapus!</span>
                    <button @click="show = false" class="text-red-600 hover:text-red-800 transition ml-3">✕</button>
                </div>
            @endif

            {{-- CARD 1: AVATAR --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div x-data="profilePhotoEditor(@js($user->getPhotoUrl()))" class="p-8 flex flex-col items-center">
                    <form x-ref="form" method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" @submit.prevent="submitCropped()" class="w-full flex flex-col items-center">
                        @csrf
                        <input x-ref="fileInput" type="file" name="profile_photo" accept="image/*" class="hidden" @change="selectFile($event)">
                        <input x-ref="croppedInput" type="hidden" name="cropped_photo">

                        <div class="relative mb-4">
                            <div
                                x-ref="frame"
                                class="relative w-32 h-32 rounded-full overflow-hidden bg-emerald-500 ring-2 ring-white ring-offset-2 ring-offset-white shadow-md select-none"
                            >
                                <img
                                    :src="preview || currentPhoto"
                                    alt="{{ $user->name }}"
                                    draggable="false"
                                    class="absolute inset-0 w-full h-full object-cover"
                                    :class="preview ? 'cursor-grab active:cursor-grabbing' : ''"
                                    :style="preview ? `transform: translate(${offsetX}px, ${offsetY}px) scale(${zoom}); transform-origin: center;` : ''"
                                    @mousedown.prevent="startDrag($event)"
                                    @mousemove.window="drag($event)"
                                    @mouseup.window="stopDrag()"
                                    @mouseleave.window="stopDrag()"
                                    @touchstart.prevent="startDrag($event.touches[0])"
                                    @touchmove.window="drag($event.touches[0])"
                                    @touchend.window="stopDrag()"
                                >
                            </div>

                            <button type="button" @click="$refs.fileInput.click()"
                                class="absolute bottom-0 right-0 w-9 h-9 bg-white border-2 border-gray-100 rounded-full flex items-center justify-center cursor-pointer hover:bg-emerald-50 hover:border-emerald-200 transition shadow-sm"
                                aria-label="Pilih foto profil">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                            </button>
                        </div>

                        <div x-show="preview" x-cloak class="w-full mb-4 space-y-3">
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <p class="text-xs font-bold text-emerald-700 truncate" x-text="fileName"></p>
                                    <button type="button" @click="clearSelection()" class="text-xs font-bold text-gray-400 hover:text-red-500 transition">Batal</button>
                                </div>

                                <label class="block text-xs font-bold text-gray-500 mb-2">Zoom foto</label>
                                <input type="range" min="1" max="3" step="0.01" x-model.number="zoom" class="w-full accent-emerald-500">

                                <div class="flex items-center justify-between mt-3">
                                    <button type="button" @click="resetCrop()" class="text-xs font-bold text-gray-500 hover:text-emerald-600 transition">Reset posisi</button>
                                    <p class="text-[11px] text-gray-400">Geser foto untuk atur posisi</p>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold text-sm shadow-sm active:scale-[0.98] transition"
                                :disabled="isSaving"
                                :class="isSaving ? 'opacity-70 cursor-wait' : ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Foto'"></span>
                            </button>
                        </div>

                        <button x-show="!preview" type="button" @click="$refs.fileInput.click()"
                            class="mb-4 text-xs font-bold text-emerald-600 hover:text-emerald-700 transition">
                            Ganti foto profil
                        </button>
                    </form>

                    <h3 class="text-xl font-black text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-400 mt-0.5">{{ $user->email }}</p>

                    {{-- Role badge --}}
                    <div class="mt-3">
                        @if($user->role === 'super_admin')
                            <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">⭐ Super Admin</span>
                        @elseif($user->role === 'admin')
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">👑 Admin</span>
                        @else
                            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full">🌿 User</span>
                        @endif
                    </div>

                    @if($user->profile_photo)
                        <form id="delete-photo-form" method="POST" action="{{ route('profile.photo.delete') }}"
                              onsubmit="return confirm('Yakin ingin menghapus foto profil?');" class="mt-3">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 font-semibold transition">
                                Hapus foto profil
                            </button>
                        </form>
                    @endif

                    @error('profile_photo')
                        <p class="text-red-500 text-xs mt-2 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- CARD 2: INFORMASI PROFIL --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Informasi Profil</h3>
                </div>
                <div class="p-6">
                    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                        @csrf @method('patch')

                        <div id="send-verification-wrapper">
                            <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-gray-500 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Nama Lengkap
                            </label>
                            <input id="name" name="name" type="text" autocomplete="name" required autofocus
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none bg-white text-sm text-gray-700 transition">
                            @error('name') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-gray-500 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Email
                            </label>
                            <input id="email" name="email" type="email" autocomplete="username" required
                                   value="{{ old('email', $user->email) }}"
                                   class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none bg-white text-sm text-gray-700 transition">
                            @error('email') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-2 p-3 bg-yellow-50 border border-yellow-100 rounded-xl">
                                    <p class="text-xs text-yellow-700 font-semibold">
                                        Email belum diverifikasi.
                                        <button form="send-verification" class="underline font-bold hover:text-yellow-900">Kirim ulang.</button>
                                    </p>
                                </div>
                            @endif
                        </div>

                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold text-sm shadow-sm active:scale-[0.98] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            {{-- CARD 3: UBAH PASSWORD --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Ubah Password</h3>
                </div>
                <div class="p-6">
                    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf @method('put')

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-gray-500 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Password Saat Ini
                            </label>
                            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                                   placeholder="Masukkan password saat ini"
                                   class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none bg-white text-sm text-gray-700 transition">
                            @error('current_password', 'updatePassword') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-gray-500 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Password Baru
                            </label>
                            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                                   placeholder="Minimal 8 karakter"
                                   class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none bg-white text-sm text-gray-700 transition">
                            @error('password', 'updatePassword') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-gray-500 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Konfirmasi Password Baru
                            </label>
                            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                                   placeholder="Masukkan ulang password baru"
                                   class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none bg-white text-sm text-gray-700 transition">
                            @error('password_confirmation', 'updatePassword') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold text-sm shadow-sm active:scale-[0.98] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Ubah Password
                        </button>
                    </form>
                </div>
            </div>

            {{-- CARD 4: HAPUS AKUN --}}
            <div class="bg-white rounded-3xl border border-red-50 shadow-sm overflow-hidden" x-data="{ showModal: false }">
                <div class="p-6 border-b border-red-50">
                    <h3 class="font-bold text-red-500">Hapus Akun</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Tindakan ini tidak dapat dibatalkan</p>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-500 leading-relaxed mb-4">
                        Setelah akun dihapus, seluruh data, riwayat setoran, dan poin akan hilang permanen.
                    </p>
                    <button @click="showModal = true"
                        class="w-full py-3 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold text-sm shadow-sm active:scale-[0.98] transition">
                        Hapus Akun Saya
                    </button>
                </div>

                {{-- Confirm Modal --}}
                <div x-show="showModal"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-[500] flex items-end sm:items-center justify-center bg-gray-900/50 p-4"
                     style="display: none;">
                    <div @click.away="showModal = false"
                         x-show="showModal"
                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-4"
                         class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <h3 class="font-black text-gray-900">Konfirmasi Hapus Akun</h3>
                            <p class="text-xs text-gray-400 mt-1">Tindakan ini tidak dapat diurungkan</p>
                        </div>
                        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 space-y-4">
                            @csrf @method('delete')
                            <p class="text-sm text-gray-500 leading-relaxed">Masukkan password untuk konfirmasi penghapusan akun kamu.</p>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5">Password</label>
                                <input name="password" type="password" placeholder="Masukkan password kamu"
                                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-red-400 focus:ring-1 focus:ring-red-400 focus:outline-none bg-white text-sm text-gray-700 transition">
                                @error('password', 'userDeletion') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex gap-3 pt-1">
                                <button type="button" @click="showModal = false"
                                    class="flex-1 py-3 text-gray-500 hover:bg-gray-100 rounded-2xl font-bold text-sm transition">Batal</button>
                                <button type="submit"
                                    class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold text-sm active:scale-[0.98] transition">Ya, Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function profilePhotoEditor(currentPhoto) {
            return {
                currentPhoto,
                preview: null,
                file: null,
                fileName: null,
                zoom: 1,
                offsetX: 0,
                offsetY: 0,
                dragging: false,
                dragStartX: 0,
                dragStartY: 0,
                startOffsetX: 0,
                startOffsetY: 0,
                isSaving: false,

                selectFile(event) {
                    const selectedFile = event.target.files[0];
                    if (!selectedFile) return;

                    if (this.preview) {
                        URL.revokeObjectURL(this.preview);
                    }

                    this.file = selectedFile;
                    this.fileName = selectedFile.name;
                    this.preview = URL.createObjectURL(selectedFile);
                    this.resetCrop();
                },

                startDrag(pointer) {
                    if (!this.preview || !pointer) return;

                    this.dragging = true;
                    this.dragStartX = pointer.clientX;
                    this.dragStartY = pointer.clientY;
                    this.startOffsetX = this.offsetX;
                    this.startOffsetY = this.offsetY;
                },

                drag(pointer) {
                    if (!this.dragging || !pointer) return;

                    this.offsetX = this.startOffsetX + (pointer.clientX - this.dragStartX);
                    this.offsetY = this.startOffsetY + (pointer.clientY - this.dragStartY);
                },

                stopDrag() {
                    this.dragging = false;
                },

                resetCrop() {
                    this.zoom = 1;
                    this.offsetX = 0;
                    this.offsetY = 0;
                },

                clearSelection() {
                    if (this.preview) {
                        URL.revokeObjectURL(this.preview);
                    }

                    this.preview = null;
                    this.file = null;
                    this.fileName = null;
                    this.resetCrop();
                    this.$refs.fileInput.value = '';
                    this.$refs.croppedInput.value = '';
                },

                async submitCropped() {
                    if (!this.file || !this.preview) {
                        this.$refs.fileInput.click();
                        return;
                    }

                    this.isSaving = true;

                    try {
                        this.$refs.croppedInput.value = await this.createCroppedImage();
                        this.$refs.form.submit();
                    } catch (error) {
                        this.isSaving = false;
                        alert('Gagal memproses foto. Coba pilih foto lain.');
                    }
                },

                createCroppedImage() {
                    return new Promise((resolve, reject) => {
                        const image = new Image();
                        image.onload = () => {
                            const outputSize = 512;
                            const frameSize = this.$refs.frame?.clientWidth || 128;
                            const positionScale = outputSize / frameSize;
                            const baseScale = Math.max(outputSize / image.naturalWidth, outputSize / image.naturalHeight);
                            const finalScale = baseScale * this.zoom;
                            const drawWidth = image.naturalWidth * finalScale;
                            const drawHeight = image.naturalHeight * finalScale;
                            const drawX = ((outputSize - drawWidth) / 2) + (this.offsetX * positionScale);
                            const drawY = ((outputSize - drawHeight) / 2) + (this.offsetY * positionScale);

                            const canvas = document.createElement('canvas');
                            canvas.width = outputSize;
                            canvas.height = outputSize;

                            const context = canvas.getContext('2d');
                            context.fillStyle = '#ffffff';
                            context.fillRect(0, 0, outputSize, outputSize);
                            context.drawImage(image, drawX, drawY, drawWidth, drawHeight);

                            resolve(canvas.toDataURL('image/jpeg', 0.9));
                        };
                        image.onerror = reject;
                        image.src = this.preview;
                    });
                },
            };
        }
    </script>
</x-app-layout>
