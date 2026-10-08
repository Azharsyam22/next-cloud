<?php

namespace Tests\Feature\Security;

use App\Models\File;
use App\Models\Share;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_api_strictly_denies_unauthenticated_requests(): void
    {
        $this->getJson('/api/v1/files')->assertUnauthorized();
        $this->getJson('/api/v1/folders')->assertUnauthorized();
        $this->getJson('/api/v1/quota')->assertUnauthorized();
        $this->getJson('/api/v1/admin/stats')->assertUnauthorized();
    }

    public function test_forbidden_mime_types_are_rejected(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Sanctum::actingAs($user, ['*']);

        // Coba upload file executable / script php
        $dangerousFile = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

        $response = $this->postJson('/api/v1/files', [
            'file' => $dangerousFile,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_file_is_stored_with_random_uuid_preventing_path_traversal(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Sanctum::actingAs($user, ['*']);

        // Coba kirimkan nama file dengan pola traversal path
        $traversalName = '../../../../etc/passwd.pdf';
        $file = UploadedFile::fake()->create($traversalName, 50, 'application/pdf');

        $response = $this->postJson('/api/v1/files', [
            'file' => $file,
        ]);

        $response->assertCreated();

        $savedFile = File::where('user_id', $user->id)->first();
        $this->assertNotNull($savedFile);

        // Pastikan nama fisik di disk adalah UUID tanpa karakter traversal '../'
        $this->assertStringNotContainsString('..', $savedFile->stored_name);
        $this->assertStringNotContainsString('/', $savedFile->stored_name);
        $this->assertStringNotContainsString('\\', $savedFile->stored_name);
        $this->assertStringStartsWith("users/{$user->id}/files/", $savedFile->storage_path);
    }

    public function test_user_cannot_access_or_download_files_of_other_users(): void
    {
        $userA = User::factory()->create();
        $userA->assignRole('user');

        $userB = User::factory()->create();
        $userB->assignRole('user');

        $fileA = File::factory()->create([
            'user_id' => $userA->id,
            'original_name' => 'rahasia_a.pdf',
            'stored_name' => 'uuid_a.pdf',
            'storage_path' => "users/{$userA->id}/files/uuid_a.pdf",
            'mime_type' => 'application/pdf',
            'size' => 100,
        ]);
        Storage::disk('local')->put($fileA->storage_path, 'secret data');

        // User B mencoba mengakses file milik User A via API
        Sanctum::actingAs($userB, ['*']);

        $this->getJson("/api/v1/files/{$fileA->id}")
            ->assertForbidden();

        $this->get("/api/v1/files/{$fileA->id}/download")
            ->assertForbidden();

        $this->deleteJson("/api/v1/files/{$fileA->id}")
            ->assertForbidden();
    }

    public function test_share_token_is_securely_random_and_at_least_64_characters(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $file = File::factory()->create(['user_id' => $user->id]);

        $share = Share::create([
            'user_id' => $user->id,
            'shareable_type' => File::class,
            'shareable_id' => $file->id,
            'permission' => 'download',
        ]);

        $this->assertGreaterThanOrEqual(64, strlen($share->token));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $share->token);
    }

    public function test_academic_account_password_modification_is_strictly_blocked(): void
    {
        $academicUser = User::factory()->create([
            'account_type' => 'academic',
            'external_id' => 'NIM20240001',
        ]);
        $academicUser->assignRole('user');

        $this->actingAs($academicUser)
            ->put('/user/password', [
                'current_password' => 'secret',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertForbidden();
    }
}
