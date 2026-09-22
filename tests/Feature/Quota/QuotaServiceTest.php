<?php

use App\Models\File;
use App\Models\User;
use App\Services\QuotaService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('quota service calculates correct quota stats and warning flag', function () {
    $service = app(QuotaService::class);

    // User dengan 92% pemakaian
    $user = User::factory()->create([
        'quota_bytes' => 1000,
        'used_bytes' => 920,
    ]);

    $stats = $service->getQuotaStats($user);

    expect($stats['quota_bytes'])->toBe(1000)
        ->and($stats['used_bytes'])->toBe(920)
        ->and($stats['remaining_bytes'])->toBe(80)
        ->and($stats['percentage'])->toBe(92.0)
        ->and($stats['is_warning'])->toBeTrue()
        ->and($stats['is_critical'])->toBeFalse();
});

test('quota service flags critical when quota is 98% or more', function () {
    $service = app(QuotaService::class);

    $user = User::factory()->create([
        'quota_bytes' => 1000,
        'used_bytes' => 990,
    ]);

    $stats = $service->getQuotaStats($user);

    expect($stats['is_warning'])->toBeTrue()
        ->and($stats['is_critical'])->toBeTrue();
});

test('quota service breakdown categorizes MIME types correctly', function () {
    $service = app(QuotaService::class);

    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);

    // 1 Gambar (1000 byte)
    File::factory()->create([
        'user_id' => $user->id,
        'mime_type' => 'image/png',
        'size' => 1000,
    ]);

    // 1 Dokumen PDF (2000 byte)
    File::factory()->create([
        'user_id' => $user->id,
        'mime_type' => 'application/pdf',
        'size' => 2000,
    ]);

    // 1 Arsip ZIP (3000 byte)
    File::factory()->create([
        'user_id' => $user->id,
        'mime_type' => 'application/zip',
        'size' => 3000,
    ]);

    $user->refresh();

    $breakdown = $service->getBreakdownByType($user);

    expect($breakdown['images']['bytes'])->toBe(1000)
        ->and($breakdown['images']['count'])->toBe(1)
        ->and($breakdown['documents']['bytes'])->toBe(2000)
        ->and($breakdown['documents']['count'])->toBe(1)
        ->and($breakdown['archives']['bytes'])->toBe(3000)
        ->and($breakdown['archives']['count'])->toBe(1);
});

test('quota service tracks trash usage accurately', function () {
    $service = app(QuotaService::class);

    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);

    $file = File::factory()->create([
        'user_id' => $user->id,
        'size' => 4500,
    ]);

    expect($service->getTrashUsage($user))->toBe(0);

    $file->delete(); // soft delete

    expect($service->getTrashUsage($user))->toBe(4500);
});

test('quota service recalculates and heals inconsistent user usage', function () {
    $service = app(QuotaService::class);

    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 999999, // desinkronisasi buatan
    ]);

    File::factory()->create([
        'user_id' => $user->id,
        'size' => 3000,
    ]);

    $recalculated = $service->recalculateUserUsage($user);
    $user->refresh();

    expect($recalculated)->toBe(3000)
        ->and($user->used_bytes)->toBe(3000);
});
