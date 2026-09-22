<?php

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user has cloudcampus attributes and default quota', function () {
    $user = User::factory()->create([
        'account_type' => 'public',
        'quota_bytes' => 5368709120,
        'used_bytes' => 1073741824, // 1 GB
    ]);

    expect($user->account_type)->toBe('public')
        ->and($user->isPublic())->toBeTrue()
        ->and($user->isAcademic())->toBeFalse()
        ->and($user->quota_bytes)->toBe(5368709120)
        ->and($user->used_bytes)->toBe(1073741824);
});

test('academic user can be created with external_id and null password', function () {
    $academicUser = User::factory()->academic('202610001')->create();

    expect($academicUser->account_type)->toBe('academic')
        ->and($academicUser->isAcademic())->toBeTrue()
        ->and($academicUser->isPublic())->toBeFalse()
        ->and($academicUser->external_id)->toBe('202610001')
        ->and($academicUser->password)->toBeNull();
});

test('quota calculation helpers work accurately', function () {
    $user = User::factory()->create([
        'quota_bytes' => 1000,
        'used_bytes' => 400,
    ]);

    expect($user->remainingQuotaBytes())->toBe(600)
        ->and($user->quotaUsagePercentage())->toBe(40.0)
        ->and($user->hasQuotaFor(500))->toBeTrue()
        ->and($user->hasQuotaFor(600))->toBeTrue()
        ->and($user->hasQuotaFor(601))->toBeFalse();
});

test('user relations with folders, files, shares, and activity logs work', function () {
    $user = User::factory()->create();

    $folder = Folder::factory()->create(['user_id' => $user->id]);
    $file = File::factory()->create(['user_id' => $user->id, 'folder_id' => $folder->id]);
    $share = Share::factory()->create(['user_id' => $user->id, 'shareable_id' => $file->id]);
    $log = ActivityLog::factory()->create(['user_id' => $user->id]);

    expect($user->folders)->toHaveCount(1)
        ->and($user->files)->toHaveCount(1)
        ->and($user->shares)->toHaveCount(1)
        ->and($user->activityLogs)->toHaveCount(1);
});
