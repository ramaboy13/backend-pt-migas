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
        // Create Super Admin User
        $superAdmin = User::create([
            'name' => 'CEO Super Admin',
            'email' => 'ceo@ptmigas.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create Admin User
        $admin = User::create([
            'name' => 'Staff Admin',
            'email' => 'admin@ptmigas.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Assign roles
        $superAdmin->assignRole('super_admin');
        $admin->assignRole('admin');

        $this->command->info('Users created:');
        $this->command->info('- Super Admin: ceo@ptmigas.com / password123');
        $this->command->info('- Admin: admin@ptmigas.com / password123');
    }
}
