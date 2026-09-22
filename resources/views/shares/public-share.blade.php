<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFolder ? $item->name : $item->original_name }} — CloudCampus Storage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-lg bg-brand-600 flex items-center justify-center text-white shadow-sm shadow-brand-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold text-gray-900 tracking-tight">CloudCampus <span class="text-brand-600">Storage</span></span>
            </a>

            <div class="flex items-center space-x-3">
                @if (auth()->check())
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs transition">
                        Buka Drive Saya
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium text-xs transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="px-3.5 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium text-xs transition shadow-sm">
                        Daftar Akun
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8 sm:py-12 flex-1 w-full">
        <!-- Item Overview Card -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <!-- Header Section -->
            <div class="p-6 sm:p-8 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-start space-x-4">
                    <!-- Icon -->
                    <div class="w-14 h-14 rounded-2xl {{ $isFolder ? 'bg-indigo-50 text-indigo-600' : 'bg-brand-50 text-brand-600' }} flex items-center justify-center flex-shrink-0 shadow-xs">
                        @if ($isFolder)
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                            </svg>
                        @elseif ($item->isImage())
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        @elseif ($item->isPdf())
                            <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        @else
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        @endif
                    </div>

                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 leading-snug">
                                {{ $isFolder ? $item->name : $item->original_name }}
                            </h1>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $share->canDownload() ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $share->canDownload() ? 'Dapat Diunduh' : 'Hanya Lihat' }}
                            </span>
                        </div>

                        <p class="text-xs text-gray-500 flex items-center gap-2 flex-wrap">
                            <span>Dibagikan oleh <strong class="text-gray-700 font-medium">{{ $share->user->name }}</strong></span>
                            @if (! $isFolder)
                                <span>•</span>
                                <span>{{ $item->formatted_size }}</span>
                            @endif
                            @if ($share->expires_at)
                                <span>•</span>
                                <span class="text-amber-600">Berlaku s/d {{ $share->expires_at->format('d M Y, H:i') }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Download Button -->
                <div class="flex items-center space-x-3">
                    @if ($share->canDownload())
                        <a href="{{ route('shares.public.download', $share->token) }}"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            <span>{{ $isFolder ? 'Unduh Seluruh Folder (.ZIP)' : 'Unduh Berkas' }}</span>
                        </a>
                    @else
                        <div class="px-4 py-2 rounded-xl bg-gray-100 text-gray-500 font-medium text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>Pengunduhan Dinonaktifkan</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Preview / Body Section -->
            <div class="p-6 sm:p-8 bg-gray-50/50 min-h-[300px] flex items-center justify-center">
                @if ($isFolder)
                    <!-- Folder Contents Explorer -->
                    <div class="w-full bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 font-semibold text-xs text-gray-700 flex items-center justify-between">
                            <span>Daftar Isi Folder: {{ $item->name }}</span>
                            <span class="text-gray-500 text-[11px]">{{ $folderContents['files']->count() }} berkas, {{ $folderContents['folders']->count() }} subfolder</span>
                        </div>

                        @if ($folderContents['folders']->isEmpty() && $folderContents['files']->isEmpty())
                            <div class="p-8 text-center text-xs text-gray-400">
                                Folder ini kosong.
                            </div>
                        @else
                            <ul class="divide-y divide-gray-100 text-xs">
                                <!-- Subfolders -->
                                @foreach ($folderContents['folders'] as $subfolder)
                                    <li class="p-3.5 flex items-center justify-between hover:bg-gray-50 transition">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                            </div>
                                            <span class="font-medium text-gray-900">{{ $subfolder->name }}</span>
                                        </div>
                                        <span class="text-gray-400 text-[11px]">Folder</span>
                                    </li>
                                @endforeach

                                <!-- Files -->
                                @foreach ($folderContents['files'] as $f)
                                    <li class="p-3.5 flex items-center justify-between hover:bg-gray-50 transition">
                                        <div class="flex items-center space-x-3 truncate">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 font-bold text-[10px] flex items-center justify-center uppercase">
                                                {{ strtoupper($f->extension ?? 'FILE') }}
                                            </div>
                                            <div class="truncate">
                                                <p class="font-medium text-gray-900 truncate">{{ $f->original_name }}</p>
                                                <p class="text-[10px] text-gray-400">{{ $f->formatted_size }}</p>
                                            </div>
                                        </div>

                                        @if ($share->canDownload())
                                            <a href="{{ route('shares.public.file.download', ['token' => $share->token, 'file' => $f->id]) }}"
                                                class="px-2.5 py-1 text-xs rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-700 font-medium inline-flex items-center gap-1 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                <span>Unduh</span>
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @elseif ($item->isImage())
                    <!-- Image Preview -->
                    <div class="text-center">
                        <img src="{{ route('shares.public.preview', $share->token) }}"
                            alt="{{ $item->original_name }}"
                            class="max-h-[500px] max-w-full rounded-xl shadow-md border border-gray-200 mx-auto object-contain">
                    </div>
                @elseif ($item->isPdf())
                    <!-- PDF Preview -->
                    <div class="w-full h-[600px] rounded-xl overflow-hidden border border-gray-200 bg-white">
                        <iframe src="{{ route('shares.public.preview', $share->token) }}" class="w-full h-full" frameborder="0"></iframe>
                    </div>
                @elseif ($item->isVideo())
                    <!-- Video Preview -->
                    <div class="w-full max-w-2xl mx-auto rounded-xl overflow-hidden shadow-md">
                        <video controls class="w-full max-h-[450px] bg-black">
                            <source src="{{ route('shares.public.preview', $share->token) }}" type="{{ $item->mime_type }}">
                            Browser Anda tidak mendukung pemutar video HTML5.
                        </video>
                    </div>
                @elseif ($item->isAudio())
                    <!-- Audio Preview -->
                    <div class="w-full max-w-md mx-auto bg-white p-6 rounded-2xl border border-gray-200 shadow-sm text-center">
                        <audio controls class="w-full">
                            <source src="{{ route('shares.public.preview', $share->token) }}" type="{{ $item->mime_type }}">
                            Browser Anda tidak mendukung pemutar audio HTML5.
                        </audio>
                    </div>
                @else
                    <!-- Generic Document Card Preview -->
                    <div class="text-center py-12">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">{{ $item->original_name }}</h3>
                        <p class="text-xs text-gray-500 mb-4">{{ $item->mime_type }} • {{ $item->formatted_size }}</p>
                        @if ($share->canDownload())
                            <a href="{{ route('shares.public.download', $share->token) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <span>Unduh Berkas</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
