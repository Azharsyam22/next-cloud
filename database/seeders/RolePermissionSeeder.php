<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat daftar izin (permissions)
        $permissions = [
            // Izin File
            'files.view',
            'files.upload',
            'files.download',
            'files.delete',
            'files.share',

            // Izin Folder
            'folders.view',
            'folders.create',
            'folders.edit',
            'folders.delete',

            // Izin Administrasi Kampus
            'admin.dashboard',
            'admin.users.view',
            'admin.users.manage',
            'admin.quota.override',
            'admin.logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Buat Role 'user' (pengguna reguler, mahasiswa, umum)
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRole->syncPermissions([
            'files.view',
            'files.upload',
            'files.download',
            'files.delete',
            'files.share',
            'folders.view',
            'folders.create',
            'folders.edit',
            'folders.delete',
        ]);

        // 3. Buat Role 'admin-kampus' (admin pengelola unit / prodi)
        $adminKampusRole = Role::firstOrCreate(['name' => 'admin-kampus', 'guard_name' => 'web']);
        $adminKampusRole->syncPermissions([
            'files.view',
            'files.upload',
            'files.download',
            'files.delete',
            'files.share',
            'folders.view',
            'folders.create',
            'folders.edit',
            'folders.delete',
            'admin.dashboard',
            'admin.users.view',
            'admin.users.manage',
            'admin.quota.override',
            'admin.logs.view',
        ]);

        // 4. Buat Role 'super-admin' (akses penuh ke semua fitur)
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());
    }
}
