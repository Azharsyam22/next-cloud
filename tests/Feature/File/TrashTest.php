<?php

use App\Livewire\TrashExplorer;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    config(['cloudcampus.disk' => 'local']);
});

test('trash page is accessible only to authenticated users', function () {
    $this->get('/trash')->assertRedirect('/login');

    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user)
        ->get('/trash')
        ->assertOk()
        ->assertSeeLivewire(TrashExplorer::class);
});

test('trash explorer lists trashed files and folders', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $activeFile = File::factory()->create(['user_id' => $user->id, 'original_name' => 'aktif.pdf']);
    $trashedFile = File::factory()->create(['user_id' => $user->id, 'original_name' => 'dibuang.pdf']);
    $trashedFile->delete();

    $trashedFolder = Folder::factory()->create(['user_id' => $user->id, 'name' => 'Folder Lama']);
    $trashedFolder->delete();

    Livewire::actingAs($user)
        ->test(TrashExplorer::class)
        ->assertSee('dibuang.pdf')
        ->assertSee('Folder Lama')
        ->assertDontSee('aktif.pdf');
});

test('trash explorer can restore file and folder', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $trashedFile = File::factory()->create(['user_id' => $user->id]);
    $trashedFile->delete();

    $trashedFolder = Folder::factory()->create(['user_id' => $user->id]);
    $trashedFolder->delete();

    Livewire::actingAs($user)
        ->test(TrashExplorer::class)
        ->call('restoreFile', $trashedFile->id)
        ->call('restoreFolder', $trashedFolder->id);

    expect(File::where('id', $trashedFile->id)->exists())->toBeTrue()
        ->and(Folder::where('id', $trashedFolder->id)->exists())->toBeTrue();
});

test('trash explorer can force delete file and reclaim quota', function () {
    $user = User::factory()->create(['quota_bytes' => 10 * 1024 * 1024, 'used_bytes' => 0]);
    $user->assignRole('user');

    $service = app(\App\Services\FileService::class);
    $uploadedFile = UploadedFile::fake()->create('dokumen.txt', 200, 'text/plain');
    $file = $service->upload($user, $uploadedFile);

    $service->delete($file);

    expect(File::onlyTrashed()->where('id', $file->id)->exists())->toBeTrue();

    Livewire::actingAs($user)
        ->test(TrashExplorer::class)
        ->call('forceDeleteFile', $file->id);

    expect(File::withTrashed()->where('id', $file->id)->exists())->toBeFalse();
    $user->refresh();
    expect($user->used_bytes)->toBe(0);
});

test('trash explorer can empty entire trash', function () {
    $user = User::factory()->create(['quota_bytes' => 10 * 1024 * 1024, 'used_bytes' => 0]);
    $user->assignRole('user');

    $service = app(\App\Services\FileService::class);
    $uploadedFile = UploadedFile::fake()->create('dokumen.txt', 200, 'text/plain');
    $file = $service->upload($user, $uploadedFile);
    $service->delete($file);

    $folder = Folder::factory()->create(['user_id' => $user->id]);
    $folder->delete();

    Livewire::actingAs($user)
        ->test(TrashExplorer::class)
        ->call('emptyTrash');

    expect(File::withTrashed()->where('user_id', $user->id)->count())->toBe(0)
        ->and(Folder::withTrashed()->where('user_id', $user->id)->count())->toBe(0);
});
