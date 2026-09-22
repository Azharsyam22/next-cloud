<?php

use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use App\Models\User;
use App\Services\ShareService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('share service creates public share with 64-character token', function () {
    $user = User::factory()->create();
    $file = File::factory()->create(['user_id' => $user->id]);

    $service = app(ShareService::class);
    $share = $service->createPublicShare($file, $user, 'download', 14);

    expect($share)->toBeInstanceOf(Share::class)
        ->and(strlen($share->token))->toBe(64)
        ->and($share->permission)->toBe('download')
        ->and($share->is_active)->toBeTrue()
        ->and($share->shared_with_user_id)->toBeNull()
        ->and($share->expires_at)->not->toBeNull()
        ->and($share->isValid())->toBeTrue();
});

test('share service can share item with another user and prevents sharing to self', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $folder = Folder::factory()->create(['user_id' => $owner->id]);

    $service = app(ShareService::class);
    $share = $service->shareWithUser($folder, $owner, $recipient, 'view');

    expect($share->shared_with_user_id)->toBe($recipient->id)
        ->and($share->permission)->toBe('view')
        ->and($share->isPublic())->toBeFalse();

    // Pencegahan berbagi ke diri sendiri
    expect(fn () => $service->shareWithUser($folder, $owner, $owner))
        ->toThrow(ValidationException::class);
});

test('share service can update and revoke share', function () {
    $user = User::factory()->create();
    $file = File::factory()->create(['user_id' => $user->id]);

    $service = app(ShareService::class);
    $share = $service->createPublicShare($file, $user, 'view');

    $service->updateShare($share, ['permission' => 'download']);
    expect($share->fresh()->permission)->toBe('download');

    $service->revokeShare($share);
    expect($share->fresh()->is_active)->toBeFalse()
        ->and($share->fresh()->isValid())->toBeFalse();
});

test('validate access allows active valid share and blocks expired or unauthorized access', function () {
    $owner = User::factory()->create();
    $file = File::factory()->create(['user_id' => $owner->id]);

    $service = app(ShareService::class);
    $share = $service->createPublicShare($file, $owner, 'view');

    // Akses publik yang valid
    $validated = $service->validateAccess($share->token);
    expect($validated->id)->toBe($share->id);

    // Tautan kedaluwarsa -> harus HTTP 410
    $share->update(['expires_at' => now()->subDay()]);
    expect(fn () => $service->validateAccess($share->token))
        ->toThrow(HttpException::class);

    // Tautan privat untuk penerima tertentu
    $recipient = User::factory()->create();
    $stranger = User::factory()->create();
    $privateShare = $service->shareWithUser($file, $owner, $recipient);

    // Tanpa login -> 401
    expect(fn () => $service->validateAccess($privateShare->token, null))
        ->toThrow(HttpException::class);

    // Stranger -> 403
    expect(fn () => $service->validateAccess($privateShare->token, $stranger))
        ->toThrow(HttpException::class);

    // Recipient yang sah -> berhasil
    $validPrivate = $service->validateAccess($privateShare->token, $recipient);
    expect($validPrivate->id)->toBe($privateShare->id);
});
