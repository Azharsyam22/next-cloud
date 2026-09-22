<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi pengguna publik.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if ($this->authService->authenticate($credentials['email'], $credentials['password'], $remember)) {
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Kombinasi email dan kata sandi yang Anda masukkan tidak sesuai.',
        ])->onlyInput('email');
    }

    /**
     * Tampilkan halaman registrasi pengguna publik.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi pengguna publik.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $this->authService->registerPublicUser($validated);

        // Langsung login setelah pendaftaran berhasil
        auth()->login($user);

        return redirect()->route('verification.notice');
    }

    /**
     * Logout pengguna dari sesi.
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar.');
    }

    /**
     * Mengubah kata sandi pengguna publik.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $this->authService->changePassword(
            $request->user(),
            $validated['current_password'],
            $validated['password']
        );

        return back()->with('status', 'Kata sandi Anda berhasil diperbarui.');
    }
}
