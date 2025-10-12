<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

public function run(): void
{
    $this->call(RolePermissionSeeder::class);

    // opsional: buat user admin & kasir contoh
    $admin = \App\Models\User::firstOrCreate(
        ['email' => 'admin@gym.local'],
        ['name' => 'Admin', 'password' => bcrypt('Admin1')]
    );
    $admin->assignRole('Admin');

    $cashier = \App\Models\User::firstOrCreate(
        ['email' => 'kasir@gym.local'],
        ['name' => 'Kasir', 'password' => bcrypt('Kasir1')]
    );
    $cashier->assignRole('Kasir');
}

}
