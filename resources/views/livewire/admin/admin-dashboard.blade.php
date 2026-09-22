<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Dasbor Administrasi Kampus</h1>
            <p class="text-sm text-slate-500 mt-1">Pemantauan kapasitas penyimpanan server, aktivitas pengguna, dan metrik sistem secara real-time.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.users') }}" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Kelola Pengguna</span>
            </a>
            <a href="{{ route('admin.logs') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold shadow-xs transition flex items-center space-x-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Log Aktivitas</span>
            </a>
        </div>
    </div>

    <!-- 4 Kartu Metrik Utama -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Kartu 1: Total Pengguna -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pengguna</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-black text-slate-900">{{ number_format($stats['total_users']) }}</span>
                @if ($stats['suspended_users'] > 0)
                    <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                        {{ $stats['suspended_users'] }} ditangguhkan
                    </span>
                @endif
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-50">
                <span>Akademik: <strong class="text-slate-800">{{ $stats['academic_users'] }}</strong></span>
                <span>Publik: <strong class="text-slate-800">{{ $stats['public_users'] }}</strong></span>
            </div>
        </div>

        <!-- Kartu 2: Penyimpanan Server -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Storage Terpakai</span>
                <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7zm0 5h16" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-black text-slate-900">{{ $stats['formatted_total_used'] }}</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-md {{ $stats['storage_percentage'] >= 90 ? 'bg-rose-100 text-rose-700' : 'bg-violet-100 text-violet-700' }}">
                    {{ $stats['storage_percentage'] }}%
                </span>
            </div>
            <div class="mt-3 text-xs text-slate-500 pt-3 border-t border-slate-50 flex items-center justify-between">
                <span>Total Dialokasi:</span>
                <strong class="text-slate-800">{{ $stats['formatted_total_allocated'] }}</strong>
            </div>
        </div>

        <!-- Kartu 3: Unggahan Hari Ini -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unggahan Hari Ini</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-black text-slate-900">{{ number_format($stats['uploads_today_count']) }}</span>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                    {{ $stats['formatted_uploads_today'] }}
                </span>
            </div>
            <div class="mt-3 text-xs text-slate-500 pt-3 border-t border-slate-50 flex items-center justify-between">
                <span>Total Berkas Sistem:</span>
                <strong class="text-slate-800">{{ number_format($stats['total_files']) }} Berkas</strong>
            </div>
        </div>

        <!-- Kartu 4: Berbagi & Kolaborasi -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tautan Berbagi Aktif</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-black text-slate-900">{{ number_format($stats['total_shares']) }}</span>
                <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                    Aktif
                </span>
            </div>
            <div class="mt-3 text-xs text-slate-500 pt-3 border-t border-slate-50 flex items-center justify-between">
                <span>Total Folder:</span>
                <strong class="text-slate-800">{{ number_format($stats['total_folders']) }} Folder</strong>
            </div>
        </div>
    </div>

    <!-- 2 Kolom: Pengguna Mendekati Kuota & Log Aktivitas Terkini -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Kolom Kiri: Pengguna Mendekati Kuota -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Perhatian Kuota Pengguna</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pengguna dengan penggunaan &ge; 85%.</p>
                </div>
                <a href="{{ route('admin.users', ['status' => 'near_quota']) }}" class="text-xs font-semibold text-brand-600 hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            @if ($stats['users_near_quota']->isEmpty())
                <div class="text-center py-10 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                    <svg class="w-10 h-10 text-emerald-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs font-semibold text-slate-700">Semua Kuota Aman</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada pengguna yang mendekati batas kuota.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($stats['users_near_quota'] as $userNear)
                        <div class="p-3.5 rounded-2xl border border-slate-100 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold text-sm text-slate-900 truncate" title="{{ $userNear->name }}">
                                    {{ $userNear->name }}
                                </span>
                                <span class="text-xs font-bold {{ $userNear->quotaUsagePercentage() >= 98 ? 'text-rose-600' : 'text-amber-600' }}">
                                    {{ $userNear->quotaUsagePercentage() }}%
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-2">
                                <div class="h-full {{ $userNear->quotaUsagePercentage() >= 98 ? 'bg-rose-500' : 'bg-amber-500' }} rounded-full"
                                     style="width: {{ min(100, $userNear->quotaUsagePercentage()) }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>{{ app(\App\Services\QuotaService::class)->formatBytes($userNear->used_bytes) }} dari {{ app(\App\Services\QuotaService::class)->formatBytes($userNear->quota_bytes) }}</span>
                                <a href="{{ route('admin.users', ['search' => $userNear->email]) }}" class="font-semibold text-brand-600 hover:underline">
                                    Kelola &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Log Aktivitas Sistem Terkini -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Aktivitas Sistem Terkini</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Riwayat audit log real-time dari seluruh pengguna dan sistem.</p>
                </div>
                <a href="{{ route('admin.logs') }}" class="text-xs font-semibold text-brand-600 hover:underline">
                    Buka Log Lengkap &rarr;
                </a>
            </div>

            @if ($stats['recent_activities']->isEmpty())
                <div class="text-center py-12 text-slate-400 text-sm">
                    Belum ada riwayat aktivitas tercatat.
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($stats['recent_activities'] as $log)
                        <div class="py-3 flex items-start justify-between gap-4 text-xs">
                            <div class="flex items-start space-x-3 min-w-0">
                                <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold uppercase tracking-wider
                                    @if(str_contains($log->action, 'delete') || str_contains($log->action, 'suspend')) bg-rose-50 text-rose-700
                                    @elseif(str_contains($log->action, 'upload') || str_contains($log->action, 'create')) bg-emerald-50 text-emerald-700
                                    @elseif(str_contains($log->action, 'share')) bg-amber-50 text-amber-700
                                    @else bg-blue-50 text-blue-700 @endif flex-shrink-0 mt-0.5">
                                    {{ $log->action }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-slate-800 font-medium truncate">
                                        {{ $log->description ?: $log->action }}
                                    </p>
                                    <p class="text-slate-400 text-[11px] mt-0.5">
                                        Oleh: <strong class="text-slate-600">{{ $log->user ? $log->user->name : 'Sistem' }}</strong> &bull; IP: {{ $log->ip_address ?: '-' }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-slate-400 whitespace-nowrap text-[11px]">
                                {{ $log->created_at?->diffForHumans() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
