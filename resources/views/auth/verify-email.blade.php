<x-layouts.auth title="Verifikasi Email">
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 sm:p-8">
        <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>

        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">Verifikasi Alamat Email Anda</h1>
            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                Terima kasih telah mendaftar! Sebelum mulai menggunakan CloudCampus Storage, mohon verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke <strong>{{ auth()->user()?->email }}</strong>.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent' || session('status'))
            <div class="mb-5 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Tautan verifikasi baru telah berhasil dikirimkan ke email Anda.</span>
            </div>
        @endif

        <div class="space-y-3 pt-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium text-xs transition shadow-sm">
                    Kirim Ulang Email Verifikasi
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs transition">
                    Keluar (Logout)
                </button>
            </form>
        </div>
    </div>
</x-layouts.auth>
