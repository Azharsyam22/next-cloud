<x-layouts.auth title="Masuk">
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 sm:p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Masuk ke Akun Anda</h1>
            <p class="text-sm text-gray-500 mt-1">Pilih jalur autentikasi sesuai jenis akun Anda</p>
        </div>

        @if (session('status'))
            <div class="mb-5 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->has('sso'))
            <div class="mb-5 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>{{ $errors->first('sso') }}</span>
            </div>
        @endif

        <!-- Opsi 1: SSO Akademik (Primary untuk Sivitas Kampus) -->
        <div class="mb-6">
            <a href="{{ route('sso.redirect') }}" class="w-full py-3 px-4 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm flex items-center justify-center gap-3 shadow-sm hover:shadow transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                </svg>
                Masuk dengan Akun Akademik (SSO)
            </a>
            <p class="text-xs text-center text-gray-500 mt-2">Untuk Mahasiswa, Dosen, dan Tenaga Kependidikan</p>
        </div>

        <!-- Pembatas Pilihan -->
        <div class="relative flex items-center justify-center my-6">
            <div class="border-t border-gray-200 w-full"></div>
            <span class="bg-white px-3 text-xs uppercase tracking-wider text-gray-400 font-medium">atau akun umum</span>
            <div class="border-t border-gray-200 w-full"></div>
        </div>

        <!-- Opsi 2: Form Login Pengguna Publik -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Alamat Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
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
                    placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500/30">
                    <span class="text-xs text-gray-600">Ingat Saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-gray-900 hover:bg-gray-800 text-white font-medium text-sm transition shadow-sm">
                Masuk
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-600">
                Pengguna umum dan belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700 underline underline-offset-2">Daftar di sini</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
