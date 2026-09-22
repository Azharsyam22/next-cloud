<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SsoController extends Controller
{
    public function __construct(
        protected SsoService $ssoService
    ) {}

    /**
     * Mengarahkan pengguna ke Identity Provider Sistem Akademik.
     */
    public function redirect(): RedirectResponse
    {
        $redirectUrl = $this->ssoService->generateRedirectUrl();

        return redirect()->away($redirectUrl);
    }

    /**
     * Menangani callback dari SSO Sistem Akademik beserta token autentikasinya.
     */
    public function callback(Request $request): RedirectResponse
    {
        $token = $request->query('token') ?: $request->input('token');

        if (! $token) {
            return redirect()->route('login')->withErrors([
                'sso' => 'Token autentikasi dari Sistem Akademik tidak ditemukan.',
            ]);
        }

        try {
            $user = $this->ssoService->handleCallback($token);

            return redirect()->intended(route('dashboard'))
                ->with('status', 'Selamat datang, '.$user->name.' (Akun Akademik).');
        } catch (ValidationException $e) {
            return redirect()->route('login')->withErrors($e->errors());
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'sso' => 'Gagal memproses autentikasi SSO: '.$e->getMessage(),
            ]);
        }
    }
}
