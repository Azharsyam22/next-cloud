<?php

use App\Models\Folder;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('user can view, update, and delete their own folder', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $folder = Folder::factory()->create(['user_id' => $user->id]);

    expect($user->can('view', $folder))->toBeTrue()
        ->and($user->can('update', $folder))->toBeTrue()
        ->and($user->can('delete', $folder))->toBeTrue()
        ->and($user->can('create', Folder::class))->toBeTrue();
});

test('user cannot view, update, or delete other users folder', function () {
    $userA = User::factory()->create();
    $userA->assignRole('user');

    $userB = User::factory()->create();
    $userB->assignRole('user');

    $folderOfUserA = Folder::factory()->create(['user_id' => $userA->id]);

    expect($userB->can('view', $folderOfUserA))->toBeFalse()
        ->and($userB->can('update', $folderOfUserA))->toBeFalse()
        ->and($userB->can('delete', $folderOfUserA))->toBeFalse();
});
