<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guest cannot access admin routes and is redirected to login', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->get(route('admin.users'))->assertRedirect(route('login'));
    $this->get(route('admin.logs'))->assertRedirect(route('login'));
});

test('regular user is forbidden from accessing admin web routes with 403', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.users'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.logs'))->assertForbidden();
});

test('regular user is forbidden from accessing admin api routes with 403', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/admin/stats')->assertForbidden();
    $this->getJson('/api/v1/admin/users')->assertForbidden();
    $this->getJson('/api/v1/admin/logs')->assertForbidden();
});

test('admin kampus can access admin web and api routes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin-kampus');

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($admin)->get(route('admin.users'))->assertOk();
    $this->actingAs($admin)->get(route('admin.logs'))->assertOk();

    Sanctum::actingAs($admin);
    $this->getJson('/api/v1/admin/stats')->assertOk();
});

test('super admin can access admin web and api routes', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($superAdmin)->get(route('admin.users'))->assertOk();
    $this->actingAs($superAdmin)->get(route('admin.logs'))->assertOk();

    Sanctum::actingAs($superAdmin);
    $this->getJson('/api/v1/admin/stats')->assertOk();
});
