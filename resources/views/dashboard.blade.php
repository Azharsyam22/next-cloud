<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — {{ config('app.name', 'CloudCampus Storage') }}</title>
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
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 transition">
                        Drive Saya
                    </a>
                    <a href="{{ route('shares.with-me') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                        Dibagikan dengan Saya
                    </a>
                    <a href="{{ route('shares.mine') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                        Tautan Berbagi Saya
                    </a>
                    <a href="{{ route('storage.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                        Penyimpanan
                    </a>
                    <a href="{{ route('trash') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition flex items-center space-x-1.5">
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
                        <p class="font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ auth()->user()->isAcademic() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ auth()->user()->isAcademic() ? 'Akademik ('.auth()->user()->external_id.')' : 'Publik' }}
                        </span>
                    </div>
                </div>

                <!-- Logout Button -->
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
        @if (session('status'))
            <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Kuota Card -->
            @php
                $quotaService = app(\App\Services\QuotaService::class);
                $quotaStats = $quotaService->getQuotaStats(auth()->user());
            @endphp
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Kuota Penyimpanan</h3>
                        <a href="{{ route('storage.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                            Rincian &rarr;
                        </a>
                    </div>
                    <div class="flex items-baseline justify-between mb-2">
                        <span class="text-2xl font-black text-gray-900">{{ $quotaStats['formatted_used'] }}</span>
                        <span class="text-xs text-gray-500 font-medium">dari {{ $quotaStats['formatted_quota'] }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $quotaStats['is_critical'] ? 'bg-rose-500' : ($quotaStats['is_warning'] ? 'bg-amber-500' : 'bg-brand-600') }}"
                             style="width: {{ min(100, $quotaStats['percentage']) }}%"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3 text-[11px] text-gray-500">
                    <span class="font-medium {{ $quotaStats['is_warning'] ? ($quotaStats['is_critical'] ? 'text-rose-600 font-bold' : 'text-amber-600 font-bold') : 'text-gray-600' }}">
                        {{ $quotaStats['percentage'] }}% terpakai
                    </span>
                    <span>Sisa: {{ $quotaStats['formatted_remaining'] }}</span>
                </div>
            </div>

            <!-- Tipe Akun Card -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Status Akun</h3>
                <p class="text-xl font-bold text-gray-900 mb-1">
                    {{ auth()->user()->isAcademic() ? 'Akun Sivitas Akademika' : 'Akun Pengguna Publik' }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ auth()->user()->isAcademic() ? 'Dikelola secara terpusat oleh SSO Sistem Akademik Kampus.' : 'Akun mandiri dengan login email & password.' }}
                </p>
            </div>

            <!-- Ringkasan File Card -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Total Berkas</h3>
                <p class="text-2xl font-extrabold text-gray-900 mb-1">{{ auth()->user()->files()->count() }} File</p>
                <p class="text-xs text-gray-500">Dalam {{ auth()->user()->folders()->count() }} folder tersimpan</p>
            </div>
        </div>

        <!-- Livewire File Explorer -->
        <livewire:file-explorer />
    </main>
</body>
</html>
