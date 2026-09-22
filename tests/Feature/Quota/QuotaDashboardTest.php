<?php

use App\Livewire\QuotaDashboard;
use App\Models\File;
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
});

test('storage page requires authentication', function () {
    $response = $this->get(route('storage.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated user can view storage page', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this->actingAs($user)->get(route('storage.index'));
    $response->assertOk()
        ->assertSee('Kuota & Manajemen Penyimpanan', false);
});

test('quota dashboard livewire component renders accurately', function () {
    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);
    $user->assignRole('user');

    $file = File::factory()->create([
        'user_id' => $user->id,
        'original_name' => 'laporan_skripsi.pdf',
        'size' => 2 * 1024 * 1024,
        'mime_type' => 'application/pdf',
    ]);

    Livewire::actingAs($user)
        ->test(QuotaDashboard::class)
        ->assertOk()
        ->assertSee('laporan_skripsi.pdf')
        ->assertSee('Dokumen');
});

test('quota dashboard displays warning banner when quota usage is over 90 percent', function () {
    $user = User::factory()->create([
        'quota_bytes' => 1000,
        'used_bytes' => 950,
    ]);
    $user->assignRole('user');

    Livewire::actingAs($user)
        ->test(QuotaDashboard::class)
        ->assertSee('Peringatan: Kuota Penyimpanan Hampir Penuh')
        ->assertSee('95%');
});

test('quota dashboard can empty trash and reclaim user quota', function () {
    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);
    $user->assignRole('user');

    $fileService = app(\App\Services\FileService::class);
    $uploadedFile = UploadedFile::fake()->create('sampah.zip', 300, 'application/zip');
    $file = $fileService->upload($user, $uploadedFile);

    $fileService->delete($file);

    expect(File::onlyTrashed()->where('id', $file->id)->exists())->toBeTrue();

    Livewire::actingAs($user)
        ->test(QuotaDashboard::class)
        ->call('emptyTrash');

    expect(File::withTrashed()->where('id', $file->id)->exists())->toBeFalse();

    $user->refresh();
    expect($user->used_bytes)->toBe(0);
});
