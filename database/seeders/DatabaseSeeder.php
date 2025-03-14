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

        $user1 = User::factory()->create([
            'name' => 'Pepe',
            'username' => 'pepe123',
            'email' => 'pepe@gmail.com',
            'password' => Hash::make('Pestillo@1'),
            'img_profile' => 'https://res.cloudinary.com/dm7ohg3ym/image/upload/v1741976889/klc2umhv7metvok8rgqi.jpg'
        ]);

        $user2 = User::factory()->create([
            'name' => 'Antonio',
            'username' => 'antonio',
            'email' => 'antonio@gmail.com',
            'password' => Hash::make('Pestillo@1'),
            'img_profile' => "https://res.cloudinary.com/dm7ohg3ym/image/upload/v1741766828/mzoomosiy8ign4qh8rzh.png"
        ]);

        $userRole = Role::firstOrCreate(['name' => 'user', 'description' => 'Usuario normal']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'description' => 'Administrador']);

        $adminUser->roles()->attach($adminRole);
        $user1->roles()->attach($userRole);
        $user2->roles()->attach($userRole);
    }
}
