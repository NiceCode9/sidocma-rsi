<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        $direkturRole = \Spatie\Permission\Models\Role::where('name', 'direktur')->first();
        $kepalaUnitRole = \Spatie\Permission\Models\Role::where('name', 'kepala unit')->first();
        $anggotaUnitRole = \Spatie\Permission\Models\Role::where('name', 'anggota unit')->first();

        $itUnit = \App\Models\Unit::where('code', 'IT')->first();
        $hrUnit = \App\Models\Unit::where('code', 'HR')->first();
        $finUnit = \App\Models\Unit::where('code', 'FIN')->first();
        $mktUnit = \App\Models\Unit::where('code', 'MKT')->first();

        $users = [
            // Admin
            [
                'username' => 'superadmin',
                'name' => 'Super Admin',
                'email' => 'admin@company.com',
                'password' => Hash::make('password'),
                'unit_id' => null,
                'is_active' => true
            ],
            // Direktur
            [
                'username' => 'direkturutama',
                'name' => 'Direktur Utama',
                'email' => 'direktur@company.com',
                'password' => Hash::make('password'),
                'unit_id' => null,
                'is_active' => true
            ],
            // Kepala Unit
            [
                'username' => 'kepalaunitit',
                'name' => 'Kepala Unit IT',
                'email' => 'kepala.it@company.com',
                'password' => Hash::make('password'),
                'unit_id' => $itUnit->id,
                'is_active' => true
            ],
            [
                'username' => 'kepalaunithr',
                'name' => 'Kepala Unit HR',
                'email' => 'kepala.hr@company.com',
                'password' => Hash::make('password'),
                'unit_id' => $hrUnit->id,
                'is_active' => true
            ],
            // Anggota Unit
            [
                'username' => 'staffit1',
                'name' => 'Staff IT 1',
                'email' => 'staff1.it@company.com',
                'password' => Hash::make('password'),
                'unit_id' => $itUnit->id,
                'is_active' => true
            ],
            [
                'username' => 'staffit2',
                'name' => 'Staff IT 2',
                'email' => 'staff2.it@company.com',
                'password' => Hash::make('password'),
                'unit_id' => $itUnit->id,
                'is_active' => true
            ],
            [
                'username' => 'staffhr1',
                'name' => 'Staff HR 1',
                'email' => 'staff1.hr@company.com',
                'password' => Hash::make('password'),
                'unit_id' => $hrUnit->id,
                'is_active' => true
            ]
        ];

        foreach ($users as $index => $userData) {
            $user = User::create([
                'username' => $userData['username'],
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'unit_id' => $userData['unit_id'],
                'is_active' => $userData['is_active'],
            ]);

            // Assign role berdasarkan index
            if ($index === 0) {
                $user->assignRole($adminRole);
            } else if ($index === 1) {
                $user->assignRole($direkturRole);
            } else if ($index === 2 || $index === 3) {
                $user->assignRole($kepalaUnitRole);
            } else {
                $user->assignRole($anggotaUnitRole);
            }
        }
    }
}
