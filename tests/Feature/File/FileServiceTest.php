<?php

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    config(['cloudcampus.disk' => 'local']);
});

test('file service uploads file with isolated path and updates user quota', function () {
    $user = User::factory()->create([
        'quota_bytes' => 100 * 1024 * 1024, // 100 MB
        'used_bytes' => 0,
    ]);

    $service = app(FileService::class);
    $uploadedFile = UploadedFile::fake()->create('laporan.pdf', 1024, 'application/pdf'); // 1 MB

    $file = $service->upload($user, $uploadedFile, null);

    expect($file)->toBeInstanceOf(File::class)
        ->and($file->original_name)->toBe('laporan.pdf')
        ->and($file->mime_type)->toBe('application/pdf')
        ->and($file->extension)->toBe('pdf')
        ->and($file->storage_path)->toStartWith("users/{$user->id}/files/")
        ->and($file->user_id)->toBe($user->id);

    Storage::disk('local')->assertExists($file->storage_path);

    $user->refresh();
    expect($user->used_bytes)->toBeGreaterThan(0);
});

test('file service rejects disallowed MIME types', function () {
    $user = User::factory()->create([
        'quota_bytes' => 100 * 1024 * 1024,
        'used_bytes' => 0,
    ]);

    $service = app(FileService::class);
    // Disallowed file type: executable
    $fakeExe = UploadedFile::fake()->create('malware.exe', 500, 'application/x-msdownload');

    expect(fn () => $service->upload($user, $fakeExe, null))
        ->toThrow(ValidationException::class);
});

test('file service rejects upload when user quota is exceeded', function () {
    $user = User::factory()->create([
        'quota_bytes' => 1024 * 1024, // 1 MB quota
        'used_bytes' => 900 * 1024,   // 900 KB used
    ]);

    $service = app(FileService::class);
    // File size 200 KB -> 900 KB + 200 KB = 1100 KB > 1024 KB
    $fakeFile = UploadedFile::fake()->create('large.pdf', 200, 'application/pdf');

    expect(fn () => $service->upload($user, $fakeFile, null))
        ->toThrow(ValidationException::class);
});

test('file service renames file and preserves extension', function () {
    $user = User::factory()->create();
    $service = app(FileService::class);

    $file = File::factory()->create([
        'user_id' => $user->id,
        'original_name' => 'proposal.docx',
    ]);

    $renamed = $service->rename($file, 'proposal_final');
    expect($renamed->original_name)->toBe('proposal_final.docx');

    $renamedWithExt = $service->rename($file, 'proposal_v2.docx');
    expect($renamedWithExt->original_name)->toBe('proposal_v2.docx');
});

test('file service moves file between folders', function () {
    $user = User::factory()->create();
    $service = app(FileService::class);

    $folder1 = Folder::factory()->create(['user_id' => $user->id]);
    $folder2 = Folder::factory()->create(['user_id' => $user->id]);

    $file = File::factory()->create([
        'user_id' => $user->id,
        'folder_id' => $folder1->id,
    ]);

    $moved = $service->move($file, $folder2->id);
    expect($moved->folder_id)->toBe($folder2->id);

    $movedToRoot = $service->move($file, null);
    expect($movedToRoot->folder_id)->toBeNull();
});

test('file service soft deletes and restores file', function () {
    $user = User::factory()->create();
    $service = app(FileService::class);

    $file = File::factory()->create(['user_id' => $user->id]);

    $service->delete($file);
    expect(File::where('id', $file->id)->exists())->toBeFalse()
        ->and(File::onlyTrashed()->where('id', $file->id)->exists())->toBeTrue();

    $service->restore($file);
    expect(File::where('id', $file->id)->exists())->toBeTrue()
        ->and(File::onlyTrashed()->where('id', $file->id)->exists())->toBeFalse();
});

test('file service permanently deletes file, removes physical disk file, and frees quota', function () {
    $user = User::factory()->create([
        'quota_bytes' => 100 * 1024 * 1024,
        'used_bytes' => 0,
    ]);

    $service = app(FileService::class);
    $uploadedFile = UploadedFile::fake()->create('data.csv', 500, 'text/csv');

    $file = $service->upload($user, $uploadedFile, null);
    $filePath = $file->storage_path;
    $fileSize = $file->size;

    $user->refresh();
    expect($user->used_bytes)->toBe($fileSize);
    Storage::disk('local')->assertExists($filePath);

    $service->permanentDelete($file);

    Storage::disk('local')->assertMissing($filePath);
    expect(File::withTrashed()->where('id', $file->id)->exists())->toBeFalse();

    $user->refresh();
    expect($user->used_bytes)->toBe(0);
});
