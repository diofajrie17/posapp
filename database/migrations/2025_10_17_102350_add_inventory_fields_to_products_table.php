<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Separate pricing for base and derived units
            $table->decimal('base_unit_price', 12, 2)->nullable()->after('price');
            $table->decimal('derived_unit_price', 12, 2)->nullable()->after('base_unit_price');
            
            // Stock management fields
            $table->decimal('min_stock', 10, 2)->default(0)->after('stock');
            $table->decimal('max_stock', 10, 2)->nullable()->after('min_stock');
            
            // Track if product is active
            $table->boolean('is_active')->default(true)->after('max_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'base_unit_price',
                'derived_unit_price',
                'min_stock',
                'max_stock',
                'is_active'
            ]);
        });
    }
};
