<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'CloudCampus Storage') }} — Penyimpanan Berkas Kampus & Publik</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-800 font-sans antialiased selection:bg-brand-500 selection:text-white">
    <!-- Navbar -->
    <header class="border-b border-gray-100 sticky top-0 bg-white/80 backdrop-blur-md z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-lg bg-brand-600 flex items-center justify-center text-white shadow-sm shadow-brand-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold text-gray-900 tracking-tight">CloudCampus <span class="text-brand-600">Storage</span></span>
            </div>

            <nav class="flex items-center space-x-3">
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-brand-600 transition">
                    Masuk
                </a>
                <a href="{{ route('sso.redirect') }}" class="px-4 py-2 text-sm font-semibold rounded-lg bg-brand-600 hover:bg-brand-700 text-white shadow-sm transition">
                    Login Akademik (SSO)
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section (UIUX_BRIEF §5.1) -->
    <section class="relative overflow-hidden py-20 lg:py-28 bg-gradient-to-b from-brand-50/50 via-white to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-100/80 text-brand-700 text-xs font-semibold mb-6">
                <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                Tersedia untuk Mahasiswa, Dosen, dan Pengguna Umum
            </span>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 tracking-tight max-w-4xl mx-auto leading-tight sm:leading-none">
                Penyimpanan Berkas Kampus yang <span class="text-brand-600">Aman & Terintegrasi</span>
            </h1>

            <p class="mt-6 text-base sm:text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                Kelola dokumen perkuliahan, berkas tugas, atau file pribadi dalam satu tempat. Terhubung langsung dengan SSO Sistem Akademik kampus.
            </p>

            <!-- 2 CTA Utama (UIUX_BRIEF §5.1) -->
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto">
                <a href="{{ route('sso.redirect') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 hover:shadow-lg transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                    Login Akademik (SSO)
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-lg bg-white border border-gray-300 hover:border-gray-400 text-gray-700 font-semibold text-sm shadow-sm hover:bg-gray-50 transition flex items-center justify-center">
                    Daftar / Masuk Umum
                </a>
            </div>
        </div>
    </section>

    <!-- 3 Kolom Fitur Singkat (UIUX_BRIEF §5.1) -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-brand-200 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Upload Cepat & Fleksibel</h3>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Dukungan drag-and-drop, multi-file upload, progress bar real-time, dan manajemen folder bertingkat yang rapi.
                </p>
            </div>

            <div class="p-6 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-brand-200 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Berbagi Aman & Terkontrol</h3>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Bagikan file lewat link aman dengan masa kedaluwarsa atau batasi izin hanya untuk melihat tanpa unduh.
                </p>
            </div>

            <div class="p-6 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-brand-200 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Terintegrasi Sistem Kampus</h3>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Login otomatis dengan akun akademik mahasiswa/dosen dan integrasi API programatik untuk pengumpulan tugas.
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-200 py-8 bg-gray-50 text-center text-xs text-gray-500">
        <p>&copy; {{ date('Y') }} CloudCampus Storage. Dikembangkan untuk Sivitas Akademika Kampus & Publik.</p>
    </footer>
</body>
</html>
