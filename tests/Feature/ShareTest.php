<?php

use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('share automatically generates 64-char token if empty', function () {
    $user = User::factory()->create();
    $file = File::factory()->create(['user_id' => $user->id]);

    $share = Share::create([
        'user_id' => $user->id,
        'shareable_type' => File::class,
        'shareable_id' => $file->id,
        'permission' => 'view',
    ]);

    expect($share->token)->not->toBeEmpty()
        ->and(strlen($share->token))->toBe(64)
        ->and($share->shareable)->toBeInstanceOf(File::class)
        ->and($share->shareable->id)->toBe($file->id);
});

test('share works for both File and Folder polymorphically', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->create(['user_id' => $user->id]);

    $share = Share::create([
        'user_id' => $user->id,
        'shareable_type' => Folder::class,
        'shareable_id' => $folder->id,
        'permission' => 'download',
    ]);

    expect($share->shareable)->toBeInstanceOf(Folder::class)
        ->and($share->canDownload())->toBeTrue()
        ->and($share->isPublic())->toBeTrue();
});

test('share expiry and validity checks work accurately', function () {
    $user = User::factory()->create();
    $file = File::factory()->create();

    $validShare = Share::factory()->create([
        'user_id' => $user->id,
        'shareable_id' => $file->id,
        'expires_at' => now()->addDays(2),
        'is_active' => true,
    ]);

    $expiredShare = Share::factory()->create([
        'user_id' => $user->id,
        'shareable_id' => $file->id,
        'expires_at' => now()->subDay(),
        'is_active' => true,
    ]);

    $inactiveShare = Share::factory()->create([
        'user_id' => $user->id,
        'shareable_id' => $file->id,
        'expires_at' => now()->addDays(2),
        'is_active' => false,
    ]);

    expect($validShare->isExpired())->toBeFalse()
        ->and($validShare->isValid())->toBeTrue()
        ->and($expiredShare->isExpired())->toBeTrue()
        ->and($expiredShare->isValid())->toBeFalse()
        ->and($inactiveShare->isValid())->toBeFalse();

    $activeShares = Share::active()->get();
    expect($activeShares)->toHaveCount(1)
        ->and($activeShares->first()->id)->toBe($validShare->id);
});
