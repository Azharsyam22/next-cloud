<?php

namespace Tests\Feature\Academic;

use App\Models\File;
use App\Models\User;
use App\Services\SsoService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AcademicIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_sso_authentication_flow_registers_academic_student(): void
    {
        $ssoService = app(SsoService::class);
        $payload = [
            'external_id' => 'NIM20241001',
            'email' => 'mahasiswa1@kampus.ac.id',
            'name' => 'Ahmad Mahasiswa',
            'exp' => time() + 3600,
        ];

        $token = $ssoService->createTokenForTesting($payload);

        $response = $this->get('/auth/sso/callback?token='.$token);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $student = User::where('external_id', 'NIM20241001')->first();
        $this->assertNotNull($student);
        $this->assertEquals('academic', $student->account_type);
        $this->assertNull($student->password);
        $this->assertTrue($student->hasRole('user'));
    }

    public function test_academic_system_admin_can_upload_file_on_behalf_of_student_by_external_id(): void
    {
        // 1. Buat mahasiswa via SSO / existing academic user
        $student = User::factory()->create([
            'external_id' => 'NIM20241002',
            'name' => 'Siti Mahasiswi',
            'email' => 'siti@kampus.ac.id',
            'account_type' => 'academic',
            'password' => null,
            'quota_bytes' => 5 * 1024 * 1024 * 1024,
            'used_bytes' => 0,
        ]);
        $student->assignRole('user');

        // 2. Buat akun service/admin Sistem Akademik
        $academicAdmin = User::factory()->create([
            'name' => 'Sistem Akademik Server API',
            'email' => 'siakad-service@kampus.ac.id',
            'account_type' => 'academic',
        ]);
        $academicAdmin->assignRole('admin-kampus');

        Sanctum::actingAs($academicAdmin, ['*']);

        // 3. Upload berkas tugas atas nama mahasiswa menggunakan external_id
        $dummyFile = UploadedFile::fake()->create('tugas_akhir_nim20241002.pdf', 1024, 'application/pdf');

        $response = $this->postJson('/api/v1/files', [
            'file' => $dummyFile,
            'external_id' => 'NIM20241002',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.original_name', 'tugas_akhir_nim20241002.pdf')
            ->assertJsonPath('data.user_id', $student->id);

        // 4. Verifikasi berkas tersimpan pada kepemilikan mahasiswa
        $fileRecord = File::where('original_name', 'tugas_akhir_nim20241002.pdf')->first();
        $this->assertNotNull($fileRecord);
        $this->assertEquals($student->id, $fileRecord->user_id);

        // 5. Verifikasi kuota mahasiswa bertambah sesuai ukuran berkas
        $student->refresh();
        $this->assertEquals($fileRecord->size, $student->used_bytes);

        // 6. Verifikasi isolasi folder fisik di disk storage
        Storage::disk('local')->assertExists("users/{$student->id}/files/{$fileRecord->stored_name}");
    }

    public function test_academic_system_can_auto_provision_student_on_first_upload(): void
    {
        $academicAdmin = User::factory()->create();
        $academicAdmin->assignRole('super-admin');

        Sanctum::actingAs($academicAdmin, ['*']);

        $dummyFile = UploadedFile::fake()->create('sk_yudisium.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/v1/files', [
            'file' => $dummyFile,
            'external_id' => 'NIM20249999',
            'user_name' => 'Budi Santoso',
            'user_email' => 'budi.santoso@kampus.ac.id',
        ]);

        $response->assertStatus(201);

        $newStudent = User::where('external_id', 'NIM20249999')->first();
        $this->assertNotNull($newStudent);
        $this->assertEquals('Budi Santoso', $newStudent->name);
        $this->assertEquals('academic', $newStudent->account_type);
        $this->assertTrue($newStudent->hasRole('user'));

        $this->assertDatabaseHas('files', [
            'user_id' => $newStudent->id,
            'original_name' => 'sk_yudisium.pdf',
        ]);
    }

    public function test_regular_user_cannot_upload_file_on_behalf_of_another_user(): void
    {
        $regularUser = User::factory()->create();
        $regularUser->assignRole('user');

        $targetUser = User::factory()->create([
            'external_id' => 'NIM999',
        ]);
        $targetUser->assignRole('user');

        Sanctum::actingAs($regularUser, ['*']);

        $dummyFile = UploadedFile::fake()->create('hacked.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/v1/files', [
            'file' => $dummyFile,
            'external_id' => 'NIM999',
        ]);

        $response->assertStatus(403);
    }

    public function test_academic_admin_can_list_and_download_student_files(): void
    {
        $student = User::factory()->create(['external_id' => 'NIM20247777']);
        $student->assignRole('user');

        $academicAdmin = User::factory()->create();
        $academicAdmin->assignRole('admin-kampus');

        // Student punya 1 file
        $storagePath = "users/{$student->id}/files/uuid_skripsi.pdf";
        $file = File::factory()->create([
            'user_id' => $student->id,
            'original_name' => 'berkas_skripsi.pdf',
            'stored_name' => 'uuid_skripsi.pdf',
            'storage_path' => $storagePath,
            'size' => 2048,
            'mime_type' => 'application/pdf',
        ]);
        Storage::disk('local')->put($storagePath, 'konten skripsi');

        Sanctum::actingAs($academicAdmin, ['*']);

        // 1. List berkas mahasiswa via external_id
        $listResponse = $this->getJson('/api/v1/files?external_id=NIM20247777');
        $listResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.original_name', 'berkas_skripsi.pdf');

        // 2. Download berkas mahasiswa oleh admin kampus
        $downloadResponse = $this->get('/api/v1/files/'.$file->id.'/download');
        $downloadResponse->assertOk();
    }

    public function test_api_rate_limiting_is_enforced(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/quota');

        $response->assertOk();
        $this->assertTrue(
            $response->headers->has('X-RateLimit-Limit') || $response->headers->has('x-ratelimit-limit'),
            'Response header harus memuat informasi Rate Limiting'
        );
    }
}
