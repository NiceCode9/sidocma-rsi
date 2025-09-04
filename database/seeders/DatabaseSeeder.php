<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RolePermissionSeeder::class,
            UnitSeeder::class,
            UserSeeder::class,
        ]);

        // Folder::factory(100)
        //     ->withChildren(3, 2)
        //     ->create();
    }
}
