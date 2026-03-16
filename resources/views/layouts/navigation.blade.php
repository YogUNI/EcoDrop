<nav x-data="{ open: false }" class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-900 dark:to-gray-800 border-b-2 border-green-200 dark:border-green-700 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            
            <!-- Logo & Brand -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                    <!-- EcoDrop Logo -->
                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-xl flex items-center justify-center text-xl shadow-lg group-hover:shadow-xl transition duration-300 transform group-hover:scale-110">
                        🌱
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-lg font-black bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">EcoDrop</h1>
                        <p class="text-xs text-gray-500 -mt-1">Waste to Worth</p>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex md:items-center md:gap-8">
                <a href="{{ route('dashboard') }}" class="group relative px-3 py-2 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'text-green-600' : 'text-gray-700 dark:text-gray-300' }} hover:text-green-600 transition duration-300">
                    <span class="flex items-center gap-2">
                        <span>📊</span>
                        Dashboard
                    </span>
                    @if(request()->routeIs('dashboard'))
                        <span class="absolute bottom-0 left-0 right-0 h-1 bg-green-600 rounded-t-lg"></span>
                    @endif
                </a>
            </div>

            <!-- Desktop User Menu & Mobile Menu Toggle -->
            <div class="flex items-center gap-4">
                <!-- Desktop Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-4 py-2 border-2 border-green-300 text-sm leading-4 font-semibold rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-green-50 dark:hover:bg-gray-700 hover:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition duration-300 transform hover:scale-105">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">👤</span>
                                    <div class="text-left">
                                        <div class="text-xs text-gray-500">{{ Auth::user()->role === 'admin' ? '👑 Admin' : '👤 User' }}</div>
                                        <div>{{ Auth::user()->name }}</div>
                                    </div>
                                </div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-3 text-xs text-gray-500 border-b border-gray-200 dark:border-gray-600">
                                {{ Auth::user()->email }}
                            </div>
                            <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                                <span>⚙️</span>
                                {{ __('Profile & Pengaturan') }}
                            </x-dropdown-link>

                            <div class="border-t border-gray-200 dark:border-gray-600"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="flex items-center gap-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <span>🚪</span>
                                    {{ __('Logout') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Mobile Hamburger Menu -->
                <div class="md:hidden">
                    <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 hover:bg-green-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-green-100 dark:focus:bg-gray-700 transition duration-300">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden md:hidden border-t-2 border-green-200 dark:border-green-700 bg-white dark:bg-gray-800">
        <div class="px-4 py-3 space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-lg text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition duration-300 flex items-center gap-2">
                <span>📊</span>
                Dashboard
            </a>
        </div>

        <!-- Mobile User Settings -->
        <div class="px-4 py-4 border-t-2 border-green-200 dark:border-green-700 space-y-2">
            <div class="px-4 py-2 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-700">
                <div class="text-xs text-gray-600 dark:text-gray-400">{{ Auth::user()->role === 'admin' ? '👑 Admin' : '👤 User' }}</div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-300 flex items-center gap-2">
                <span>⚙️</span>
                Profile & Pengaturan
            </a>

            <!-- Mobile Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" onclick="event.preventDefault(); this.closest('form').submit();" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition duration-300 flex items-center gap-2 justify-start">
                    <span>🚪</span>
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>