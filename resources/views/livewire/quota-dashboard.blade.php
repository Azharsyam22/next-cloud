<div>
    <!-- Notifikasi Sukses -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Banner Peringatan Kuota Penuh / Waspada (>= 90%) -->
    @if ($stats['is_warning'])
        <div class="mb-6 p-5 rounded-2xl {{ $stats['is_critical'] ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-amber-50 border-amber-200 text-amber-900' }} border shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-xl {{ $stats['is_critical'] ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center flex-shrink-0 mt-0.5 sm:mt-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-base {{ $stats['is_critical'] ? 'text-rose-900' : 'text-amber-900' }}">
                            {{ $stats['is_critical'] ? 'Kapasitas Penyimpanan Kritis (' . $stats['percentage'] . '%)' : 'Peringatan: Kuota Penyimpanan Hampir Penuh (' . $stats['percentage'] . '%)' }}
                        </h4>
                        <p class="text-sm mt-0.5 {{ $stats['is_critical'] ? 'text-rose-700' : 'text-amber-700' }}">
                            Anda telah menggunakan <strong>{{ $stats['formatted_used'] }}</strong> dari total <strong>{{ $stats['formatted_quota'] }}</strong>. Tersisa hanya <strong>{{ $stats['formatted_remaining'] }}</strong>. Bersihkan tempat sampah atau hapus berkas lama untuk menghindari penolakan unggahan baru.
                        </p>
                    </div>
                </div>

                @if ($stats['trash_bytes'] > 0)
                    <button wire:click="emptyTrash" wire:confirm="Apakah Anda yakin ingin mengosongkan tempat sampah secara permanen?"
                            class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-semibold {{ $stats['is_critical'] ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-amber-600 hover:bg-amber-700 text-white' }} transition shadow-sm flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Kosongkan Tempat Sampah ({{ $stats['formatted_trash'] }})</span>
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- Header Dasbor -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Kuota & Manajemen Penyimpanan</h1>
            <p class="text-sm text-slate-500 mt-0.5">Pantau alokasi ruang disk, distribusi tipe berkas, dan optimalkan penyimpanan akun Anda.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button wire:click="recalculate" wire:loading.attr="disabled"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold shadow-sm transition">
                <svg wire:loading.remove wire:target="recalculate" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading wire:target="recalculate" class="animate-spin w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span wire:loading.remove wire:target="recalculate">Sinkronisasi Kuota</span>
                <span wire:loading wire:target="recalculate">Memperbarui...</span>
            </button>
        </div>
    </div>

    <!-- Kartu Utama: Progres Penyimpanan Tersegmentasi -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div class="flex items-center space-x-5">
                <div class="w-14 h-14 rounded-2xl {{ $stats['is_warning'] ? ($stats['is_critical'] ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600') : 'bg-brand-50 text-brand-600' }} flex items-center justify-center flex-shrink-0 shadow-inner">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7zm0 5h16" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl font-black text-slate-900">{{ $stats['formatted_used'] }}</span>
                        <span class="text-slate-400 text-lg font-medium">/ {{ $stats['formatted_quota'] }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $stats['is_warning'] ? ($stats['is_critical'] ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') : 'bg-brand-100 text-brand-800' }}">
                            {{ $stats['percentage'] }}% Terpakai
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Tersedia {{ $stats['formatted_remaining'] }} dari total alokasi akun Anda.</p>
                </div>
            </div>

            <!-- Ringkasan Cepat -->
            <div class="flex items-center space-x-6 text-sm">
                <div>
                    <span class="block text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Berkas</span>
                    <span class="text-lg font-bold text-slate-800">{{ number_format($stats['total_files']) }} Berkas</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div>
                    <span class="block text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Folder</span>
                    <span class="text-lg font-bold text-slate-800">{{ number_format($stats['folder_count']) }} Folder</span>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div>
                    <span class="block text-xs text-slate-400 font-semibold uppercase tracking-wider">Tempat Sampah</span>
                    <span class="text-lg font-bold text-rose-600">{{ $stats['formatted_trash'] }}</span>
                </div>
            </div>
        </div>

        <!-- Multi-Segmented Progress Bar -->
        <div class="mt-6">
            <div class="h-4 w-full bg-slate-100 rounded-full overflow-hidden flex shadow-inner">
                @php
                    $renderedWidth = 0;
                @endphp
                @foreach ($breakdown as $cat)
                    @if ($cat['percentage'] > 0)
                        @php
                            $width = min($cat['percentage'], 100 - $renderedWidth);
                            $renderedWidth += $width;
                        @endphp
                        <div style="width: {{ $width }}%" class="{{ $cat['bg_class'] }} transition-all duration-500 hover:opacity-90 relative group"
                             title="{{ $cat['label'] }}: {{ $cat['formatted'] }} ({{ $cat['percentage'] }}%)">
                        </div>
                    @endif
                @endforeach
                @if ($stats['percentage'] < 100)
                    <div style="width: {{ 100 - min(100, $stats['percentage']) }}%" class="bg-slate-100" title="Tersedia: {{ $stats['formatted_remaining'] }}"></div>
                @endif
            </div>

            <!-- Legend Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-4 pt-2">
                @foreach ($breakdown as $cat)
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="w-3 h-3 rounded-full {{ $cat['bg_class'] }} flex-shrink-0"></span>
                        <span class="text-slate-600 truncate">{{ $cat['label'] }}:</span>
                        <span class="font-bold text-slate-900">{{ $cat['formatted'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Grid Kartu Kategori Tipe Berkas -->
    <h2 class="text-lg font-bold text-slate-900 mb-4">Rincian Penggunaan Berdasarkan Jenis Berkas</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        @foreach ($breakdown as $cat)
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-11 h-11 rounded-xl {{ $cat['bg_light'] }} flex items-center justify-center flex-shrink-0">
                            @if ($cat['key'] === 'documents')
                                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            @elseif ($cat['key'] === 'images')
                                <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @elseif ($cat['key'] === 'videos')
                                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            @elseif ($cat['key'] === 'audio')
                                <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            @elseif ($cat['key'] === 'archives')
                                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ $cat['label'] }}</h3>
                            <p class="text-xs text-slate-400">{{ $cat['count'] }} berkas</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 rounded-lg {{ $cat['bg_light'] }} {{ $cat['text_class'] }}">
                        {{ $cat['percentage'] }}%
                    </span>
                </div>

                <div class="mt-4 flex items-baseline justify-between">
                    <span class="text-xl font-black text-slate-800">{{ $cat['formatted'] }}</span>
                </div>

                <!-- Mini Bar -->
                <div class="mt-2.5 h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full {{ $cat['bg_class'] }} rounded-full" style="width: {{ min(100, $cat['percentage']) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Bagian Optimasi & Berkas Terbesar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Kolom Kiri: Optimasi Tempat Sampah -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Ruang di Tempat Sampah</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Berkas yang dipindahkan ke Tempat Sampah tetap menggunakan alokasi kuota penyimpanan Anda sampai dihapus secara permanen atau melewati masa retensi 30 hari.
                </p>

                <div class="mt-5 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500">Ruang Dapat Dihemat</span>
                        <span class="text-lg font-black text-rose-600">{{ $stats['formatted_trash'] }}</span>
                    </div>
                    <div class="text-xs text-slate-400 mt-1">
                        {{ $stats['trashed_file_count'] }} berkas berada di tempat sampah
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center space-x-3">
                <a href="{{ route('trash') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                    Lihat Sampah
                </a>
                @if ($stats['trash_bytes'] > 0)
                    <button wire:click="emptyTrash" wire:confirm="Yakin ingin mengosongkan tempat sampah? Berkas akan hilang permanen."
                            class="flex-1 text-center px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition shadow-sm">
                        Kosongkan
                    </button>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Berkas Terbesar -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Berkas Terbesar Milik Anda</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Identifikasi berkas berukuran besar untuk mengoptimalkan ruang.</p>
                </div>
            </div>

            @if ($largestFiles->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm font-medium text-slate-600">Belum ada berkas yang diunggah.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($largestFiles as $file)
                        <div class="py-3.5 flex items-center justify-between hover:bg-slate-50/50 rounded-xl px-2 transition">
                            <div class="flex items-center space-x-3.5 min-w-0 flex-1 mr-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-bold text-slate-800 truncate" title="{{ $file->original_name }}">
                                        {{ $file->original_name }}
                                    </h4>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $file->folder ? 'Folder: ' . $file->folder->name : 'Drive Utama' }} &bull; Diunggah {{ $file->created_at?->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-4">
                                <span class="text-sm font-black text-slate-800 whitespace-nowrap">
                                    {{ app(\App\Services\QuotaService::class)->formatBytes($file->size) }}
                                </span>

                                <div class="flex items-center space-x-1">
                                    <a href="{{ route('files.download', $file) }}" class="p-2 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 transition" title="Unduh">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                    <button wire:click="deleteFile({{ $file->id }})" wire:confirm="Pindahkan berkas ini ke tempat sampah?"
                                            class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Pindahkan ke Tempat Sampah">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
