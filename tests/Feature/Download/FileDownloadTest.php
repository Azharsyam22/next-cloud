<?php

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\User;
use App\Services\FileService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    config(['cloudcampus.disk' => 'local']);
});

test('authenticated user can stream download their own file via web route', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $fileService = app(FileService::class);
    $uploadedFile = UploadedFile::fake()->create('dokumen_rahasia.pdf', 500, 'application/pdf');
    $file = $fileService->upload($user, $uploadedFile);

    $response = $this->actingAs($user)->get("/files/{$file->id}/download");

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('attachment')
        ->and($response->headers->get('content-disposition'))->toContain('dokumen_rahasia.pdf');

    // Pastikan activity log tercatat
    expect(ActivityLog::where('action', 'file_download')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('unauthorized user cannot download another users file', function () {
    $owner = User::factory()->create();
    $owner->assignRole('user');

    $attacker = User::factory()->create();
    $attacker->assignRole('user');

    $fileService = app(FileService::class);
    $uploadedFile = UploadedFile::fake()->create('tugas.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    $file = $fileService->upload($owner, $uploadedFile);

    $this->actingAs($attacker)
        ->get("/files/{$file->id}/download")
        ->assertForbidden();
});

test('file download throws validation exception if physical file is missing', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $file = File::factory()->create([
        'user_id' => $user->id,
        'storage_path' => 'users/1/files/missing_file.pdf',
        'original_name' => 'missing_file.pdf',
    ]);

    $fileService = app(FileService::class);

    expect(fn () => $fileService->download($file))
        ->toThrow(ValidationException::class);
});
