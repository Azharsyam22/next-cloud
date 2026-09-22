<?php

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    config(['cloudcampus.disk' => 'local']);
});

test('api file index returns files for authenticated user', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    File::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->getJson('/api/v1/files');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('api file upload handles multipart request', function () {
    $user = User::factory()->create([
        'quota_bytes' => 50 * 1024 * 1024,
        'used_bytes' => 0,
    ]);
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $uploadedFile = UploadedFile::fake()->create('riset.pdf', 500, 'application/pdf');

    $response = $this->postJson('/api/v1/files', [
        'file' => $uploadedFile,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.original_name', 'riset.pdf')
        ->assertJsonPath('data.extension', 'pdf');

    $this->assertDatabaseHas('files', [
        'user_id' => $user->id,
        'original_name' => 'riset.pdf',
    ]);
});

test('api file show returns single file', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $file = File::factory()->create(['user_id' => $user->id]);

    $response = $this->getJson("/api/v1/files/{$file->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $file->id)
        ->assertJsonPath('data.original_name', $file->original_name);
});

test('api file download streams file response', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $service = app(\App\Services\FileService::class);
    $uploadedFile = UploadedFile::fake()->create('dokumen.txt', 10, 'text/plain');
    $file = $service->upload($user, $uploadedFile);

    $response = $this->get("/api/v1/files/{$file->id}/download");

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('attachment')
        ->and($response->headers->get('content-disposition'))->toContain('dokumen.txt');
});

test('api file rename updates file name', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $file = File::factory()->create([
        'user_id' => $user->id,
        'original_name' => 'lama.pdf',
    ]);

    $response = $this->patchJson("/api/v1/files/{$file->id}", [
        'name' => 'baru',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.original_name', 'baru.pdf');
});

test('api file move transfers file to target folder', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $folder = Folder::factory()->create(['user_id' => $user->id]);
    $file = File::factory()->create(['user_id' => $user->id, 'folder_id' => null]);

    $response = $this->postJson("/api/v1/files/{$file->id}/move", [
        'folder_id' => $folder->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.folder_id', $folder->id);
});

test('api file delete soft deletes the file', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $file = File::factory()->create(['user_id' => $user->id]);

    $response = $this->deleteJson("/api/v1/files/{$file->id}");

    $response->assertOk();
    $this->assertSoftDeleted('files', ['id' => $file->id]);
});

test('api prevents unauthorized user from accessing another users file', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $attacker->assignRole('user');
    Sanctum::actingAs($attacker);

    $file = File::factory()->create(['user_id' => $owner->id]);

    $this->getJson("/api/v1/files/{$file->id}")->assertForbidden();
    $this->patchJson("/api/v1/files/{$file->id}", ['name' => 'hacked'])->assertForbidden();
    $this->deleteJson("/api/v1/files/{$file->id}")->assertForbidden();
});
