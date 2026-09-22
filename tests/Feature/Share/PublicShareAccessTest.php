<?php

use App\Models\Folder;
use App\Models\User;
use App\Services\FileService;
use App\Services\ShareService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    config(['cloudcampus.disk' => 'local']);
});

test('guest can access public shared file preview without login', function () {
    $owner = User::factory()->create();
    $fileService = app(FileService::class);
    $uploadedFile = UploadedFile::fake()->create('modul_kuliah.pdf', 300, 'application/pdf');
    $file = $fileService->upload($owner, $uploadedFile);

    $shareService = app(ShareService::class);
    $share = $shareService->createPublicShare($file, $owner, 'view');

    $response = $this->get("/s/{$share->token}");

    $response->assertOk()
        ->assertSee('modul_kuliah.pdf')
        ->assertSee('Hanya Lihat');
});

test('public share with view permission strictly denies file download with 403 forbidden', function () {
    $owner = User::factory()->create();
    $fileService = app(FileService::class);
    $uploadedFile = UploadedFile::fake()->create('rahasia.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    $file = $fileService->upload($owner, $uploadedFile);

    $shareService = app(ShareService::class);
    $share = $shareService->createPublicShare($file, $owner, 'view');

    // Percobaan mengunduh harus ditolak dengan 403
    $this->get("/s/{$share->token}/download")
        ->assertForbidden();
});

test('public share with download permission allows file and folder zip download', function () {
    $owner = User::factory()->create();
    $fileService = app(FileService::class);
    $uploadedFile = UploadedFile::fake()->create('materi.pdf', 200, 'application/pdf');
    $file = $fileService->upload($owner, $uploadedFile);

    $shareService = app(ShareService::class);
    $shareFile = $shareService->createPublicShare($file, $owner, 'download');

    $responseFile = $this->get("/s/{$shareFile->token}/download");
    $responseFile->assertOk();
    expect($responseFile->headers->get('content-type'))->toBe('application/pdf');

    // Folder Download sebagai ZIP
    $folder = Folder::factory()->create(['user_id' => $owner->id, 'name' => 'Kumpulan Materi']);
    $fileService->upload($owner, UploadedFile::fake()->create('bab1.txt', 10, 'text/plain'), $folder->id);

    $shareFolder = $shareService->createPublicShare($folder, $owner, 'download');

    $responseFolder = $this->get("/s/{$shareFolder->token}/download");
    $responseFolder->assertOk();
    expect($responseFolder->headers->get('content-type'))->toBe('application/zip');
});

test('expired public share returns 410 gone', function () {
    $owner = User::factory()->create();
    $fileService = app(FileService::class);
    $file = $fileService->upload($owner, UploadedFile::fake()->create('catatan.txt', 5, 'text/plain'));

    $shareService = app(ShareService::class);
    $share = $shareService->createPublicShare($file, $owner, 'view');
    $share->update(['expires_at' => now()->subMinute()]);

    $this->get("/s/{$share->token}")
        ->assertStatus(410);

    $this->get("/s/{$share->token}/download")
        ->assertStatus(410);
});
