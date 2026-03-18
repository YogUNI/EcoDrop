@php
    $role = Auth::user()->role;
    $isAdmin = $role === 'admin';
    $isSuperAdmin = $role === 'super_admin';
    $isUser = $role === 'user';

    $navBg       = $isUser ? 'bg-white border-green-200'
                 : ($isAdmin ? 'bg-white border-blue-200'
                 : 'bg-white border-amber-200');

    $logoBg      = $isUser ? 'from-green-400 to-emerald-600'
                 : ($isAdmin ? 'from-blue-400 to-indigo-600'
                 : 'from-amber-400 to-orange-600');

    $logoEmoji   = $isUser ? '🌱' : ($isAdmin ? '👑' : '⭐');

    $brandColor  = $isUser ? 'from-green-600 to-emerald-600'
                 : ($isAdmin ? 'from-blue-600 to-indigo-600'
                 : 'from-amber-600 to-orange-600');

    $roleLabel   = $isUser ? '🌿 User'
                 : ($isAdmin ? '👑 Admin'
                 : '⭐ Super Admin');

    $roleBadge   = $isUser ? 'bg-green-100 text-green-700 border-green-200'
                 : ($isAdmin ? 'bg-blue-100 text-blue-700 border-blue-200'
                 : 'bg-amber-100 text-amber-700 border-amber-200');

    $hoverBtn    = $isUser ? 'hover:border-green-400 hover:bg-green-50'
                 : ($isAdmin ? 'hover:border-blue-400 hover:bg-blue-50'
                 : 'hover:border-amber-400 hover:bg-amber-50');

    $dashRoute   = $isUser ? 'user.dashboard'
                 : ($isAdmin ? 'admin.dashboard'
                 : 'superadmin.dashboard');

    $pendingCount = 0;
    if ($isAdmin || $isSuperAdmin) {
        $pendingCount = \App\Models\Pickup::where('status', 'pending')->count();
    }

    $photoUrl = Auth::user()->getPhotoUrl();
@endphp

<nav x-data="{ open: false }" class="border-b-2 shadow-sm sticky top-0 z-50 {{ $navBg }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- Logo --}}
            <a href="{{ route($dashRoute) }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 bg-gradient-to-br {{ $logoBg }} rounded-xl flex items-center justify-center text-xl shadow-md group-hover:shadow-lg transition duration-300 transform group-hover:scale-105">
                    {{ $logoEmoji }}
                </div>
                <div>
                    <h1 class="text-lg font-black bg-gradient-to-r {{ $brandColor }} bg-clip-text text-transparent leading-tight">EcoDrop</h1>
                    <p class="text-xs text-gray-400 -mt-0.5 font-medium">Waste to Worth</p>
                </div>
            </a>

            {{-- Desktop Right --}}
            <div class="hidden sm:flex items-center gap-3">

                {{-- Pending Badge --}}
                @if(($isAdmin || $isSuperAdmin) && $pendingCount > 0)
                    <a href="{{ route($dashRoute) }}"
                       class="relative inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm transition duration-300
                           {{ $isAdmin ? 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' }}">
                        <span class="animate-pulse">⏳</span>
                        {{ $pendingCount }} Pending
                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full text-white text-xs font-black flex items-center justify-center shadow
                            {{ $isAdmin ? 'bg-blue-600' : 'bg-amber-600' }}">
                            {{ $pendingCount > 9 ? '9+' : $pendingCount }}
                        </span>
                    </a>
                @endif

                {{-- Dropdown User --}}
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-3 px-4 py-2 border-2 border-gray-200 rounded-xl text-sm font-semibold text-gray-700 bg-white transition duration-300 hover:scale-105 {{ $hoverBtn }}">
                            {{-- Foto Profile (trigger) --}}
                            <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-white shadow flex-shrink-0">
                                <img src="{{ $photoUrl }}" class="w-full h-full object-cover" alt="{{ Auth::user()->name }}">
                            </div>
                            <div class="text-left hidden md:block">
                                <div class="text-xs font-bold {{ $isUser ? 'text-green-600' : ($isAdmin ? 'text-blue-600' : 'text-amber-600') }}">
                                    {{ $roleLabel }}
                                </div>
                                <div class="text-gray-800 font-bold text-sm leading-tight">{{ Str::limit(Auth::user()->name, 15) }}</div>
                            </div>
                            <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Header dropdown --}}
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                {{-- Foto Profile (dropdown header) --}}
                                <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100 shadow flex-shrink-0">
                                    <img src="{{ $photoUrl }}" class="w-full h-full object-cover" alt="{{ Auth::user()->name }}">
                                </div>
                                <div>
                                    <p class="font-black text-gray-900 text-sm">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 truncate max-w-[160px]">{{ Auth::user()->email }}</p>
                                    <span class="inline-flex items-center text-xs font-bold px-2 py-0.5 rounded-full border mt-1 {{ $roleBadge }}">
                                        {{ $roleLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Profile --}}
                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2 font-semibold text-gray-700">
                            <span>⚙️</span> Profile & Pengaturan
                        </x-dropdown-link>

                        <div class="border-t border-gray-100 my-1"></div>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 flex items-center gap-2 transition duration-200 rounded-b-lg">
                                <span>🚪</span> Keluar dari Akun
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Mobile Hamburger --}}
            <div class="sm:hidden">
                <button @click="open = !open" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition duration-300">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-gray-100 bg-white">
        <div class="px-4 py-4 space-y-2">

            {{-- User info --}}
            <div class="flex items-center gap-3 p-4 rounded-2xl border {{ $roleBadge }}">
                {{-- Foto Profile (mobile) --}}
                <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow flex-shrink-0">
                    <img src="{{ $photoUrl }}" class="w-full h-full object-cover" alt="{{ Auth::user()->name }}">
                </div>
                <div>
                    <p class="font-black text-gray-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    <span class="text-xs font-bold {{ $isUser ? 'text-green-600' : ($isAdmin ? 'text-blue-600' : 'text-amber-600') }}">{{ $roleLabel }}</span>
                </div>
            </div>

            {{-- Pending badge mobile --}}
            @if(($isAdmin || $isSuperAdmin) && $pendingCount > 0)
                <a href="{{ route($dashRoute) }}"
                   class="flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-bold
                       {{ $isAdmin ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                    <span>⏳</span> {{ $pendingCount }} Setoran Pending
                </a>
            @endif

            {{-- Profile --}}
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-100 transition">
                <span>⚙️</span> Profile & Pengaturan
            </a>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-bold text-red-600 hover:bg-red-50 transition">
                    <span>🚪</span> Keluar dari Akun
                </button>
            </form>
        </div>
    </div>
</nav>