<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Mendaftarkan pengguna publik baru.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function registerPublicUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'account_type' => 'public',
            'external_id' => null,
            'quota_bytes' => (int) config('cloudcampus.default_quota_bytes', 5368709120),
            'used_bytes' => 0,
        ]);

        // Berikan role user standar
        $user->assignRole('user');

        // Picu event Registered untuk pengiriman verifikasi email
        event(new Registered($user));

        // Catat riwayat audit
        ActivityLog::record('register_public', $user, 'Pendaftaran akun publik baru', [
            'email' => $user->email,
        ], $user->id);

        return $user;
    }

    /**
     * Melakukan autentikasi pengguna publik.
     */
    public function authenticate(string $email, string $password, bool $remember = false): bool
    {
        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            $user = Auth::user();

            Session::regenerate();

            ActivityLog::record('login_public', $user, 'Login pengguna publik', [
                'email' => $user->email,
            ], $user->id);

            return true;
        }

        return false;
    }

    /**
     * Melakukan logout pengguna dari sistem.
     */
    public function logout(): void
    {
        if (Auth::check()) {
            ActivityLog::record('logout', Auth::user(), 'Logout dari sistem', [], Auth::id());
        }

        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
    }

    /**
     * Mengubah password pengguna (hanya diizinkan untuk akun publik).
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Aturan rules.md §4: User akademik tidak boleh bisa mengganti password lewat CloudCampus
        if ($user->isAcademic()) {
            throw new AuthorizationException('Akun akademik dikelola oleh SSO Sistem Akademik dan tidak dapat mengganti kata sandi lokal.');
        }

        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Kata sandi saat ini tidak cocok.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        ActivityLog::record('change_password', $user, 'Perubahan kata sandi akun publik', [], $user->id);

        return true;
    }
}
