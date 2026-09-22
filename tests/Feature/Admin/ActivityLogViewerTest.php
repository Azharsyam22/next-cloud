<?php

use App\Livewire\Admin\ActivityLogViewer;
use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can view activity log list and modal detail', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin-kampus');

    $log = ActivityLog::record(
        'file_upload',
        null,
        'Mengunggah berkas riset_ai.pdf',
        ['size' => 1024, 'extension' => 'pdf'],
        $admin->id
    );

    Livewire::actingAs($admin)
        ->test(ActivityLogViewer::class)
        ->assertOk()
        ->assertSee('file_upload')
        ->assertSee('Mengunggah berkas riset_ai.pdf')
        ->call('viewDetails', $log->id)
        ->assertSee('Rincian Audit Log')
        ->assertSee('riset_ai.pdf');
});

test('admin can filter activity logs by action', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    ActivityLog::record('file_upload', null, 'Upload dokumen skripsi', [], $admin->id);
    ActivityLog::record('file_delete', null, 'Hapus dokumen lama', [], $admin->id);

    Livewire::actingAs($admin)
        ->test(ActivityLogViewer::class)
        ->set('action', 'file_upload')
        ->assertSee('Upload dokumen skripsi')
        ->assertDontSee('Hapus dokumen lama')
        ->set('action', 'file_delete')
        ->assertSee('Hapus dokumen lama')
        ->assertDontSee('Upload dokumen skripsi');
});
