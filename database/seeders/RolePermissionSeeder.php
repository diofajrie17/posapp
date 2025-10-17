<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // daftar permission (silakan tambah sesuai kebutuhan kamu)
        $permissions = [
            // Members
            'members.view', 'members.create', 'members.update', 'members.delete',

            // Products
            'products.view', 'products.create', 'products.update', 'products.delete', 'products.stockopname',

            // Transactions (POS)
            'transactions.view', 'transactions.create', 'transactions.reprint', 'transactions.delete', // delete biasanya HANYA admin

            // Reports
            'reports.view',

            // Expenses
            'expenses.view', 'expenses.create', 'expenses.update', 'expenses.delete',

            // Notifications
            'notifications.view', 'notifications.send',

            // Settings
            'settings.manage',
        ];

        // Create all permissions first
        foreach ($permissions as $p) {
            Permission::create(['name' => $p, 'guard_name' => 'web']);
        }

        // Roles
        $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $cashier = Role::create(['name' => 'Kasir', 'guard_name' => 'web']);

        // Admin: semua permission
        $admin->givePermissionTo(Permission::all());

        // Kasir: subset yang aman
        $cashier->syncPermissions([
            'members.view', 'members.create', 'members.update',
            'products.view',
            'transactions.view', 'transactions.create', 'transactions.reprint',
            'reports.view',
            'expenses.view', 'expenses.create',
            'notifications.view', 'notifications.send',
        ]);
    }
}
