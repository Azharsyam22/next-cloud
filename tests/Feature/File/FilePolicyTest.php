<?php

use App\Models\File;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('user can manage their own file', function () {
    $owner = User::factory()->create();
    $owner->assignRole('user');

    $file = File::factory()->create(['user_id' => $owner->id]);

    expect($owner->can('view', $file))->toBeTrue()
        ->and($owner->can('download', $file))->toBeTrue()
        ->and($owner->can('update', $file))->toBeTrue()
        ->and($owner->can('delete', $file))->toBeTrue()
        ->and($owner->can('restore', $file))->toBeTrue()
        ->and($owner->can('forceDelete', $file))->toBeTrue();
});

test('unauthorized user cannot access or modify another users file', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherUser->assignRole('user');

    $file = File::factory()->create(['user_id' => $owner->id]);

    expect($otherUser->can('view', $file))->toBeFalse()
        ->and($otherUser->can('download', $file))->toBeFalse()
        ->and($otherUser->can('update', $file))->toBeFalse()
        ->and($otherUser->can('delete', $file))->toBeFalse()
        ->and($otherUser->can('restore', $file))->toBeFalse()
        ->and($otherUser->can('forceDelete', $file))->toBeFalse();
});

test('super admin can access and manage any file', function () {
    $owner = User::factory()->create();
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $file = File::factory()->create(['user_id' => $owner->id]);

    expect($superAdmin->can('view', $file))->toBeTrue()
        ->and($superAdmin->can('download', $file))->toBeTrue()
        ->and($superAdmin->can('update', $file))->toBeTrue()
        ->and($superAdmin->can('delete', $file))->toBeTrue()
        ->and($superAdmin->can('restore', $file))->toBeTrue()
        ->and($superAdmin->can('forceDelete', $file))->toBeTrue();
});
