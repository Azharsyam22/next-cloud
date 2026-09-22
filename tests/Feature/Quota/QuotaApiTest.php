<?php

use App\Models\File;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('quota api endpoint requires sanctum authentication', function () {
    $response = $this->getJson('/api/v1/quota');
    $response->assertUnauthorized();
});

test('authenticated user can retrieve quota stats and breakdown via api', function () {
    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);
    Sanctum::actingAs($user);

    File::factory()->create([
        'user_id' => $user->id,
        'original_name' => 'proposal.docx',
        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'size' => 1024 * 1024,
    ]);

    $response = $this->getJson('/api/v1/quota');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'stats' => [
                    'quota_bytes',
                    'used_bytes',
                    'remaining_bytes',
                    'percentage',
                    'is_warning',
                    'is_critical',
                    'formatted_quota',
                    'formatted_used',
                    'formatted_remaining',
                    'trash_bytes',
                    'formatted_trash',
                ],
                'breakdown',
                'largest_files',
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);
});

test('authenticated user can trigger quota recalculation via api', function () {
    $user = User::factory()->create([
        'quota_bytes' => 10 * 1024 * 1024,
        'used_bytes' => 0,
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/quota/recalculate');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Kuota penyimpanan berhasil disinkronisasi.',
        ]);
});
