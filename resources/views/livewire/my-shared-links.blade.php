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
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Tautan Berbagi Saya</h1>
                <p class="text-xs text-gray-500">Kelola semua tautan berbagi publik dan akses privat yang pernah Anda buat.</p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Filter -->
            <div class="flex rounded-xl border border-gray-200 bg-gray-50 p-0.5 text-xs font-medium">
                <button wire:click="$set('filter', 'all')" class="px-3 py-1.5 rounded-lg transition {{ $filter === 'all' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Semua</button>
                <button wire:click="$set('filter', 'public')" class="px-3 py-1.5 rounded-lg transition {{ $filter === 'public' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Publik</button>
                <button wire:click="$set('filter', 'private')" class="px-3 py-1.5 rounded-lg transition {{ $filter === 'private' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900' }}">Privat</button>
            </div>
        </div>
    </div>

    @if ($shares->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-800 mb-1">Belum Ada Tautan Berbagi</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto mb-6">Anda belum membagikan berkas atau folder apa pun. Klik menu Bagikan pada item di Drive Anda untuk membuat tautan.</p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <span>Buka Drive Saya</span>
            </a>
        </div>
    @else
        <!-- Shares Table -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ copiedId: null }">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="py-3 px-4">Item yang Dibagikan</th>
                        <th class="py-3 px-4">Tipe Akses</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Izin</th>
                        <th class="py-3 px-4 hidden md:table-cell">Status & Masa Berlaku</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @foreach ($shares as $share)
                        @php
                            $isFolder = $share->shareable instanceof \App\Models\Folder;
                            $name = $isFolder ? ($share->shareable?->name ?? 'Folder dihapus') : ($share->shareable?->original_name ?? 'Berkas dihapus');
                            $url = url('/s/' . $share->token);
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition">
                            <!-- Item Info -->
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

                            <!-- Access Type -->
                            <td class="py-3 px-4">
                                @if ($share->isPublic())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">
                                        Publik (Tautan)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700">
                                        Privat: {{ $share->sharedWithUser?->name ?? 'User' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Permission -->
                            <td class="py-3 px-4 hidden sm:table-cell text-gray-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $share->canDownload() ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $share->canDownload() ? 'Unduh' : 'Lihat Saja' }}
                                </span>
                            </td>

                            <!-- Status & Expiry -->
                            <td class="py-3 px-4 hidden md:table-cell">
                                @if (! $share->is_active)
                                    <span class="text-rose-600 font-semibold text-[11px]">Dinonaktifkan</span>
                                @elseif ($share->isExpired())
                                    <span class="text-amber-600 font-semibold text-[11px]">Kedaluwarsa</span>
                                @else
                                    <span class="text-emerald-600 font-medium text-[11px]">
                                        {{ $share->expires_at ? 'Hingga ' . $share->expires_at->format('d M Y') : 'Tanpa batas' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right space-x-1">
                                <!-- Salin Tautan -->
                                @if ($share->isValid())
                                    <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $url }}'); copiedId = {{ $share->id }}; setTimeout(() => copiedId = null, 2500)"
                                        class="p-1.5 text-brand-600 hover:text-brand-800 hover:bg-brand-50 rounded-lg transition inline-flex items-center"
                                        title="Salin Tautan">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                        <span class="text-[11px]" x-text="copiedId === {{ $share->id }} ? 'Tersalin!' : 'Salin'">Salin</span>
                                    </button>
                                @endif

                                <!-- Toggle Aktif / Nonaktif -->
                                @if ($share->is_active)
                                    <button type="button" wire:click="revoke({{ $share->id }})"
                                        class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition inline-flex items-center"
                                        title="Nonaktifkan Tautan">
                                        <span class="text-[11px]">Nonaktifkan</span>
                                    </button>
                                @else
                                    <button type="button" wire:click="activate({{ $share->id }})"
                                        class="p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition inline-flex items-center"
                                        title="Aktifkan Kembali">
                                        <span class="text-[11px]">Aktifkan</span>
                                    </button>
                                @endif

                                <!-- Hapus Permanen -->
                                <button type="button" wire:click="delete({{ $share->id }})"
                                    wire:confirm="Yakin ingin menghapus record tautan berbagi ini?"
                                    class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition inline-flex items-center"
                                    title="Hapus Record">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
