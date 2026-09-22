<?php

use App\Models\File;
use App\Models\User;
use App\Services\FileService;
use App\Services\ShareService;
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

test('api user can list their created shares', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $file = File::factory()->create(['user_id' => $user->id]);
    $shareService = app(ShareService::class);
    $shareService->createPublicShare($file, $user);

    $response = $this->getJson('/api/v1/shares');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

test('api user can create public share', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $file = File::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/v1/shares', [
        'type' => 'file',
        'id' => $file->id,
        'permission' => 'download',
        'expires_in_days' => 7,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.permission', 'download')
        ->assertJsonPath('data.is_public', true);
});

test('api user can create private share for registered user', function () {
    $user = User::factory()->create();
    $recipient = User::factory()->create(['email' => 'tujuan@kampus.ac.id']);
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $file = File::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/v1/shares', [
        'type' => 'file',
        'id' => $file->id,
        'permission' => 'view',
        'recipient_email' => 'tujuan@kampus.ac.id',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.permission', 'view')
        ->assertJsonPath('data.is_public', false)
        ->assertJsonPath('data.shared_with.email', 'tujuan@kampus.ac.id');
});

test('public api can retrieve share info and respects download permissions', function () {
    $owner = User::factory()->create();
    $fileService = app(FileService::class);
    $file = $fileService->upload($owner, UploadedFile::fake()->create('ebook.pdf', 50, 'application/pdf'));

    $shareService = app(ShareService::class);
    $shareView = $shareService->createPublicShare($file, $owner, 'view');

    // Public API info
    $responseInfo = $this->getJson("/api/v1/public/shares/{$shareView->token}");
    $responseInfo->assertOk()
        ->assertJsonPath('data.item.name', 'ebook.pdf')
        ->assertJsonPath('data.can_download', false);

    // Download ditolak jika view-only
    $this->get("/api/v1/public/shares/{$shareView->token}/download")
        ->assertForbidden();

    // Diizinkan jika download permission
    $shareDownload = $shareService->createPublicShare($file, $owner, 'download');
    $this->get("/api/v1/public/shares/{$shareDownload->token}/download")
        ->assertOk();
});
