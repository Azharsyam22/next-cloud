<?php

use App\Livewire\FileExplorer;
use App\Models\Folder;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('file explorer component renders successfully on dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user);

    Livewire::test(FileExplorer::class)
        ->assertStatus(200)
        ->assertSee('Semua File')
        ->assertSee('Folder Baru');
});

test('file explorer can create new folder', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user);

    Livewire::test(FileExplorer::class)
        ->call('openCreateModal')
        ->assertSet('showCreateModal', true)
        ->set('newFolderName', 'Folder Ujian')
        ->set('newFolderColor', '#10B981')
        ->call('createFolder')
        ->assertSet('showCreateModal', false)
        ->assertSee('Folder Ujian');

    expect(Folder::where('name', 'Folder Ujian')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('file explorer can rename folder', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $folder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Draft']);

    $this->actingAs($user);

    Livewire::test(FileExplorer::class)
        ->call('openRenameModal', $folder->id)
        ->assertSet('showRenameModal', true)
        ->assertSet('editFolderName', 'Draft')
        ->set('editFolderName', 'Final')
        ->call('renameFolder')
        ->assertSet('showRenameModal', false);

    expect($folder->fresh()->name)->toBe('Final');
});

test('file explorer can delete folder', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $folder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Untuk Dihapus']);

    $this->actingAs($user);

    Livewire::test(FileExplorer::class)
        ->call('confirmDelete', $folder->id)
        ->assertSet('showDeleteModal', true)
        ->call('deleteFolder')
        ->assertSet('showDeleteModal', false);

    expect(Folder::where('id', $folder->id)->exists())->toBeFalse()
        ->and(Folder::withTrashed()->where('id', $folder->id)->exists())->toBeTrue();
});

test('file explorer can navigate into folder', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $parent = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Mata Kuliah']);

    $this->actingAs($user);

    Livewire::test(FileExplorer::class)
        ->call('navigateToFolder', $parent->id)
        ->assertSet('currentFolderId', $parent->id)
        ->assertSee('Mata Kuliah');
});
