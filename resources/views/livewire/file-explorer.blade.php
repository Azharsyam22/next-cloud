<div class="space-y-6">
    <!-- Feedback Toast / Alert -->
    @if ($feedbackMessage)
        <div class="p-4 rounded-lg flex items-center justify-between text-sm transition {{ $feedbackType === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800' }}"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="flex items-center gap-2.5">
                @if ($feedbackType === 'success')
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @endif
                <span class="font-medium">{{ $feedbackMessage }}</span>
            </div>
            <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    <!-- Banner Peringatan Kuota >90% -->
    @php
        $quotaUser = auth()->user();
        $usagePercent = $quotaUser ? $quotaUser->quotaUsagePercentage() : 0;
    @endphp
    @if ($quotaUser && $usagePercent >= 90)
        <div class="p-4 rounded-xl {{ $usagePercent >= 98 ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-amber-50 border border-amber-200 text-amber-900' }} shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg {{ $usagePercent >= 98 ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm">
                        {{ $usagePercent >= 98 ? 'Penyimpanan Kritis (' . $usagePercent . '% Terpakai)' : 'Peringatan Kuota: ' . $usagePercent . '% Terpakai' }}
                    </p>
                    <p class="text-xs opacity-90">
                        Sisa ruang penyimpanan Anda menipis. Bersihkan tempat sampah atau hapus berkas untuk mengosongkan ruang.
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <a href="{{ route('storage.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-current hover:bg-slate-50 transition whitespace-nowrap">
                    Kelola Kuota
                </a>
                <a href="{{ route('trash') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $usagePercent >= 98 ? 'bg-rose-600 hover:bg-rose-700' : 'bg-amber-600 hover:bg-amber-700' }} text-white transition whitespace-nowrap">
                    Tempat Sampah
                </a>
            </div>
        </div>
    @endif

    <!-- Toolbar: Breadcrumbs + Actions -->
    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Breadcrumbs Navigation -->
        <nav class="flex items-center space-x-1.5 text-sm overflow-x-auto py-1">
            @foreach ($breadcrumbs as $crumb)
                @if (! $loop->first)
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                @endif
                @if ($loop->last)
                    <span class="font-semibold text-gray-900 px-2 py-1 rounded bg-gray-100 flex-shrink-0">
                        {{ $crumb['name'] }}
                    </span>
                @else
                    <button type="button" wire:click="navigateToFolder({{ $crumb['id'] ?? 'null' }})"
                        class="text-gray-600 hover:text-brand-600 hover:underline px-1.5 py-0.5 rounded transition flex-shrink-0 font-medium">
                        {{ $crumb['name'] }}
                    </button>
                @endif
            @endforeach
        </nav>

        <!-- Right Side: Search + View Toggle + Actions -->
        <div class="flex items-center space-x-2.5">
            <!-- Search Input -->
            <div class="relative w-44 sm:w-56">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari di folder..."
                    class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-gray-300 text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
            </div>

            <!-- Grid / List View Toggle -->
            <div class="inline-flex rounded-lg border border-gray-200 p-0.5 bg-gray-50">
                <button type="button" wire:click="toggleViewMode('grid')"
                    class="p-1.5 rounded-md text-xs transition {{ $viewMode === 'grid' ? 'bg-white shadow-xs text-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-900' }}"
                    title="Tampilan Grid">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </button>
                <button type="button" wire:click="toggleViewMode('list')"
                    class="p-1.5 rounded-md text-xs transition {{ $viewMode === 'list' ? 'bg-white shadow-xs text-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-900' }}"
                    title="Tampilan Tabel / List">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Tombol Buat Folder -->
            <button type="button" wire:click="openCreateModal"
                class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium text-xs flex items-center gap-1.5 transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                </svg>
                <span>Folder Baru</span>
            </button>

            <!-- Tombol Unduh Folder Saat Ini (.zip) -->
            @if ($currentFolderId !== null)
                <a href="{{ route('folders.download', $currentFolderId) }}"
                    class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium text-xs flex items-center gap-1.5 transition"
                    title="Unduh seluruh isi folder ini sebagai arsip .ZIP">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span>Unduh ZIP</span>
                </a>
            @endif

            <!-- Tombol Unggah Berkas (Upload) -->
            <button type="button" wire:click="openUploadModal"
                class="px-3.5 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                <span>Upload</span>
            </button>
        </div>
    </div>

    <!-- Section: Folder List -->
    @if ($folders->isNotEmpty())
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Folders ({{ $folders->count() }})</h2>

            @if ($viewMode === 'grid')
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5">
                    @foreach ($folders as $folder)
                        <div class="group relative bg-white p-3.5 rounded-lg border border-gray-200 hover:border-brand-400 hover:shadow-md transition flex flex-col justify-between"
                            x-data="{ openMenu: false }">
                            <div class="cursor-pointer" wire:click="navigateToFolder({{ $folder->id }})">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white shadow-xs"
                                        style="background-color: {{ $folder->color ?: '#3B82F6' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                    </div>

                                    <button type="button" @click.stop="openMenu = !openMenu"
                                        class="p-1 rounded-md text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <p class="text-xs font-semibold text-gray-900 truncate" title="{{ $folder->name }}">
                                    {{ $folder->name }}
                                </p>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    {{ $folder->files_count }} file, {{ $folder->children_count }} subfolder
                                </p>
                            </div>

                            <div x-show="openMenu" @click.away="openMenu = false" x-cloak
                                class="absolute right-2 top-10 w-40 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-20 text-xs">
                                <button type="button" wire:click="openRenameModal({{ $folder->id }})" @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Ubah Nama
                                </button>
                                <button type="button" @click="$dispatch('open-share-modal', { type: 'folder', id: {{ $folder->id }} }); openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                    </svg>
                                    Bagikan
                                </button>
                                <button type="button" wire:click="openMoveModal({{ $folder->id }})" @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    Pindah Folder
                                </button>
                                <a href="{{ route('folders.download', $folder->id) }}" @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Unduh (.zip)
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <button type="button" wire:click="confirmDelete({{ $folder->id }})" @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-rose-600 hover:bg-rose-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Hapus ke Sampah
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-xs">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase font-semibold">
                                <th class="px-4 py-2.5">Nama</th>
                                <th class="px-4 py-2.5">Isi</th>
                                <th class="px-4 py-2.5">Terakhir Diubah</th>
                                <th class="px-4 py-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($folders as $folder)
                                <tr class="hover:bg-gray-50/80 transition cursor-pointer" wire:click="navigateToFolder({{ $folder->id }})">
                                    <td class="px-4 py-3 flex items-center gap-2.5 font-medium text-gray-900">
                                        <div class="w-6 h-6 rounded flex items-center justify-center text-white"
                                            style="background-color: {{ $folder->color ?: '#3B82F6' }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            </svg>
                                        </div>
                                        <span>{{ $folder->name }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">
                                        {{ $folder->files_count }} file, {{ $folder->children_count }} subfolder
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">
                                        {{ $folder->updated_at?->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-1" @click.stop>
                                        <button type="button" @click="$dispatch('open-share-modal', { type: 'folder', id: {{ $folder->id }} })"
                                            class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded transition text-[11px] font-medium">Bagikan</button>
                                        <a href="{{ route('folders.download', $folder->id) }}"
                                            class="text-indigo-600 hover:text-indigo-800 px-2 py-1 rounded transition text-[11px] font-medium inline-flex items-center gap-1"
                                            title="Unduh (.zip)">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            <span>ZIP</span>
                                        </a>
                                        <button type="button" wire:click="openRenameModal({{ $folder->id }})"
                                            class="text-gray-500 hover:text-brand-600 px-2 py-1 rounded transition">Ubah</button>
                                        <button type="button" wire:click="openMoveModal({{ $folder->id }})"
                                            class="text-gray-500 hover:text-brand-600 px-2 py-1 rounded transition">Pindah</button>
                                        <button type="button" wire:click="confirmDelete({{ $folder->id }})"
                                            class="text-rose-600 hover:text-rose-700 px-2 py-1 rounded transition">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <!-- Section: Files List -->
    @if ($files->isNotEmpty())
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Berkas ({{ $files->count() }})</h2>

            @if ($viewMode === 'grid')
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5">
                    @foreach ($files as $file)
                        <div class="group relative bg-white p-3 rounded-lg border border-gray-200 hover:border-brand-400 hover:shadow-md transition flex flex-col justify-between"
                            x-data="{ openMenu: false }">
                            <div>
                                <!-- File Icon / Thumbnail -->
                                <div class="relative w-full h-24 rounded-md bg-gray-50 mb-2 flex items-center justify-center overflow-hidden border border-gray-100">
                                    @if ($file->isImage())
                                        <span class="text-xs text-brand-600 font-semibold uppercase">Gambar</span>
                                    @elseif ($file->isPdf())
                                        <div class="text-rose-500">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @elseif ($file->isArchive())
                                        <div class="text-amber-500">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="text-gray-400">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                    @endif

                                    <!-- Favorite Button -->
                                    <button type="button" wire:click="toggleFavorite({{ $file->id }})"
                                        class="absolute top-1 left-1 p-1 rounded-md text-xs transition {{ $file->is_favorite ? 'text-amber-500' : 'text-gray-300 hover:text-amber-400' }}">
                                        ★
                                    </button>

                                    <!-- Context Menu Button -->
                                    <button type="button" @click.stop="openMenu = !openMenu"
                                        class="absolute top-1 right-1 p-1 rounded-md text-gray-400 hover:text-gray-700 bg-white/80 backdrop-blur-xs transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <p class="text-xs font-semibold text-gray-900 truncate" title="{{ $file->original_name }}">
                                    {{ $file->original_name }}
                                </p>
                                <p class="text-[11px] text-gray-400 mt-0.5 flex items-center justify-between">
                                    <span>{{ $file->formatted_size }}</span>
                                    <span>{{ strtoupper($file->extension) }}</span>
                                </p>
                            </div>

                            <!-- Dropdown Menu Popover -->
                            <div x-show="openMenu" @click.away="openMenu = false" x-cloak
                                class="absolute right-2 top-8 w-40 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-20 text-xs">
                                <a href="{{ route('files.download', $file->id) }}" @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Unduh Berkas
                                </a>
                                <button type="button" wire:click="openFileRenameModal({{ $file->id }})" @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Ubah Nama
                                </button>
                                <button type="button" @click="$dispatch('open-share-modal', { type: 'file', id: {{ $file->id }} }); openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                    </svg>
                                    Bagikan
                                </button>
                                <button type="button" wire:click="openFileMoveModal({{ $file->id }})" @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    Pindah Folder
                                </button>
                                <div class="border-t border-gray-100 my-1"></div>
                                <button type="button" wire:click="confirmDeleteFile({{ $file->id }})" @click="openMenu = false"
                                    class="w-full text-left px-3 py-1.5 text-rose-600 hover:bg-rose-50 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Hapus ke Sampah
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-xs">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase font-semibold">
                                <th class="px-4 py-2.5">Nama Berkas</th>
                                <th class="px-4 py-2.5">Ukuran</th>
                                <th class="px-4 py-2.5">Tipe MIME</th>
                                <th class="px-4 py-2.5">Diunggah</th>
                                <th class="px-4 py-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($files as $file)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3 flex items-center gap-2.5 font-medium text-gray-900">
                                        <span class="text-base">📄</span>
                                        <span>{{ $file->original_name }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $file->formatted_size }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $file->mime_type }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $file->created_at?->diffForHumans() }}</td>
                                    <td class="px-4 py-3 text-right space-x-1">
                                        <button type="button" @click="$dispatch('open-share-modal', { type: 'file', id: {{ $file->id }} })"
                                            class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded transition text-[11px] font-medium">Bagikan</button>
                                        <a href="{{ route('files.download', $file->id) }}"
                                            class="text-brand-600 hover:text-brand-700 px-2 py-1 rounded transition">Unduh</a>
                                        <button type="button" wire:click="openFileRenameModal({{ $file->id }})"
                                            class="text-gray-500 hover:text-brand-600 px-2 py-1 rounded transition">Ubah</button>
                                        <button type="button" wire:click="openFileMoveModal({{ $file->id }})"
                                            class="text-gray-500 hover:text-brand-600 px-2 py-1 rounded transition">Pindah</button>
                                        <button type="button" wire:click="confirmDeleteFile({{ $file->id }})"
                                            class="text-rose-600 hover:text-rose-700 px-2 py-1 rounded transition">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <!-- Empty State (UIUX_BRIEF §10) -->
    @if ($folders->isEmpty() && $files->isEmpty())
        <div class="bg-white rounded-lg border border-dashed border-gray-300 p-12 text-center">
            <div class="w-12 h-12 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 mb-1">Belum ada file di sini</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto mb-5">
                Folder ini masih kosong. Mulai dengan membuat folder baru atau mengunggah berkas.
            </p>
            <div class="flex items-center justify-center gap-3">
                <button type="button" wire:click="openCreateModal"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium text-xs transition">
                    Buat Folder
                </button>
                <button type="button" wire:click="openUploadModal"
                    class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium text-xs shadow-sm transition">
                    Upload Berkas
                </button>
            </div>
        </div>
    @endif

    <!-- Modal: Upload Berkas (Multi-file & Drag-and-drop Progress Bar) -->
    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4" x-cloak>
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.away="$wire.resetModals()"
                x-data="{ isDropping: false }">
                <h3 class="text-base font-bold text-gray-900 mb-2">Unggah Berkas</h3>
                <p class="text-xs text-gray-500 mb-4">
                    Unggah berkas ke: <strong class="text-gray-800">{{ $currentFolder ? $currentFolder->name : 'Semua File (Root)' }}</strong>
                </p>

                <form wire:submit="uploadFiles">
                    <!-- Drop Zone -->
                    <div class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition mb-4"
                        :class="isDropping ? 'border-brand-500 bg-brand-50/50' : 'border-gray-300 hover:border-brand-400 bg-gray-50/50'"
                        @dragover.prevent="isDropping = true"
                        @dragleave.prevent="isDropping = false"
                        @drop="isDropping = false">
                        <input type="file" wire:model="uploads" multiple id="fileUploadInput" class="hidden">
                        <label for="fileUploadInput" class="cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-gray-800">
                                Klik untuk memilih berkas <span class="font-normal text-gray-500">atau seret ke sini</span>
                            </p>
                            <p class="text-[11px] text-gray-400 mt-1">
                                Dokumen, PDF, Gambar, Arsip Zip hingga {{ round(config('cloudcampus.max_upload_size_kb')/1024, 0) }} MB
                            </p>
                        </label>
                    </div>

                    <!-- Progress Bar Saat Mengunggah -->
                    <div wire:loading wire:target="uploads" class="mb-4">
                        <div class="flex items-center justify-between text-xs text-brand-600 font-medium mb-1">
                            <span>Memproses berkas...</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-brand-600 h-2 rounded-full animate-pulse w-full"></div>
                        </div>
                    </div>

                    <!-- Preview Berkas Terpilih -->
                    @if (!empty($uploads))
                        <div class="mb-4 max-h-36 overflow-y-auto divide-y divide-gray-100 border border-gray-200 rounded-lg p-2 text-xs">
                            @foreach ($uploads as $file)
                                <div class="py-1.5 flex items-center justify-between">
                                    <span class="truncate font-medium text-gray-800 max-w-[200px]">{{ $file->getClientOriginalName() }}</span>
                                    <span class="text-gray-400 text-[11px]">{{ round($file->getSize() / 1024, 1) }} KB</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @error('uploads')
                        <p class="text-[11px] text-rose-600 mb-3">{{ $message }}</p>
                    @enderror
                    @error('uploads.*')
                        <p class="text-[11px] text-rose-600 mb-3">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-end space-x-2.5">
                        <button type="button" wire:click="resetModals"
                            class="px-3.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-xs font-medium text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition disabled:opacity-50">
                            Mulai Unggah ({{ count($uploads) }})
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal: Buat Folder Baru -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4" x-cloak>
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6" @click.away="$wire.resetModals()">
                <h3 class="text-base font-bold text-gray-900 mb-3">Buat Folder Baru</h3>

                <form wire:submit="createFolder">
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Folder</label>
                        <input type="text" wire:model="newFolderName" autofocus placeholder="Misal: Tugas Akhir"
                            class="w-full px-3 py-2 rounded-lg border @error('newFolderName') border-rose-500 @else border-gray-300 @enderror text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                        @error('newFolderName')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Warna Label</label>
                        <div class="flex items-center space-x-2">
                            @foreach (['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6B7280'] as $c)
                                <button type="button" wire:click="$set('newFolderColor', '{{ $c }}')"
                                    class="w-6 h-6 rounded-full transition {{ $newFolderColor === $c ? 'ring-2 ring-offset-2 ring-gray-900 scale-110' : 'hover:scale-105' }}"
                                    style="background-color: {{ $c }}"></button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2.5">
                        <button type="button" wire:click="resetModals"
                            class="px-3.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-xs font-medium text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal: Ubah Nama Folder -->
    @if ($showRenameModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4" x-cloak>
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6" @click.away="$wire.resetModals()">
                <h3 class="text-base font-bold text-gray-900 mb-3">Ubah Nama Folder</h3>

                <form wire:submit="renameFolder">
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Baru</label>
                        <input type="text" wire:model="editFolderName" autofocus
                            class="w-full px-3 py-2 rounded-lg border @error('editFolderName') border-rose-500 @else border-gray-300 @enderror text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                        @error('editFolderName')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-2.5">
                        <button type="button" wire:click="resetModals"
                            class="px-3.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-xs font-medium text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition">
                            Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal: Ubah Nama Berkas -->
    @if ($showFileRenameModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4" x-cloak>
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6" @click.away="$wire.resetModals()">
                <h3 class="text-base font-bold text-gray-900 mb-3">Ubah Nama Berkas</h3>

                <form wire:submit="renameFile">
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Berkas</label>
                        <input type="text" wire:model="editFileName" autofocus
                            class="w-full px-3 py-2 rounded-lg border @error('editFileName') border-rose-500 @else border-gray-300 @enderror text-xs focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                        @error('editFileName')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-2.5">
                        <button type="button" wire:click="resetModals"
                            class="px-3.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-xs font-medium text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition">
                            Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal: Pindah Folder -->
    @if ($showMoveModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4" x-cloak>
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.away="$wire.resetModals()">
                <h3 class="text-base font-bold text-gray-900 mb-2">Pindahkan Folder</h3>
                <p class="text-xs text-gray-500 mb-4">Pilih folder tujuan:</p>

                <form wire:submit="moveFolder">
                    <div class="mb-4 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2 divide-y divide-gray-100 text-xs">
                        <label class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer {{ $targetParentId === null ? 'bg-brand-50 text-brand-700 font-semibold' : '' }}">
                            <input type="radio" wire:model="targetParentId" :value="null" name="targetParentId" class="text-brand-600">
                            <span>/ (Folder Utama / Root)</span>
                        </label>

                        @foreach ($folderTree as $node)
                            <label class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer {{ $targetParentId === $node['id'] ? 'bg-brand-50 text-brand-700 font-semibold' : '' }}">
                                <input type="radio" wire:model="targetParentId" value="{{ $node['id'] }}" name="targetParentId" class="text-brand-600">
                                <span>📁 {{ $node['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('targetParentId')
                        <p class="text-[11px] text-rose-600 mb-3">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-end space-x-2.5">
                        <button type="button" wire:click="resetModals"
                            class="px-3.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-xs font-medium text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition">
                            Pindahkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal: Pindah Berkas -->
    @if ($showFileMoveModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4" x-cloak>
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.away="$wire.resetModals()">
                <h3 class="text-base font-bold text-gray-900 mb-2">Pindahkan Berkas</h3>
                <p class="text-xs text-gray-500 mb-4">Pilih folder tujuan untuk berkas ini:</p>

                <form wire:submit="moveFile">
                    <div class="mb-4 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2 divide-y divide-gray-100 text-xs">
                        <label class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer {{ $targetFileFolderId === null ? 'bg-brand-50 text-brand-700 font-semibold' : '' }}">
                            <input type="radio" wire:model="targetFileFolderId" :value="null" name="targetFileFolderId" class="text-brand-600">
                            <span>/ (Folder Utama / Root)</span>
                        </label>

                        @foreach ($folderTree as $node)
                            <label class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer {{ $targetFileFolderId === $node['id'] ? 'bg-brand-50 text-brand-700 font-semibold' : '' }}">
                                <input type="radio" wire:model="targetFileFolderId" value="{{ $node['id'] }}" name="targetFileFolderId" class="text-brand-600">
                                <span>📁 {{ $node['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('targetFileFolderId')
                        <p class="text-[11px] text-rose-600 mb-3">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-end space-x-2.5">
                        <button type="button" wire:click="resetModals"
                            class="px-3.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-xs font-medium text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition">
                            Pindahkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal: Konfirmasi Hapus Folder -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4" x-cloak>
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 text-center" @click.away="$wire.resetModals()">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Hapus Folder Ini?</h3>
                <p class="text-xs text-gray-500 mb-5 leading-relaxed">
                    Folder "<strong>{{ $folderToDeleteName }}</strong>" beserta seluruh isinya akan dipindahkan ke Tempat Sampah.
                </p>

                <div class="flex items-center justify-center space-x-2.5">
                    <button type="button" wire:click="resetModals"
                        class="px-4 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-xs font-medium text-gray-700 transition">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteFolder"
                        class="px-4 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition">
                        Hapus ke Sampah
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Konfirmasi Hapus Berkas -->
    @if ($showDeleteFileModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-900/50 backdrop-blur-xs p-4" x-cloak>
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 text-center" @click.away="$wire.resetModals()">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Hapus Berkas Ini?</h3>
                <p class="text-xs text-gray-500 mb-5 leading-relaxed">
                    Berkas "<strong>{{ $fileToDeleteName }}</strong>" akan dipindahkan ke Tempat Sampah (dapat dipulihkan dalam 30 hari).
                </p>

                <div class="flex items-center justify-center space-x-2.5">
                    <button type="button" wire:click="resetModals"
                        class="px-4 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-xs font-medium text-gray-700 transition">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteFile"
                        class="px-4 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition">
                        Hapus ke Sampah
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Livewire Share Modal -->
    <livewire:share-modal />
</div>
