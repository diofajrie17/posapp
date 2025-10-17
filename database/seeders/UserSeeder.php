<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }
    }
}

