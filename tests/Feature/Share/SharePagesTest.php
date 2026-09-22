<?php

use App\Livewire\MySharedLinks;
use App\Livewire\SharedWithMe;
use App\Livewire\ShareModal;
use App\Models\File;
use App\Models\Share;
use App\Models\User;
use App\Services\ShareService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('my shared links page requires authentication and renders component', function () {
    $this->get('/shares/mine')->assertRedirect('/login');

    $user = User::factory()->create();
    $user->assignRole('user');

    $file = File::factory()->create(['user_id' => $user->id, 'original_name' => 'dokumen_saya.pdf']);
    $shareService = app(ShareService::class);
    $share = $shareService->createPublicShare($file, $user, 'download');

    $this->actingAs($user)
        ->get('/shares/mine')
        ->assertOk()
        ->assertSeeLivewire(MySharedLinks::class);

    Livewire::actingAs($user)
        ->test(MySharedLinks::class)
        ->assertSee('dokumen_saya.pdf')
        ->call('revoke', $share->id);

    expect($share->fresh()->is_active)->toBeFalse();
});

test('shared with me page requires authentication and displays items shared to user', function () {
    $this->get('/shares/with-me')->assertRedirect('/login');

    $owner = User::factory()->create(['name' => 'Dosen Pengampu']);
    $recipient = User::factory()->create();
    $recipient->assignRole('user');

    $file = File::factory()->create(['user_id' => $owner->id, 'original_name' => 'silabus.pdf']);
    $shareService = app(ShareService::class);
    $shareService->shareWithUser($file, $owner, $recipient, 'download');

    $this->actingAs($recipient)
        ->get('/shares/with-me')
        ->assertOk()
        ->assertSeeLivewire(SharedWithMe::class);

    Livewire::actingAs($recipient)
        ->test(SharedWithMe::class)
        ->assertSee('silabus.pdf')
        ->assertSee('Dosen Pengampu');
});

test('share modal component can toggle public link and add collaborator', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $recipient = User::factory()->create(['name' => 'Rekan Mahasiswa', 'email' => 'rekan@kampus.ac.id']);

    $file = File::factory()->create(['user_id' => $user->id, 'original_name' => 'tugas_kelompok.docx']);

    Livewire::actingAs($user)
        ->test(ShareModal::class)
        ->dispatch('open-share-modal', type: 'file', id: $file->id)
        ->assertSet('isOpen', true)
        ->assertSet('itemName', 'tugas_kelompok.docx')
        ->set('isPublicActive', true)
        ->call('togglePublicShare')
        ->call('addCollaborator', $recipient->id);

    expect(Share::where('user_id', $user->id)->whereNull('shared_with_user_id')->exists())->toBeTrue()
        ->and(Share::where('user_id', $user->id)->where('shared_with_user_id', $recipient->id)->exists())->toBeTrue();
});
