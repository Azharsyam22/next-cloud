<?php

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use App\Services\FolderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('folder service can create root and nested folders', function () {
    $user = User::factory()->create();
    $service = app(FolderService::class);

    $root = $service->create($user, 'Dokumen Kampus', null, '#3B82F6');

    expect($root->name)->toBe('Dokumen Kampus')
        ->and($root->parent_id)->toBeNull()
        ->and($root->user_id)->toBe($user->id)
        ->and($root->color)->toBe('#3B82F6');

    $sub = $service->create($user, 'Semester 1', $root->id, '#10B981');

    expect($sub->name)->toBe('Semester 1')
        ->and($sub->parent_id)->toBe($root->id)
        ->and($sub->user_id)->toBe($user->id);
});

test('folder service prevents duplicate folder names in the same parent', function () {
    $user = User::factory()->create();
    $service = app(FolderService::class);

    $service->create($user, 'Skripsi', null);

    expect(fn () => $service->create($user, 'Skripsi', null))
        ->toThrow(ValidationException::class);
});

test('folder service can rename folder and rejects name collision', function () {
    $user = User::factory()->create();
    $service = app(FolderService::class);

    $folder1 = $service->create($user, 'Folder A');
    $folder2 = $service->create($user, 'Folder B');

    $renamed = $service->rename($folder1, 'Folder A Baru');
    expect($renamed->name)->toBe('Folder A Baru');

    // Tabrakan nama dengan Folder B
    expect(fn () => $service->rename($folder1, 'Folder B'))
        ->toThrow(ValidationException::class);
});

test('folder service moves folder and strictly prevents circular references', function () {
    $user = User::factory()->create();
    $service = app(FolderService::class);

    $root = $service->create($user, 'Root');
    $sub1 = $service->create($user, 'Sub 1', $root->id);
    $sub2 = $service->create($user, 'Sub 2', $sub1->id);

    // Coba memindahkan Root ke dalam Sub 2 (anak dari anak) -> Harus dicegah!
    expect(fn () => $service->move($root, $sub2->id))
        ->toThrow(ValidationException::class);

    // Coba memindahkan Sub 1 ke dalam dirinya sendiri -> Harus dicegah!
    expect(fn () => $service->move($sub1, $sub1->id))
        ->toThrow(ValidationException::class);

    // Pemindahan yang sah: pindahkan Sub 2 ke Root level
    $moved = $service->move($sub2, null);
    expect($moved->parent_id)->toBeNull();
});

test('folder service soft deletes folder and cascade deletes all child files and subfolders', function () {
    $user = User::factory()->create();
    $service = app(FolderService::class);

    $root = $service->create($user, 'Folder Induk');
    $sub = $service->create($user, 'Subfolder', $root->id);

    $fileInRoot = File::factory()->create(['user_id' => $user->id, 'folder_id' => $root->id]);
    $fileInSub = File::factory()->create(['user_id' => $user->id, 'folder_id' => $sub->id]);

    $service->delete($root);

    expect(Folder::where('id', $root->id)->exists())->toBeFalse()
        ->and(Folder::where('id', $sub->id)->exists())->toBeFalse()
        ->and(File::where('id', $fileInRoot->id)->exists())->toBeFalse()
        ->and(File::where('id', $fileInSub->id)->exists())->toBeFalse()
        ->and(Folder::withTrashed()->where('id', $root->id)->exists())->toBeTrue()
        ->and(File::withTrashed()->where('id', $fileInRoot->id)->exists())->toBeTrue();
});

test('folder service listContents returns contents and accurate breadcrumbs', function () {
    $user = User::factory()->create();
    $service = app(FolderService::class);

    $root = $service->create($user, 'Tugas');
    $sub = $service->create($user, 'Kalkulus', $root->id);

    File::factory()->create(['user_id' => $user->id, 'folder_id' => $sub->id, 'original_name' => 'Tugas1.pdf']);

    $contents = $service->listContents($user, $sub->id);

    expect($contents['current_folder']->id)->toBe($sub->id)
        ->and($contents['breadcrumbs'])->toHaveCount(3)
        ->and($contents['breadcrumbs'][0]['name'])->toBe('Semua File')
        ->and($contents['breadcrumbs'][1]['name'])->toBe('Tugas')
        ->and($contents['breadcrumbs'][2]['name'])->toBe('Kalkulus')
        ->and($contents['files'])->toHaveCount(1);
});
