<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penyimpanan & Kuota — {{ config('app.name', 'CloudCampus Storage') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50/60 text-slate-800 font-sans antialiased min-h-screen flex flex-col">
    <!-- Top Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white shadow-sm shadow-brand-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path>
                        </svg>
                    </div>
                    <span class="text-lg font-black text-slate-900 tracking-tight">CloudCampus <span class="text-brand-600">Storage</span></span>
                </a>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        Drive Saya
                    </a>
                    <a href="{{ route('shares.with-me') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        Dibagikan dengan Saya
                    </a>
                    <a href="{{ route('shares.mine') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        Tautan Berbagi Saya
                    </a>
                    <a href="{{ route('storage.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-brand-50 text-brand-700 transition">
                        Penyimpanan
                    </a>
                    <a href="{{ route('trash') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition flex items-center space-x-1.5">
                        <span>Tempat Sampah</span>
                        @php
                            $trashCount = auth()->user()->files()->onlyTrashed()->count() + auth()->user()->folders()->onlyTrashed()->count();
                        @endphp
                        @if ($trashCount > 0)
                            <span class="px-1.5 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold">{{ $trashCount }}</span>
                        @endif
                    </a>
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                @if (auth()->user()->hasAnyRole(['super-admin', 'admin-kampus']))
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 text-xs font-bold border border-purple-200 transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Admin Panel</span>
                    </a>
                @endif

                <!-- User Profile & Badge -->
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 font-bold text-xs flex items-center justify-center">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-left text-xs">
                        <p class="font-bold text-slate-900">{{ auth()->user()->name }}</p>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ auth()->user()->isAcademic() ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ auth()->user()->isAcademic() ? 'Akademik ('.auth()->user()->external_id.')' : 'Publik' }}
                        </span>
                    </div>
                </div>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-xs font-semibold text-slate-700 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
        <livewire:quota-dashboard />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400 mt-auto">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} CloudCampus Storage — Institut Teknologi & Sains CloudCampus. Hak Cipta Dilindungi.</p>
            <div class="flex items-center space-x-4">
                <span>Versi 1.0.0</span>
                <span>&bull;</span>
                <a href="{{ route('storage.index') }}" class="text-brand-600 hover:underline font-semibold">Status Kuota</a>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation (UIUX_BRIEF §8) -->
    <x-mobile-nav />
</body>
</html>
