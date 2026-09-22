<?php

use App\Jobs\GenerateFolderZipJob;
use App\Models\ActivityLog;
use App\Models\Folder;
use App\Models\User;
use App\Services\FileService;
use App\Services\ZipService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    config(['cloudcampus.disk' => 'local']);
});

test('authenticated user can download folder as valid zip archive with nested hierarchy', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $rootFolder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Skripsi']);
    $subFolder = Folder::factory()->create(['user_id' => $user->id, 'parent_id' => $rootFolder->id, 'name' => 'Data']);

    $fileService = app(FileService::class);
    $file1 = $fileService->upload($user, UploadedFile::fake()->create('bab1.docx', 10, 'text/plain'), $rootFolder->id);
    $file2 = $fileService->upload($user, UploadedFile::fake()->create('kuisioner.csv', 5, 'text/csv'), $subFolder->id);

    $response = $this->actingAs($user)->get("/folders/{$rootFolder->id}/download");

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/zip')
        ->and($response->headers->get('content-disposition'))->toContain('Skripsi.zip');

    // Verifikasi bahwa file ZIP yang dihasilkan benar-benar valid dan terbaca oleh ZipArchive
    $tempZip = $response->getFile()->getPathname();

    $zip = new ZipArchive();
    $status = $zip->open($tempZip);
    expect($status)->toBeTrue();

    // Verifikasi keberadaan file di dalam hierarki ZIP
    $hasBab1 = $zip->locateName('Skripsi/bab1.docx') !== false;
    $hasKuisioner = $zip->locateName('Skripsi/Data/kuisioner.csv') !== false;

    $zip->close();

    expect($hasBab1)->toBeTrue()
        ->and($hasKuisioner)->toBeTrue();

    // Activity log harus tercatat
    expect(ActivityLog::where('action', 'folder_download_zip')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('empty folder download produces valid zip archive containing empty folder directory', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $emptyFolder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Folder Kosong']);

    $response = $this->actingAs($user)->get("/folders/{$emptyFolder->id}/download");

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/zip');

    $tempZip = $response->getFile()->getPathname();

    $zip = new ZipArchive();
    $status = $zip->open($tempZip);
    expect($status)->toBeTrue()
        ->and($zip->numFiles)->toBeGreaterThanOrEqual(1);

    $zip->close();
});

test('unauthorized user cannot download another users folder as zip', function () {
    $owner = User::factory()->create();
    $owner->assignRole('user');

    $attacker = User::factory()->create();
    $attacker->assignRole('user');

    $folder = Folder::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($attacker)
        ->get("/folders/{$folder->id}/download")
        ->assertForbidden();
});

test('api folder download streams zip or queues background job when requested', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    Sanctum::actingAs($user);

    $folder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Arsip']);

    // Direct download via API
    $responseDirect = $this->get("/api/v1/folders/{$folder->id}/download");
    $responseDirect->assertOk();
    expect($responseDirect->headers->get('content-type'))->toBe('application/zip');

    // Asynchronous queue download via API with ?async=1
    Queue::fake();

    $responseAsync = $this->getJson("/api/v1/folders/{$folder->id}/download?async=1");
    $responseAsync->assertStatus(202)
        ->assertJsonPath('status', 'queued');

    Queue::assertPushed(GenerateFolderZipJob::class, function ($job) use ($folder, $user) {
        return $job->folder->id === $folder->id && $job->user->id === $user->id;
    });
});

test('generate folder zip job creates archive in user storage and logs activity', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $folder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Proyek']);
    $fileService = app(FileService::class);
    $fileService->upload($user, UploadedFile::fake()->create('desain.txt', 10, 'text/plain'), $folder->id);

    $job = new GenerateFolderZipJob($folder, $user);
    $archivePath = $job->handle(app(ZipService::class));

    expect($archivePath)->toStartWith("users/{$user->id}/archives/")
        ->and(Storage::disk('local')->exists($archivePath))->toBeTrue();

    expect(ActivityLog::where('action', 'folder_zip_completed')->where('user_id', $user->id)->exists())->toBeTrue();
});
