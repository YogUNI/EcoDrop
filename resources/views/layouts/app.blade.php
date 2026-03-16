<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EcoDrop') }} - Platform Manajemen Sampah</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Favicon -->
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-sm border-b border-green-100 dark:border-green-700">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="min-h-[calc(100vh-200px)]">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border-t border-green-100 dark:border-green-700 mt-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Brand -->
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-600 rounded-lg flex items-center justify-center text-lg">🌱</div>
                                <h3 class="font-black text-gray-900 dark:text-white">EcoDrop</h3>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Platform manajemen sampah berbasis reward untuk gaya hidup yang lebih hijau.</p>
                        </div>

                        <!-- Quick Links -->
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white mb-3">Quick Links</h4>
                            <ul class="space-y-2 text-sm">
                                <li><a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition">Dashboard</a></li>
                                <li><a href="{{ route('profile.edit') }}" class="text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition">Profile</a></li>
                            </ul>
                        </div>

                        <!-- Info -->
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white mb-3">Info</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">© 2026 EcoDrop. Semua hak dilindungi.</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Dibuat dengan ❤️ untuk planet yang lebih baik</p>
                        </div>
                    </div>

                    <div class="border-t border-green-100 dark:border-green-700 mt-8 pt-8 text-center text-xs text-gray-500 dark:text-gray-400">
                        <p>EcoDrop v1.0 | Pemrograman Web Project</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>