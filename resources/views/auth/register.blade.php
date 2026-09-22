<x-layouts.auth title="Pendaftaran Pengguna Publik">
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 sm:p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Daftar Akun Baru</h1>
            <p class="text-sm text-gray-500 mt-1">Layanan penyimpanan mandiri untuk pengguna umum</p>
        </div>

        <!-- Banner Info untuk Sivitas Kampus -->
        <div class="mb-5 p-3.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-start gap-2.5">
            <svg class="w-5 h-5 flex-shrink-0 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <span class="font-semibold block mb-0.5">Sivitas Akademika Kampus?</span>
                Mahasiswa dan dosen tidak perlu mendaftar manual. Silakan langsung <a href="{{ route('sso.redirect') }}" class="underline font-bold text-brand-700 hover:text-brand-800">Masuk dengan SSO</a>.
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('name') border-rose-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm transition outline-none"
                    placeholder="Budi Santoso">
                @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Alamat Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('email') border-rose-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm transition outline-none"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Kata Sandi</label>
                <input id="password" type="password" name="password" required
                    class="w-full px-3.5 py-2.5 rounded-lg border @error('password') border-rose-500 @else border-gray-300 @enderror focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm transition outline-none"
                    placeholder="Minimal 8 karakter">
                @error('password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Konfirmasi Kata Sandi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm transition outline-none"
                    placeholder="Ulangi kata sandi">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm transition shadow-sm">
                    Daftar Sekarang
                </button>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-600">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700 underline underline-offset-2">Masuk ke akun</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
