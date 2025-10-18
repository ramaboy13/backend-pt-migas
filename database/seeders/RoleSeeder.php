<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing roles
        Role::where('name', 'superadmin')->delete();
        Role::where('name', 'admin')->delete();

        // Create roles
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'admin']);
        
        $this->command->info('Roles super_admin dan admin berhasil dibuat!');
    }
}