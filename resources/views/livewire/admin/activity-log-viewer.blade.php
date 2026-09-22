<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Audit Trail & Log Aktivitas</h1>
            <p class="text-sm text-slate-500 mt-0.5">Rekaman riwayat lengkap seluruh operasi berkas, otentikasi, dan perubahan konfigurasi sistem.</p>
        </div>
    </div>

    <!-- Toolbar Pencarian & Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs mb-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari deskripsi, nama user, atau alamat IP..."
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition" />
        </div>

        <!-- Filter Aksi -->
        <div>
            <select wire:model.live="action" class="text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="all">Semua Tipe Aksi</option>
                @foreach ($availableActions as $act)
                    <option value="{{ $act }}">{{ $act }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tabel Log Aktivitas -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6">Waktu</th>
                        <th class="py-4 px-4">Aksi</th>
                        <th class="py-4 px-4">Pengguna</th>
                        <th class="py-4 px-4">Deskripsi Aktivitas</th>
                        <th class="py-4 px-4">Alamat IP</th>
                        <th class="py-4 px-6 text-right">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition">
                            <!-- Waktu -->
                            <td class="py-4 px-6 whitespace-nowrap text-xs text-slate-500">
                                <span class="font-bold text-slate-800 block">{{ $log->created_at?->format('d M Y') }}</span>
                                <span class="text-slate-400 font-mono text-[11px]">{{ $log->created_at?->format('H:i:s') }}</span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full font-mono text-xs font-bold uppercase tracking-wider
                                    @if(str_contains($log->action, 'delete') || str_contains($log->action, 'suspend')) bg-rose-50 text-rose-700
                                    @elseif(str_contains($log->action, 'upload') || str_contains($log->action, 'create')) bg-emerald-50 text-emerald-700
                                    @elseif(str_contains($log->action, 'share')) bg-amber-50 text-amber-700
                                    @else bg-blue-50 text-blue-700 @endif">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <!-- Pengguna -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if ($log->user)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-xs text-slate-800">{{ $log->user->name }}</p>
                                            <p class="text-[11px] text-slate-400">{{ $log->user->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Sistem Otomatis</span>
                                @endif
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-4 px-4">
                                <p class="text-xs text-slate-800 font-medium max-w-md truncate" title="{{ $log->description }}">
                                    {{ $log->description ?: '-' }}
                                </p>
                            </td>

                            <!-- IP Address -->
                            <td class="py-4 px-4 whitespace-nowrap text-xs font-mono text-slate-500">
                                {{ $log->ip_address ?: '-' }}
                            </td>

                            <!-- Aksi Detail -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <button wire:click="viewDetails({{ $log->id }})"
                                        class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                                    Lihat Meta
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Tidak ada log aktivitas yang cocok dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Detail Metadata -->
    @if ($showDetailModal && $selectedLogDetails)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 animate-scale-in" @click.away="$wire.closeDetailModal()">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 uppercase">
                            {{ $selectedLogDetails['action'] }}
                        </span>
                        <h3 class="font-bold text-slate-900 text-base mt-1.5">Rincian Audit Log #{{ $selectedLogDetails['id'] }}</h3>
                    </div>
                    <button wire:click="closeDetailModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="py-4 space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 font-semibold block mb-0.5">Deskripsi:</span>
                        <p class="font-bold text-slate-800">{{ $selectedLogDetails['description'] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-slate-400 font-semibold block mb-0.5">Pengguna:</span>
                            <span class="font-bold text-slate-800">{{ $selectedLogDetails['user_name'] }}</span>
                            <span class="text-slate-400 block text-[11px]">{{ $selectedLogDetails['user_email'] }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl">
                            <span class="text-slate-400 font-semibold block mb-0.5">Waktu Pencatatan:</span>
                            <span class="font-bold text-slate-800">{{ $selectedLogDetails['created_at'] }}</span>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-400 font-semibold block mb-0.5">Konteks Jaringan:</span>
                        <p class="text-slate-700 font-mono text-[11px]">IP: {{ $selectedLogDetails['ip_address'] ?: '-' }}</p>
                        <p class="text-slate-400 text-[10px] truncate mt-0.5">UA: {{ $selectedLogDetails['user_agent'] ?: '-' }}</p>
                    </div>

                    <!-- JSON Metadata -->
                    <div>
                        <span class="text-slate-400 font-semibold block mb-1">Payload Metadata (JSON):</span>
                        <div class="bg-slate-900 text-slate-100 p-4 rounded-xl font-mono text-[11px] max-h-48 overflow-y-auto">
                            @if (! empty($selectedLogDetails['meta']))
                                <pre>{{ json_encode($selectedLogDetails['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            @else
                                <span class="text-slate-500 italic">Tidak ada payload tambahan.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button wire:click="closeDetailModal" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
