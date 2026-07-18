@php
    $role = Auth::user()->role;
    $isAdmin = $role === 'admin';
    $isSuperAdmin = $role === 'super_admin';
    $isUser = $role === 'user';

    $dashRoute   = $isUser ? 'user.dashboard'
                 : ($isAdmin ? 'admin.dashboard'
                 : 'superadmin.dashboard');

    $pendingCount = 0;
    if ($isAdmin || $isSuperAdmin) {
        $pendingCount = \App\Models\Pickup::where('status', 'pending')->count();
    }

    $photoUrl = Auth::user()->getPhotoUrl();
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- Logo --}}
            <a href="{{ route($dashRoute) }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-md transition">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <span class="text-lg font-black text-emerald-600">EcoDrop</span>
            </a>

            {{-- Desktop Right --}}
            <div class="hidden sm:flex items-center gap-3">

                {{-- Pending Badge --}}
                @if(($isAdmin || $isSuperAdmin) && $pendingCount > 0)
                    <a href="{{ route($dashRoute) }}"
                       class="relative inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100 transition">
                        <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></span>
                        {{ $pendingCount }} Pending
                    </a>
                @endif

                {{-- Dropdown User --}}
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-sm font-semibold text-gray-700 bg-white border border-gray-100 hover:bg-gray-50 transition">
                            <div class="w-7 h-7 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
                                <img src="{{ $photoUrl }}" class="w-full h-full object-cover" alt="{{ Auth::user()->name }}">
                            </div>
                            <span class="text-sm font-bold text-gray-700 hidden md:inline">{{ Str::limit(Auth::user()->name, 18) }}</span>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Header dropdown --}}
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
                                    <img src="{{ $photoUrl }}" class="w-full h-full object-cover" alt="{{ Auth::user()->name }}">
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900 text-sm truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Profile --}}
                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2 text-gray-700 hover:text-gray-900 font-semibold">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profil & Pengaturan
                        </x-dropdown-link>

                        @if($isUser)
                            <x-dropdown-link :href="route('rewards.index')" class="flex items-center gap-2 text-gray-700 hover:text-gray-900 font-semibold">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0h4m-4 0h-4m0 0v13m0 13h12"></path>
                                </svg>
                                Katalog Hadiah / Tukar Poin
                            </x-dropdown-link>
                        @else
                            @php
                                $redemptionsRoute = $role === 'super_admin' ? 'superadmin.redemptions' : 'admin.redemptions';
                            @endphp
                            <x-dropdown-link :href="route($redemptionsRoute)" class="flex items-center gap-2 text-gray-700 hover:text-gray-900 font-semibold">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Verifikasi Klaim Poin
                            </x-dropdown-link>
                        @endif

                        <div class="border-t border-gray-100 my-1"></div>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 flex items-center gap-2 transition rounded-b-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Mobile Hamburger --}}
            <div class="sm:hidden">
                <button @click="open = !open" class="p-2 rounded-xl text-gray-500 hover:bg-gray-50 transition">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
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
            <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50">
                <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
                    <img src="{{ $photoUrl }}" class="w-full h-full object-cover" alt="{{ Auth::user()->name }}">
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-400">{{ Auth::user()->email }}</p>
                </div>
            </div>

            {{-- Pending badge mobile --}}
            @if(($isAdmin || $isSuperAdmin) && $pendingCount > 0)
                <a href="{{ route($dashRoute) }}"
                   class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-yellow-50 text-yellow-700 border border-yellow-100">
                    <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></span>
                    {{ $pendingCount }} Setoran Pending
                </a>
            @endif

            {{-- Profile --}}
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Profil & Pengaturan
            </a>

            @if($isUser)
                <a href="{{ route('rewards.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0h4m-4 0h-4m0 0v13m0 13h12"></path>
                    </svg>
                    Katalog Hadiah / Tukar Poin
                </a>
            @else
                @php
                    $redemptionsRouteMobile = $role === 'super_admin' ? 'superadmin.redemptions' : 'admin.redemptions';
                @endphp
                <a href="{{ route($redemptionsRouteMobile) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Verifikasi Klaim Poin
                </a>
            @endif

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-red-500 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
</nav>