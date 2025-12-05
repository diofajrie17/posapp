<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'renewal'
        // MySQL/MariaDB requires ALTER TABLE with MODIFY COLUMN
        // Check if column exists and modify it
        if (Schema::hasColumn('membership_payments', 'type')) {
            DB::statement("ALTER TABLE membership_payments MODIFY COLUMN type ENUM('registration', 'daily', 'renewal') DEFAULT 'registration'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE membership_payments MODIFY COLUMN type ENUM('registration', 'daily') DEFAULT 'registration'");
    }
};

