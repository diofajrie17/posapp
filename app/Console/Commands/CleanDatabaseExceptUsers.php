<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanDatabaseExceptUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean-except-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all tables except users, migrations, roles and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('This will delete all data except users, roles and permissions. Are you sure?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Cleaning database except users, migrations, roles and permissions...');

        // Tables to preserve for security and user access
        $except = [
            'users', 
            'migrations',
            'roles',
            'permissions',
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
        ];
        $database = DB::getDatabaseName();
        
        // Get all tables using raw SQL
        $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = ?", [$database]);

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            $tableName = $table->table_name ?? $table->TABLE_NAME;
            
            if (!in_array($tableName, $except)) {
                DB::table($tableName)->truncate();
                $this->line("✓ Truncated: {$tableName}");
            } else {
                $this->line("→ Skipped: {$tableName}");
            }
        }

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->info('Database cleaned successfully!');
        $this->info('Preserved: users, migrations, roles, permissions, and role assignments');
    }
}
