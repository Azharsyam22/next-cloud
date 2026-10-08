<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tautan Berbagi Saya — {{ config('app.name', 'CloudCampus Storage') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">
    <!-- Top Bar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-brand-600 flex items-center justify-center text-white shadow-sm shadow-brand-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-gray-900 tracking-tight">CloudCampus <span class="text-brand-600">Storage</span></span>
                </a>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                        Drive Saya
                    </a>
                    <a href="{{ route('shares.with-me') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                        Dibagikan dengan Saya
                    </a>
                    <a href="{{ route('shares.mine') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 transition">
                        Tautan Berbagi Saya
                    </a>
                    <a href="{{ route('storage.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                        Penyimpanan
                    </a>
                    <a href="{{ route('trash') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition flex items-center space-x-1.5">
                        <span>Tempat Sampah</span>
                    </a>
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 font-bold text-xs flex items-center justify-center">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-left text-xs">
                        <p class="font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ auth()->user()->isAcademic() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ auth()->user()->isAcademic() ? 'Akademik' : 'Publik' }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 text-xs font-medium text-gray-700 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
        <livewire:my-shared-links />
    </main>

    <!-- Mobile Bottom Navigation (UIUX_BRIEF §8) -->
    <x-mobile-nav />
</body>
</html>
