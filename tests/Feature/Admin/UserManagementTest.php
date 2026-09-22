<?php

use App\Livewire\Admin\UserManagement;
use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can view user list in user management component', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin-kampus');

    $student = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi@kampus.ac.id',
        'account_type' => 'academic',
        'external_id' => '12345678',
    ]);
    $student->assignRole('user');

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->assertOk()
        ->assertSee('Budi Santoso')
        ->assertSee('budi@kampus.ac.id')
        ->assertSee('12345678');
});

test('admin can filter users by account type and search term', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $academic = User::factory()->create([
        'name' => 'Dr. Hendra Wijaya',
        'account_type' => 'academic',
    ]);
    $public = User::factory()->create([
        'name' => 'John Doe Public',
        'account_type' => 'public',
    ]);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->set('accountType', 'academic')
        ->assertSee('Dr. Hendra Wijaya')
        ->assertDontSee('John Doe Public')
        ->set('accountType', 'public')
        ->assertSee('John Doe Public')
        ->assertDontSee('Dr. Hendra Wijaya')
        ->set('accountType', 'all')
        ->set('search', 'Hendra')
        ->assertSee('Dr. Hendra Wijaya')
        ->assertDontSee('John Doe Public');
});

test('admin can toggle user suspension and activity log is recorded', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin-kampus');

    $user = User::factory()->create([
        'name' => 'Pengguna Bermasalah',
        'is_suspended' => false,
    ]);
    $user->assignRole('user');

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('toggleSuspend', $user->id)
        ->assertSee('berhasil ditangguhkan');

    $user->refresh();
    expect($user->isSuspended())->toBeTrue()
        ->and($user->suspended_at)->not->toBeNull();

    expect(ActivityLog::where('action', 'user_suspend')->where('user_id', $admin->id)->exists())->toBeTrue();

    // Aktifkan kembali
    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('toggleSuspend', $user->id)
        ->assertSee('berhasil diaktifkan kembali');

    $user->refresh();
    expect($user->isSuspended())->toBeFalse()
        ->and($user->suspended_at)->toBeNull();

    expect(ActivityLog::where('action', 'user_activate')->where('user_id', $admin->id)->exists())->toBeTrue();
});

test('admin cannot suspend themselves', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin-kampus');

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('toggleSuspend', $admin->id)
        ->assertSee('tidak dapat menangguhkan akunnya sendiri');

    $admin->refresh();
    expect($admin->isSuspended())->toBeFalse();
});

test('suspended user is logged out and blocked on web request', function () {
    $user = User::factory()->create([
        'is_suspended' => true,
    ]);
    $user->assignRole('user');

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('suspended user is blocked on api request with 403', function () {
    $user = User::factory()->create([
        'is_suspended' => true,
    ]);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/quota');
    $response->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Akun Anda telah ditangguhkan. Silakan hubungi administrator kampus.',
        ]);
});

test('admin can override user quota and activity log is recorded', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $user = User::factory()->create([
        'quota_bytes' => 5 * 1024 * 1024 * 1024, // 5 GB
    ]);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openQuotaModal', $user->id)
        ->set('customQuotaGb', 25)
        ->call('saveQuota');

    $user->refresh();
    expect($user->quota_bytes)->toBe(25 * 1024 * 1024 * 1024);

    expect(ActivityLog::where('action', 'user_quota_override')
        ->where('user_id', $admin->id)
        ->exists())->toBeTrue();
});
