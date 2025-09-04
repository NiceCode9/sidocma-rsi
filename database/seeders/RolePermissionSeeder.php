<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Buat permissions
        $permissions = [
            'manage_users',
            'manage_units',
            'manage_folders',
            'manage_documents',
            'upload_documents',
            'download_documents',
            'share_documents',
            'delete_documents',
            'view_all_units',
            'manage_permissions'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Buat roles
        $adminRole = Role::create(['name' => 'admin']);
        $direkturRole = Role::create(['name' => 'direktur']);
        $kepalaUnitRole = Role::create(['name' => 'kepala unit']);
        $anggotaUnitRole = Role::create(['name' => 'anggota unit']);

        // Assign permissions ke roles
        $adminRole->givePermissionTo($permissions);

        $direkturRole->givePermissionTo([
            'manage_folders',
            'manage_documents',
            'upload_documents',
            'download_documents',
            'share_documents',
            'view_all_units'
        ]);

        $kepalaUnitRole->givePermissionTo([
            'manage_folders',
            'manage_documents',
            'upload_documents',
            'download_documents',
            'share_documents',
            'delete_documents',
        ]);

        $anggotaUnitRole->givePermissionTo([
            'upload_documents',
            'download_documents',
            'share_documents',
            'delete_documents',
        ]);
    }
}
