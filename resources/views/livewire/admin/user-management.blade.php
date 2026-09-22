<div>
    <!-- Notifikasi Sukses / Error -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
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

    @if (session()->has('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Header & Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola akun mahasiswa, dosen, dan staf serta sesuaikan alokasi batas kuota individual.</p>
        </div>
    </div>

    <!-- Toolbar Pencarian & Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs mb-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <!-- Input Search -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, email, atau NIM/NIP..."
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition" />
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Tipe Akun -->
            <select wire:model.live="accountType" class="text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="all">Semua Tipe Akun</option>
                <option value="academic">Sivitas Akademika</option>
                <option value="public">Pengguna Publik</option>
            </select>

            <!-- Filter Status -->
            <select wire:model.live="status" class="text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="all">Semua Status</option>
                <option value="active">Aktif Normal</option>
                <option value="suspended">Ditangguhkan</option>
                <option value="near_quota">Mendekati Kuota (&ge; 85%)</option>
            </select>
        </div>
    </div>

    <!-- Tabel Pengguna -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6">Pengguna</th>
                        <th class="py-4 px-4">Tipe Akun</th>
                        <th class="py-4 px-4">Role</th>
                        <th class="py-4 px-4">Pemakaian Kuota</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/50 transition">
                            <!-- Kolom Pengguna -->
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3.5">
                                    <div class="w-10 h-10 rounded-full {{ $user->isSuspended() ? 'bg-slate-200 text-slate-500' : 'bg-brand-100 text-brand-700' }} flex items-center justify-center font-bold text-sm flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <p class="font-bold text-slate-900 truncate">{{ $user->name }}</p>
                                            @if ($user->id === auth()->id())
                                                <span class="text-[10px] bg-slate-100 text-slate-600 font-bold px-1.5 py-0.2 rounded">Anda</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                                        @if ($user->external_id)
                                            <p class="text-[11px] font-mono text-brand-600 mt-0.5">ID: {{ $user->external_id }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Tipe Akun -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $user->isAcademic() ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $user->isAcademic() ? 'Akademik' : 'Publik' }}
                                </span>
                            </td>

                            <!-- Kolom Role -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->roles as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $role->name === 'super-admin' ? 'bg-purple-100 text-purple-800' : ($role->name === 'admin-kampus' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-600') }}">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Kolom Kuota -->
                            <td class="py-4 px-4 w-48">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-bold text-slate-800">{{ app(\App\Services\QuotaService::class)->formatBytes($user->used_bytes) }}</span>
                                    <span class="text-slate-400">/ {{ app(\App\Services\QuotaService::class)->formatBytes($user->quota_bytes) }}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300 {{ $user->quotaUsagePercentage() >= 90 ? 'bg-rose-500' : ($user->quotaUsagePercentage() >= 75 ? 'bg-amber-500' : 'bg-brand-600') }}"
                                         style="width: {{ min(100, $user->quotaUsagePercentage()) }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">{{ $user->quotaUsagePercentage() }}% terpakai</span>
                            </td>

                            <!-- Kolom Status -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if ($user->isSuspended())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                        Ditangguhkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Aktif
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2">
                                    <button wire:click="openQuotaModal({{ $user->id }})"
                                            class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition" title="Sesuaikan Kuota">
                                        Ubah Kuota
                                    </button>

                                    @if ($user->id !== auth()->id())
                                        <button wire:click="toggleSuspend({{ $user->id }})"
                                                wire:confirm="Apakah Anda yakin ingin {{ $user->isSuspended() ? 'mengaktifkan kembali' : 'menangguhkan' }} akun {{ $user->name }}?"
                                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $user->isSuspended() ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                                            {{ $user->isSuspended() ? 'Aktifkan' : 'Tangguhkan' }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Tidak ada data pengguna yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Override Kuota -->
    @if ($showQuotaModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-scale-in" @click.away="$wire.closeQuotaModal()">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7zm0 5h16" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Ubah Kuota Pengguna</h3>
                            <p class="text-xs text-slate-400">Alokasikan ruang khusus untuk akun ini.</p>
                        </div>
                    </div>
                    <button wire:click="closeQuotaModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="py-5 space-y-4">
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
                        <div class="flex justify-between mb-1">
                            <span class="text-slate-500">Nama Pengguna:</span>
                            <span class="font-bold text-slate-800">{{ $selectedUserName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kuota Saat Ini:</span>
                            <span class="font-bold text-brand-600">{{ $selectedUserCurrentQuota }}</span>
                        </div>
                    </div>

                    <!-- Preset Kuota Cepat -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Preset Kuota Cepat</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ([5, 10, 25, 50] as $preset)
                                <button type="button" wire:click="setQuotaPreset({{ $preset }})"
                                        class="py-2 text-xs font-bold rounded-xl border transition {{ $customQuotaGb == $preset ? 'bg-brand-600 text-white border-brand-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                    {{ $preset }} GB
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Input Kustom GB -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Batas Kuota Baru (GB)</label>
                        <div class="relative">
                            <input type="number" step="0.5" min="0.1" wire:model="customQuotaGb"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" />
                            <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-xs font-bold text-slate-400 pointer-events-none">GB</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <button wire:click="closeQuotaModal" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button wire:click="saveQuota" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition">
                        Simpan Kuota
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
