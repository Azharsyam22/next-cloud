<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SsoService
{
    /**
     * Menghasilkan URL redirect menuju Sistem Akademik (Identity Provider).
     */
    public function generateRedirectUrl(?string $state = null): string
    {
        $providerUrl = config('cloudcampus.sso.provider_url') ?: url('/auth/sso/mock-provider');
        $clientId = config('cloudcampus.sso.client_id', 'cloudcampus-client');
        $redirectUri = config('cloudcampus.sso.redirect_uri', url('/auth/sso/callback'));
        $state = $state ?: Str::random(40);

        Session::put('sso_state', $state);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'token',
            'state' => $state,
        ]);

        return rtrim($providerUrl, '/').'?'.$query;
    }

    /**
     * Memproses callback SSO dan token yang dikirim dari Sistem Akademik.
     *
     * @throws ValidationException
     */
    public function handleCallback(string $token): User
    {
        $payload = $this->verifyToken($token);

        if (empty($payload['external_id']) || empty($payload['email']) || empty($payload['name'])) {
            throw ValidationException::withMessages([
                'token' => ['Token SSO tidak memiliki data pengguna yang lengkap (external_id, email, name).'],
            ]);
        }

        // Logic firstOrCreate user akademik berdasarkan external_id (NIM / NIP)
        $user = User::firstOrCreate(
            ['external_id' => (string) $payload['external_id']],
            [
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => null, // Aturan rules.md §4: Akun akademik tidak memiliki password lokal
                'account_type' => 'academic',
                'quota_bytes' => (int) config('cloudcampus.default_quota_bytes', 5368709120),
                'used_bytes' => 0,
                'email_verified_at' => now(), // Email akademik sudah diverifikasi oleh SSO kampus
            ]
        );

        // Jika user sudah ada, sinkronkan nama dan email terbaru
        if (! $user->wasRecentlyCreated) {
            $user->update([
                'name' => $payload['name'],
                'email' => $payload['email'],
            ]);
        }

        // Pastikan role default terpasang
        if ($user->roles()->count() === 0) {
            $user->assignRole('user');
        }

        // Login ke session Laravel
        Auth::login($user);
        Session::regenerate();

        // Rekam riwayat audit SSO
        ActivityLog::record('login_sso', $user, 'Login melalui SSO Sistem Akademik', [
            'external_id' => $user->external_id,
            'email' => $user->email,
        ], $user->id);

        return $user;
    }

    /**
     * Memvalidasi dan mengekstrak payload dari token SSO (JWT / Signed token).
     *
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function verifyToken(string $token): array
    {
        $parts = explode('.', trim($token));

        if (count($parts) !== 3) {
            throw ValidationException::withMessages([
                'token' => ['Format token SSO tidak valid.'],
            ]);
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $secret = config('cloudcampus.sso.client_secret') ?: config('app.key');
        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true));

        if (! hash_equals($expectedSignature, $signatureB64)) {
            throw ValidationException::withMessages([
                'token' => ['Tanda tangan (signature) token SSO tidak valid atau telah dimanipulasi.'],
            ]);
        }

        $payloadJson = $this->base64UrlDecode($payloadB64);
        $payload = json_decode($payloadJson, true);

        if (! is_array($payload)) {
            throw ValidationException::withMessages([
                'token' => ['Isi payload token SSO tidak dapat diuraikan.'],
            ]);
        }

        // Cek kedaluwarsa jika ada klaim 'exp'
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw ValidationException::withMessages([
                'token' => ['Token SSO telah kedaluwarsa.'],
            ]);
        }

        return $payload;
    }

    /**
     * Helper untuk membuat token JWT valid untuk kebutuhan mock SSO dan testing.
     *
     * @param  array<string, mixed>  $payload
     */
    public function createTokenForTesting(array $payload, ?string $secret = null): string
    {
        $secret = $secret ?: (config('cloudcampus.sso.client_secret') ?: config('app.key'));

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headerB64 = $this->base64UrlEncode(json_encode($header));
        $payloadB64 = $this->base64UrlEncode(json_encode($payload));
        $signatureB64 = $this->base64UrlEncode(hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true));

        return "$headerB64.$payloadB64.$signatureB64";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
