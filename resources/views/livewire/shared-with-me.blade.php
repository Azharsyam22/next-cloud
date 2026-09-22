<div>
    <!-- Toolbar Header -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Dibagikan dengan Saya</h1>
                <p class="text-xs text-gray-500">Berkas dan folder yang dibagikan secara khusus oleh pengguna lain kepada Anda.</p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Search -->
            <div class="relative w-48 sm:w-60">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama item..." class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Filter Buttons -->
            <div class="flex rounded-xl border border-gray-200 bg-gray-50 p-0.5 text-xs font-medium">
                <button wire:click="$set('filter', 'all')" class="px-3 py-1.5 rounded-lg transition {{ $filter === 'all' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Semua</button>
                <button wire:click="$set('filter', 'folders')" class="px-3 py-1.5 rounded-lg transition {{ $filter === 'folders' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Folder</button>
                <button wire:click="$set('filter', 'files')" class="px-3 py-1.5 rounded-lg transition {{ $filter === 'files' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Berkas</button>
            </div>
        </div>
    </div>

    @if ($shares->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-800 mb-1">Belum Ada Berkas yang Dibagikan</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto mb-6">Ketika ada pengguna lain yang membagikan berkas atau folder privat ke alamat email Anda, item tersebut akan muncul di sini.</p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <span>Buka Drive Saya</span>
            </a>
        </div>
    @else
        <!-- Items Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="py-3 px-4">Nama Item</th>
                        <th class="py-3 px-4">Pemilik (Dibagikan Oleh)</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Izin</th>
                        <th class="py-3 px-4 hidden md:table-cell">Tanggal Berbagi</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @foreach ($shares as $share)
                        @php
                            $isFolder = $share->shareable instanceof \App\Models\Folder;
                            $name = $isFolder ? ($share->shareable?->name ?? 'Folder') : ($share->shareable?->original_name ?? 'Berkas');
                            $url = url('/s/' . $share->token);
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition">
                            <!-- Name -->
                            <td class="py-3 px-4 flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isFolder ? 'bg-indigo-50 text-indigo-600' : 'bg-brand-50 text-brand-600' }}">
                                    @if ($isFolder)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    @endif
                                </div>
                                <div class="truncate max-w-xs">
                                    <span class="font-medium text-gray-900 block truncate">{{ $name }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $isFolder ? 'Folder' : ($share->shareable?->formatted_size ?? '') }}</span>
                                </div>
                            </td>

                            <!-- Owner -->
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 font-bold text-[10px] flex items-center justify-center">
                                        {{ strtoupper(substr($share->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $share->user->name ?? 'Pengguna' }}</span>
                                </div>
                            </td>

                            <!-- Permission -->
                            <td class="py-3 px-4 hidden sm:table-cell text-gray-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $share->canDownload() ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $share->canDownload() ? 'Dapat Mengunduh' : 'Hanya Lihat' }}
                                </span>
                            </td>

                            <!-- Date Shared -->
                            <td class="py-3 px-4 hidden md:table-cell text-gray-500">
                                {{ $share->created_at->diffForHumans() }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right space-x-1">
                                <!-- Buka Preview -->
                                <a href="{{ $url }}" target="_blank"
                                    class="p-1.5 text-brand-600 hover:text-brand-800 hover:bg-brand-50 rounded-lg transition inline-flex items-center"
                                    title="Buka Pratinjau">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <span class="text-[11px] font-medium">Buka</span>
                                </a>

                                <!-- Unduh Jika Berizin -->
                                @if ($share->canDownload())
                                    <a href="{{ route('shares.public.download', $share->token) }}"
                                        class="p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition inline-flex items-center"
                                        title="Unduh Berkas">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span class="text-[11px] font-medium">Unduh</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
