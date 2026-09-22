<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Autentikasi' }} — {{ config('app.name', 'CloudCampus Storage') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col justify-between">
    <!-- Header Brand -->
    <header class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center space-x-3 text-brand-600 font-bold text-xl tracking-tight hover:opacity-90 transition">
            <div class="w-10 h-10 rounded-lg bg-brand-600 flex items-center justify-center text-white shadow-md shadow-brand-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path>
                </svg>
            </div>
            <span>CloudCampus <span class="text-gray-900 font-semibold text-base">Storage</span></span>
        </a>
        <a href="{{ route('home') }}" class="text-sm font-medium text-gray-500 hover:text-brand-600 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Beranda
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-gray-500">
        &copy; {{ date('Y') }} CloudCampus Storage. Sistem Penyimpanan Berkas Kampus & Publik.
    </footer>
</body>
</html>
