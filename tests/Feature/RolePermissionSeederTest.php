<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('role permission seeder creates required roles and permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    expect(Role::where('name', 'super-admin')->exists())->toBeTrue()
        ->and(Role::where('name', 'admin-kampus')->exists())->toBeTrue()
        ->and(Role::where('name', 'user')->exists())->toBeTrue();

    $user = User::factory()->create();
    $user->assignRole('user');

    expect($user->hasRole('user'))->toBeTrue()
        ->and($user->hasPermissionTo('files.upload'))->toBeTrue()
        ->and($user->hasPermissionTo('folders.create'))->toBeTrue()
        ->and($user->can('admin.dashboard'))->toBeFalse();
});

test('database seeder seeds default admin and demo users', function () {
    $this->seed(DatabaseSeeder::class);

    $superAdmin = User::where('email', 'admin@cloudcampus.ac.id')->first();
    $student = User::where('email', 'mahasiswa@cloudcampus.ac.id')->first();
    $publicUser = User::where('email', 'public@example.com')->first();

    expect($superAdmin)->not->toBeNull()
        ->and($superAdmin->hasRole('super-admin'))->toBeTrue()
        ->and($superAdmin->isAcademic())->toBeTrue()
        ->and($student)->not->toBeNull()
        ->and($student->isAcademic())->toBeTrue()
        ->and($student->external_id)->toBe('202610001')
        ->and($publicUser)->not->toBeNull()
        ->and($publicUser->isPublic())->toBeTrue();
});
