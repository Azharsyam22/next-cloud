<div>
    @if ($isOpen)
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <!-- Modal Box -->
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-gray-200 overflow-hidden transform transition-all"
                @click.away="$wire.closeModal()"
                x-data="{ copied: false }">

                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center space-x-3 truncate pr-4">
                        <div class="w-9 h-9 rounded-xl {{ $type === 'folder' ? 'bg-indigo-50 text-indigo-600' : 'bg-brand-50 text-brand-600' }} flex items-center justify-center flex-shrink-0">
                            @if ($type === 'folder')
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            @endif
                        </div>
                        <div class="truncate">
                            <h3 class="text-sm font-bold text-gray-900 truncate">Bagikan: {{ $itemName }}</h3>
                            <p class="text-[11px] text-gray-500 capitalize">{{ $type }} • Kelola izin akses</p>
                        </div>
                    </div>

                    <button type="button" wire:click="closeModal" class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Flash Notice -->
                @if (session()->has('message'))
                    <div class="mx-6 mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ session('message') }}</span>
                    </div>
                @endif

                @error('recipient')
                    <div class="mx-6 mt-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                        {{ $message }}
                    </div>
                @enderror

                <!-- Tabs -->
                <div class="px-6 pt-3 flex space-x-4 border-b border-gray-100 text-xs font-semibold">
                    <button type="button" wire:click="$set('activeTab', 'link')"
                        class="pb-2.5 transition relative {{ $activeTab === 'link' ? 'text-brand-600' : 'text-gray-500 hover:text-gray-900' }}">
                        <span>Tautan Publik</span>
                        @if ($activeTab === 'link')
                            <span class="absolute bottom-0 inset-x-0 h-0.5 bg-brand-600 rounded-full"></span>
                        @endif
                    </button>
                    <button type="button" wire:click="$set('activeTab', 'people')"
                        class="pb-2.5 transition relative {{ $activeTab === 'people' ? 'text-brand-600' : 'text-gray-500 hover:text-gray-900' }}">
                        <span>Bagikan ke Orang</span>
                        @if ($activeTab === 'people')
                            <span class="absolute bottom-0 inset-x-0 h-0.5 bg-brand-600 rounded-full"></span>
                        @endif
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Tab 1: Tautan Publik -->
                    @if ($activeTab === 'link')
                        <div class="space-y-5">
                            <!-- Toggle Aktifkan Tautan -->
                            <div class="flex items-center justify-between p-3.5 rounded-xl border border-gray-200 bg-gray-50/50">
                                <div>
                                    <p class="text-xs font-semibold text-gray-900">Akses Siapa Saja yang Memiliki Tautan</p>
                                    <p class="text-[11px] text-gray-500">Tautan acak aman tanpa memerlukan login.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="isPublicActive" wire:change="togglePublicShare" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                                </label>
                            </div>

                            @if ($isPublicActive && $publicLinkUrl)
                                <!-- Link Input & Copy -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-1.5">Tautan Berbagi</label>
                                    <div class="flex rounded-xl shadow-xs overflow-hidden border border-gray-300">
                                        <input type="text" readonly value="{{ $publicLinkUrl }}"
                                            class="w-full px-3 py-2 text-xs bg-gray-50 text-gray-700 select-all outline-none">
                                        <button type="button"
                                            @click="navigator.clipboard.writeText('{{ $publicLinkUrl }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                            class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs flex items-center space-x-1.5 transition">
                                            <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                            <svg x-show="copied" class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Pengaturan Izin & Masa Berlaku -->
                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Izin Akses</label>
                                        <select wire:model="publicPermission" wire:change="updatePublicPermission"
                                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-gray-300 bg-white outline-none focus:ring-2 focus:ring-brand-500">
                                            <option value="view">Hanya Lihat (View Only)</option>
                                            <option value="download">Dapat Mengunduh (Can Download)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Masa Berlaku</label>
                                        <select wire:model="publicExpiryDays" wire:change="togglePublicShare"
                                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-gray-300 bg-white outline-none focus:ring-2 focus:ring-brand-500">
                                            <option value="1">1 Hari</option>
                                            <option value="7">7 Hari (Default)</option>
                                            <option value="30">30 Hari</option>
                                            <option value="0">Tanpa Batas Waktu</option>
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Tab 2: Bagikan ke Orang -->
                    @if ($activeTab === 'people')
                        <div class="space-y-4">
                            <!-- Cari User -->
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1.5">Tambah Pengguna Kampus</label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="userSearch"
                                        placeholder="Cari nama atau email pengguna..."
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-500">

                                    <!-- Search Results Dropdown -->
                                    @if ($searchResults->isNotEmpty())
                                        <div class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-30 divide-y divide-gray-100 max-h-48 overflow-y-auto">
                                            @foreach ($searchResults as $user)
                                                <div class="p-2.5 flex items-center justify-between hover:bg-gray-50 transition">
                                                    <div class="truncate pr-2">
                                                        <p class="text-xs font-semibold text-gray-900">{{ $user->name }}</p>
                                                        <p class="text-[10px] text-gray-500">{{ $user->email }}</p>
                                                    </div>
                                                    <button type="button" wire:click="addCollaborator({{ $user->id }})"
                                                        class="px-2.5 py-1 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-[10px] font-semibold flex-shrink-0 transition">
                                                        Bagikan
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Pilihan Izin Default Tambah -->
                            <div class="flex items-center space-x-3 text-xs">
                                <span class="text-[11px] font-semibold text-gray-600">Izin default:</span>
                                <label class="inline-flex items-center space-x-1 cursor-pointer">
                                    <input type="radio" wire:model="recipientPermission" value="view" class="text-brand-600">
                                    <span class="text-[11px]">Hanya Lihat</span>
                                </label>
                                <label class="inline-flex items-center space-x-1 cursor-pointer">
                                    <input type="radio" wire:model="recipientPermission" value="download" class="text-brand-600">
                                    <span class="text-[11px]">Dapat Mengunduh</span>
                                </label>
                            </div>

                            <!-- Daftar Orang yang Memiliki Akses -->
                            <div class="pt-3 border-t border-gray-100">
                                <h4 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Orang dengan Akses</h4>

                                <!-- Owner Row -->
                                <div class="py-2 flex items-center justify-between text-xs">
                                    <div class="flex items-center space-x-2.5 truncate">
                                        <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                        <div class="truncate">
                                            <p class="font-medium text-gray-900 truncate">{{ auth()->user()->name }} (Anda)</p>
                                            <p class="text-[10px] text-gray-400">{{ auth()->user()->email }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-medium">Pemilik</span>
                                </div>

                                <!-- Collaborators List -->
                                @foreach ($collaborators as $collab)
                                    <div class="py-2 flex items-center justify-between text-xs group hover:bg-gray-50/80 rounded-lg px-1 transition">
                                        <div class="flex items-center space-x-2.5 truncate">
                                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                                {{ strtoupper(substr($collab->sharedWithUser->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="truncate">
                                                <p class="font-medium text-gray-900 truncate">{{ $collab->sharedWithUser->name ?? 'Pengguna' }}</p>
                                                <p class="text-[10px] text-gray-400">{{ $collab->sharedWithUser->email ?? '' }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <select wire:change="updateCollaboratorPermission({{ $collab->id }}, $event.target.value)"
                                                class="text-[11px] rounded-lg border border-gray-200 py-0.5 px-2 bg-white text-gray-700 outline-none">
                                                <option value="view" {{ $collab->permission === 'view' ? 'selected' : '' }}>Lihat Saja</option>
                                                <option value="download" {{ $collab->permission === 'download' ? 'selected' : '' }}>Unduh</option>
                                            </select>
                                            <button type="button" wire:click="removeCollaborator({{ $collab->id }})"
                                                class="text-gray-400 hover:text-rose-600 p-1 rounded transition" title="Cabut Akses">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl text-xs font-semibold transition">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
