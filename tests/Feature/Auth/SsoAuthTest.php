<?php

use App\Models\User;
use App\Services\SsoService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('sso redirect endpoint redirects user to academic provider with parameters', function () {
    $response = $this->get(route('sso.redirect'));

    $response->assertRedirect();
    $targetUrl = $response->headers->get('Location');

    expect($targetUrl)->toContain('client_id=')
        ->and($targetUrl)->toContain('redirect_uri=')
        ->and($targetUrl)->toContain('state=');
});

test('sso callback with valid token creates academic user and logs in', function () {
    $ssoService = app(SsoService::class);

    $token = $ssoService->createTokenForTesting([
        'external_id' => '202610002',
        'email' => 'ahmad@campus.ac.id',
        'name' => 'Ahmad Mahasiswa',
        'exp' => time() + 3600,
    ]);

    $response = $this->get(route('sso.callback', ['token' => $token]));

    $response->assertRedirect(route('dashboard'));

    $user = User::where('external_id', '202610002')->first();

    expect($user)->not->toBeNull()
        ->and($user->account_type)->toBe('academic')
        ->and($user->isAcademic())->toBeTrue()
        ->and($user->isPublic())->toBeFalse()
        ->and($user->password)->toBeNull()
        ->and($user->email)->toBe('ahmad@campus.ac.id')
        ->and($user->name)->toBe('Ahmad Mahasiswa')
        ->and($user->quota_bytes)->toBe(5368709120)
        ->and($user->hasRole('user'))->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

test('sso callback with existing academic user logs in without creating duplicate', function () {
    $existingUser = User::factory()->academic('202610003')->create([
        'email' => 'old_email@campus.ac.id',
        'name' => 'Nama Lama',
    ]);

    $ssoService = app(SsoService::class);
    $token = $ssoService->createTokenForTesting([
        'external_id' => '202610003',
        'email' => 'new_email@campus.ac.id',
        'name' => 'Nama Baru',
        'exp' => time() + 3600,
    ]);

    $response = $this->get(route('sso.callback', ['token' => $token]));

    $response->assertRedirect(route('dashboard'));

    expect(User::where('external_id', '202610003')->count())->toBe(1);

    $existingUser->refresh();
    expect($existingUser->email)->toBe('new_email@campus.ac.id')
        ->and($existingUser->name)->toBe('Nama Baru');

    $this->assertAuthenticatedAs($existingUser);
});

test('sso callback rejects invalid or tampered token', function () {
    $ssoService = app(SsoService::class);

    // Buat token dengan secret berbeda sehingga signature salah
    $tamperedToken = $ssoService->createTokenForTesting([
        'external_id' => '202610004',
        'email' => 'hacker@example.com',
        'name' => 'Hacker',
    ], 'wrong-secret-key-12345');

    $response = $this->get(route('sso.callback', ['token' => $tamperedToken]));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['token']);
    $this->assertGuest();
});

test('sso callback rejects expired token', function () {
    $ssoService = app(SsoService::class);

    $expiredToken = $ssoService->createTokenForTesting([
        'external_id' => '202610005',
        'email' => 'expired@campus.ac.id',
        'name' => 'User Expired',
        'exp' => time() - 3600, // 1 jam lalu
    ]);

    $response = $this->get(route('sso.callback', ['token' => $expiredToken]));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['token']);
    $this->assertGuest();
});

test('sso callback without token query param redirects to login with error', function () {
    $response = $this->get(route('sso.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['sso']);
    $this->assertGuest();
});
