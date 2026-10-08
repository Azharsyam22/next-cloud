<!-- Mobile Bottom Navigation Bar (UIUX_BRIEF §8) -->
<nav class="md:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-gray-200 z-40 px-2 py-1 shadow-lg">
    <div class="grid grid-cols-5 gap-1 text-[10px] font-semibold text-center">
        <!-- Drive Saya -->
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center py-1 px-1 rounded-lg transition {{ request()->routeIs('dashboard') ? 'text-brand-600 bg-brand-50 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
            </svg>
            <span class="truncate w-full">Drive</span>
        </a>

        <!-- Dibagikan dengan Saya -->
        <a href="{{ route('shares.with-me') }}"
           class="flex flex-col items-center py-1 px-1 rounded-lg transition {{ request()->routeIs('shares.with-me') ? 'text-brand-600 bg-brand-50 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span class="truncate w-full">Dibagikan</span>
        </a>

        <!-- Tautan Saya -->
        <a href="{{ route('shares.mine') }}"
           class="flex flex-col items-center py-1 px-1 rounded-lg transition {{ request()->routeIs('shares.mine') ? 'text-brand-600 bg-brand-50 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
            </svg>
            <span class="truncate w-full">Tautan</span>
        </a>

        <!-- Kuota Penyimpanan -->
        <a href="{{ route('storage.index') }}"
           class="flex flex-col items-center py-1 px-1 rounded-lg transition {{ request()->routeIs('storage.index') ? 'text-brand-600 bg-brand-50 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
            </svg>
            <span class="truncate w-full">Kuota</span>
        </a>

        <!-- Tempat Sampah -->
        <a href="{{ route('trash') }}"
           class="flex flex-col items-center py-1 px-1 rounded-lg transition {{ request()->routeIs('trash') ? 'text-rose-600 bg-rose-50 font-bold' : 'text-gray-500 hover:text-gray-900' }} relative">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            <span class="truncate w-full">Sampah</span>
        </a>
    </div>
</nav>
<div class="md:hidden h-16 w-full"></div>
