<?php

use App\Models\File;
use App\Models\User;
use App\Services\FileService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
});

test('file observer automatically increments user used_bytes on file creation', function () {
    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);

    File::factory()->create([
        'user_id' => $user->id,
        'size' => 2048,
    ]);

    $user->refresh();
    expect($user->used_bytes)->toBe(2048);
});

test('file service upload does not double increment used_bytes', function () {
    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);

    $service = app(FileService::class);
    $uploadedFile = UploadedFile::fake()->create('tugas.pdf', 500, 'application/pdf');

    $file = $service->upload($user, $uploadedFile);

    $user->refresh();
    expect($user->used_bytes)->toBe($file->size);
});

test('soft deleting a file does not decrease used_bytes because disk storage is still occupied', function () {
    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);

    $file = File::factory()->create([
        'user_id' => $user->id,
        'size' => 5000,
    ]);

    $user->refresh();
    expect($user->used_bytes)->toBe(5000);

    $service = app(FileService::class);
    $service->delete($file);

    $user->refresh();
    expect($user->used_bytes)->toBe(5000);
});

test('file observer automatically decrements used_bytes on permanent deletion', function () {
    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);

    $service = app(FileService::class);
    $uploadedFile = UploadedFile::fake()->create('foto.jpg', 300, 'image/jpeg');
    $file = $service->upload($user, $uploadedFile);

    $user->refresh();
    expect($user->used_bytes)->toBe($file->size);

    $service->permanentDelete($file);

    $user->refresh();
    expect($user->used_bytes)->toBe(0);
});
