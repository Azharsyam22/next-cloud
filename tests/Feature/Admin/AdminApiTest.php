<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can retrieve system stats via api', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin-kampus');
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/admin/stats');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'total_users',
                'academic_users',
                'public_users',
                'suspended_users',
                'total_storage_allocated',
                'total_storage_used',
                'storage_percentage',
                'total_files',
                'total_folders',
                'total_shares',
                'uploads_today_count',
                'uploads_today_bytes',
                'users_near_quota',
                'recent_activities',
            ],
        ]);
});

test('admin can list users with pagination via api', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin);

    User::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/admin/users');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data',
            'meta' => [
                'current_page',
                'total',
            ],
        ]);
});

test('admin can toggle user suspension via api', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin-kampus');
    Sanctum::actingAs($admin);

    $target = User::factory()->create(['is_suspended' => false]);

    $response = $this->postJson("/api/v1/admin/users/{$target->id}/suspend");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'is_suspended' => true,
        ]);

    $target->refresh();
    expect($target->isSuspended())->toBeTrue();
});

test('admin can override user quota via api', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Sanctum::actingAs($admin);

    $target = User::factory()->create(['quota_bytes' => 5 * 1024 * 1024 * 1024]);

    $newQuota = 50 * 1024 * 1024 * 1024; // 50 GB
    $response = $this->postJson("/api/v1/admin/users/{$target->id}/quota", [
        'quota_bytes' => $newQuota,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $target->id,
                'quota_bytes' => $newQuota,
            ],
        ]);

    $target->refresh();
    expect($target->quota_bytes)->toBe($newQuota);
});
