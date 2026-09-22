<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Jalankan seeder roles & permissions
        $this->call(RolePermissionSeeder::class);

        // 2. Buat Super Admin bawaan sistem
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@cloudcampus.ac.id'],
            [
                'name' => 'Super Administrator',
                'password' => bcrypt('password'),
                'account_type' => 'academic',
                'external_id' => 'ADMIN-001',
                'quota_bytes' => 107374182400, // 100 GB untuk super admin
                'used_bytes' => 0,
            ]
        );
        $superAdmin->assignRole('super-admin');

        // 3. Buat contoh User Mahasiswa (Akademik)
        $student = User::firstOrCreate(
            ['email' => 'mahasiswa@cloudcampus.ac.id'],
            [
                'name' => 'Rani Mahasiswa',
                'password' => null, // SSO tanpa password lokal
                'account_type' => 'academic',
                'external_id' => '202610001',
                'quota_bytes' => 5368709120, // 5 GB
                'used_bytes' => 0,
            ]
        );
        $student->assignRole('user');

        // 4. Buat contoh User Publik
        $publicUser = User::firstOrCreate(
            ['email' => 'public@example.com'],
            [
                'name' => 'Sari Publik',
                'password' => bcrypt('password123'),
                'account_type' => 'public',
                'external_id' => null,
                'quota_bytes' => 5368709120, // 5 GB
                'used_bytes' => 0,
            ]
        );
        $publicUser->assignRole('user');
    }
}
