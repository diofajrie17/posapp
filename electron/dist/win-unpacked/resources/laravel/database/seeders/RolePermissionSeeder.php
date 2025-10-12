<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($permissions as $p) {
            Permission::findOrCreate($p, 'web');
        }

        // Roles
        $admin = Role::findOrCreate('Admin', 'web');
        $cashier = Role::findOrCreate('Kasir', 'web');

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
