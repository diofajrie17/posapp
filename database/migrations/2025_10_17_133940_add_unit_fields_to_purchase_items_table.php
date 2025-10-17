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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('unit_name')->nullable()->after('product_id'); // e.g., "Box"
            $table->decimal('unit_conversion', 10, 4)->default(1)->after('unit_name'); // e.g., 12.0000
            $table->decimal('quantity_in_base_unit', 10, 2)->nullable()->after('quantity'); // converted quantity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['unit_name', 'unit_conversion', 'quantity_in_base_unit']);
        });
    }
};
