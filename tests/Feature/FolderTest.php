<?php

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('folder supports nested hierarchy and parent-child relations', function () {
    $user = User::factory()->create();

    $rootFolder = Folder::factory()->create([
        'user_id' => $user->id,
        'parent_id' => null,
        'name' => 'Root Folder',
    ]);

    $childFolder = Folder::factory()->create([
        'user_id' => $user->id,
        'parent_id' => $rootFolder->id,
        'name' => 'Sub Folder 1',
    ]);

    $grandChildFolder = Folder::factory()->create([
        'user_id' => $user->id,
        'parent_id' => $childFolder->id,
        'name' => 'Sub Sub Folder',
    ]);

    expect($rootFolder->children)->toHaveCount(1)
        ->and($childFolder->parent->id)->toBe($rootFolder->id)
        ->and($grandChildFolder->parent->id)->toBe($childFolder->id);

    // Test breadcrumbs
    $breadcrumbs = $grandChildFolder->getBreadcrumbs();
    expect($breadcrumbs)->toHaveCount(3)
        ->and($breadcrumbs[0]['name'])->toBe('Root Folder')
        ->and($breadcrumbs[1]['name'])->toBe('Sub Folder 1')
        ->and($breadcrumbs[2]['name'])->toBe('Sub Sub Folder');
});

test('folder root scope returns only top-level folders', function () {
    $user = User::factory()->create();

    $root1 = Folder::factory()->create(['user_id' => $user->id, 'parent_id' => null]);
    $root2 = Folder::factory()->create(['user_id' => $user->id, 'parent_id' => null]);
    Folder::factory()->create(['user_id' => $user->id, 'parent_id' => $root1->id]);

    $rootFolders = Folder::root()->get();

    expect($rootFolders)->toHaveCount(2)
        ->and($rootFolders->pluck('id')->all())->toContain($root1->id, $root2->id);
});

test('folder can be soft deleted and restored', function () {
    $folder = Folder::factory()->create();

    $folder->delete();

    expect(Folder::count())->toBe(0)
        ->and(Folder::withTrashed()->count())->toBe(1);

    $folder->restore();

    expect(Folder::count())->toBe(1);
});
