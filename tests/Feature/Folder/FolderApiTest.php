<?php

use App\Models\Folder;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('api requires authentication to access folders', function () {
    $response = $this->getJson('/api/v1/folders');

    $response->assertStatus(401);
});

test('authenticated user can list their folders via api', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    Folder::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->getJson('/api/v1/folders');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'color', 'parent_id', 'breadcrumbs', 'created_at'],
            ],
        ])
        ->assertJsonCount(3, 'data');
});

test('authenticated user can create folder via api', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/folders', [
        'name' => 'Dokumen Baru API',
        'color' => '#10B981',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Dokumen Baru API')
        ->assertJsonPath('data.color', '#10B981');

    expect(Folder::where('name', 'Dokumen Baru API')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('api returns validation error when creating folder with missing name', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/folders', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('authenticated user can view single folder details via api', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $folder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Folder Khusus']);

    $response = $this->getJson('/api/v1/folders/'.$folder->id);

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $folder->id)
        ->assertJsonPath('data.name', 'Folder Khusus');
});

test('user cannot view another users folder via api', function () {
    $userA = User::factory()->create();
    $userA->assignRole('user');

    $userB = User::factory()->create();
    $userB->assignRole('user');

    $folderA = Folder::factory()->create(['user_id' => $userA->id]);

    Sanctum::actingAs($userB);

    $response = $this->getJson('/api/v1/folders/'.$folderA->id);

    $response->assertStatus(403);
});

test('authenticated user can update folder via api', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $folder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Nama Lama']);

    $response = $this->putJson('/api/v1/folders/'.$folder->id, [
        'name' => 'Nama Baru',
        'color' => '#EF4444',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Nama Baru')
        ->assertJsonPath('data.color', '#EF4444');

    expect($folder->fresh()->name)->toBe('Nama Baru');
});

test('authenticated user can move folder via api', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $parent = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Induk']);
    $folder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Anak']);

    $response = $this->postJson('/api/v1/folders/'.$folder->id.'/move', [
        'target_parent_id' => $parent->id,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.parent_id', $parent->id);

    expect($folder->fresh()->parent_id)->toBe($parent->id);
});

test('authenticated user can delete folder via api', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $folder = Folder::factory()->create(['user_id' => $user->id]);

    $response = $this->deleteJson('/api/v1/folders/'.$folder->id);

    $response->assertStatus(200)
        ->assertJsonStructure(['message']);

    expect(Folder::where('id', $folder->id)->exists())->toBeFalse()
        ->and(Folder::withTrashed()->where('id', $folder->id)->exists())->toBeTrue();
});
