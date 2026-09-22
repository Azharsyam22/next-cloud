<?php

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('academic user is strictly blocked by middleware from changing password', function () {
    $academicUser = User::factory()->academic('202610009')->create();

    $response = $this->actingAs($academicUser)->put('/user/password', [
        'current_password' => 'some-password',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ]);

    $response->assertStatus(403);
});

test('academic user is strictly blocked by AuthService from changing password', function () {
    $academicUser = User::factory()->academic('202610010')->create();
    $authService = app(AuthService::class);

    expect(fn () => $authService->changePassword($academicUser, 'current', 'new123456'))
        ->toThrow(AuthorizationException::class);
});

test('public user can successfully change password with valid current password', function () {
    $publicUser = User::factory()->create([
        'account_type' => 'public',
        'password' => Hash::make('old-password123'),
    ]);

    $response = $this->actingAs($publicUser)->put('/user/password', [
        'current_password' => 'old-password123',
        'password' => 'new-password456',
        'password_confirmation' => 'new-password456',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $publicUser->refresh();
    expect(Hash::check('new-password456', $publicUser->password))->toBeTrue();
});

test('public user fails to change password if current password is wrong', function () {
    $publicUser = User::factory()->create([
        'account_type' => 'public',
        'password' => Hash::make('correct-password123'),
    ]);

    $response = $this->actingAs($publicUser)->put('/user/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password456',
        'password_confirmation' => 'new-password456',
    ]);

    $response->assertSessionHasErrors(['current_password']);

    $publicUser->refresh();
    expect(Hash::check('correct-password123', $publicUser->password))->toBeTrue();
});
