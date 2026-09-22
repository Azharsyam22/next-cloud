<?php

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('file can belong to user and folder or root', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->create(['user_id' => $user->id]);

    $fileInFolder = File::factory()->create([
        'user_id' => $user->id,
        'folder_id' => $folder->id,
        'original_name' => 'document.pdf',
        'stored_name' => 'random-uuid.pdf',
        'storage_path' => 'users/'.$user->id.'/files/random-uuid.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1048576, // 1 MB
    ]);

    $fileAtRoot = File::factory()->create([
        'user_id' => $user->id,
        'folder_id' => null,
    ]);

    expect($fileInFolder->folder->id)->toBe($folder->id)
        ->and($fileInFolder->user->id)->toBe($user->id)
        ->and($fileAtRoot->folder)->toBeNull();
});

test('file formatted size and mime helpers work correctly', function () {
    $pdf = File::factory()->create([
        'mime_type' => 'application/pdf',
        'size' => 2097152, // 2 MB
        'original_name' => 'test.pdf',
    ]);

    $image = File::factory()->create([
        'mime_type' => 'image/png',
        'size' => 512, // 512 B
        'original_name' => 'photo.png',
    ]);

    $archive = File::factory()->create([
        'mime_type' => 'application/zip',
        'size' => 1073741824, // 1 GB
        'original_name' => 'archive.zip',
    ]);

    expect($pdf->formatted_size)->toBe('2 MB')
        ->and($pdf->isPdf())->toBeTrue()
        ->and($pdf->isImage())->toBeFalse()
        ->and($pdf->extension)->toBe('pdf')
        ->and($image->formatted_size)->toBe('512 B')
        ->and($image->isImage())->toBeTrue()
        ->and($archive->formatted_size)->toBe('1 GB')
        ->and($archive->isArchive())->toBeTrue();
});

test('file can be soft deleted and restored', function () {
    $file = File::factory()->create();

    $file->delete();

    expect(File::count())->toBe(0)
        ->and(File::withTrashed()->count())->toBe(1);

    $file->restore();

    expect(File::count())->toBe(1);
});
