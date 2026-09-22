<div>
    <!-- Notification Banner -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm transition">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- Toolbar Header -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Tempat Sampah</h1>
                <p class="text-xs text-gray-500">Item di sini dapat dipulihkan atau dihapus secara permanen untuk membebaskan kuota.</p>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Search -->
            <div class="relative flex-1 sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari di sampah..." class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>

            <!-- Filter Buttons -->
            <div class="flex rounded-xl border border-gray-200 bg-gray-50 p-0.5 text-xs font-medium">
                <button wire:click="$set('filter', 'all')" class="px-3 py-1.5 rounded-lg transition {{ $filter === 'all' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Semua</button>
                <button wire:click="$set('filter', 'folders')" class="px-3 py-1.5 rounded-lg transition {{ $filter === 'folders' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Folder</button>
                <button wire:click="$set('filter', 'files')" class="px-3 py-1.5 rounded-lg transition {{ $filter === 'files' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Berkas</button>
            </div>

            <!-- Empty Trash Button -->
            @if ($totalCount > 0)
                <button
                    wire:click="emptyTrash"
                    wire:confirm="PERINGATAN: Apakah Anda yakin ingin mengosongkan seluruh tempat sampah? Semua berkas fisik akan dihapus permanen dari server dan tidak dapat dikembalikan!"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span>Kosongkan Sampah</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Empty State -->
    @if ($folders->isEmpty() && $files->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-800 mb-1">Tempat Sampah Bersih</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto mb-6">Tidak ada berkas atau folder yang dihapus. Berkas yang Anda buang akan tampil di sini.</p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke Drive Saya</span>
            </a>
        </div>
    @else
        <!-- Trashed Items Table / List -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="py-3 px-4">Nama</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Ukuran</th>
                        <th class="py-3 px-4 hidden md:table-cell">Waktu Dihapus</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    <!-- Trashed Folders -->
                    @foreach ($folders as $folder)
                        <tr class="hover:bg-gray-50/80 transition group">
                            <td class="py-3 px-4 flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: {{ $folder->color ? $folder->color.'20' : '#e0e7ff' }}; color: {{ $folder->color ?? '#4f46e5' }}">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                    </svg>
                                </div>
                                <div class="truncate">
                                    <span class="font-medium text-gray-900 block truncate">{{ $folder->name }}</span>
                                    <span class="text-[10px] text-gray-400">Folder</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 hidden sm:table-cell text-gray-500">—</td>
                            <td class="py-3 px-4 hidden md:table-cell text-gray-500">
                                {{ $folder->deleted_at?->diffForHumans() }}
                            </td>
                            <td class="py-3 px-4 text-right space-x-1">
                                <button
                                    wire:click="restoreFolder({{ $folder->id }})"
                                    title="Pulihkan Folder"
                                    class="p-1.5 text-brand-600 hover:text-brand-800 hover:bg-brand-50 rounded-lg transition inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    <span class="text-[11px] font-medium hidden sm:inline">Pulihkan</span>
                                </button>
                                <button
                                    wire:click="forceDeleteFolder({{ $folder->id }})"
                                    wire:confirm="Yakin ingin menghapus permanen folder '{{ $folder->name }}' beserta seluruh file di dalamnya?"
                                    title="Hapus Permanen"
                                    class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span class="text-[11px] font-medium hidden sm:inline">Hapus Permanen</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach

                    <!-- Trashed Files -->
                    @foreach ($files as $file)
                        <tr class="hover:bg-gray-50/80 transition group">
                            <td class="py-3 px-4 flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0 text-gray-500 font-bold text-[10px] uppercase">
                                    {{ strtoupper($file->extension ?? 'FILE') }}
                                </div>
                                <div class="truncate">
                                    <span class="font-medium text-gray-900 block truncate">{{ $file->original_name }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $file->mime_type }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 hidden sm:table-cell text-gray-600 font-medium">
                                {{ $file->formatted_size }}
                            </td>
                            <td class="py-3 px-4 hidden md:table-cell text-gray-500">
                                {{ $file->deleted_at?->diffForHumans() }}
                            </td>
                            <td class="py-3 px-4 text-right space-x-1">
                                <button
                                    wire:click="restoreFile({{ $file->id }})"
                                    title="Pulihkan Berkas"
                                    class="p-1.5 text-brand-600 hover:text-brand-800 hover:bg-brand-50 rounded-lg transition inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    <span class="text-[11px] font-medium hidden sm:inline">Pulihkan</span>
                                </button>
                                <button
                                    wire:click="forceDeleteFile({{ $file->id }})"
                                    wire:confirm="Yakin ingin menghapus permanen berkas '{{ $file->original_name }}'? Berkas fisik akan dihapus dan kuota Anda akan dibebaskan."
                                    title="Hapus Permanen"
                                    class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span class="text-[11px] font-medium hidden sm:inline">Hapus Permanen</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
