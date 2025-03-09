<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $adminUser = User::factory()->create([
            'name' => 'admin',
            'username' => 'admin',
            'email' => 'belerofonte@gmail.com',
            'password' => Hash::make('@M4nd4r1n4'),
        ]);

        Role::factory()->create(['name' => 'user', 'description' => 'Usuario normal']);
        $adminRole = Role::factory()->create(['name' => 'admin', 'description' => 'Administrador']);

        $adminUser->roles()->attach($adminRole);
    }
}
