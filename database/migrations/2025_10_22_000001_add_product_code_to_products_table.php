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
        // Add product_code column as nullable initially
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code')->nullable()->after('name');
        });

        // Backfill existing products with auto-generated product codes
        $products = DB::table('products')->whereNull('product_code')->get();
        foreach ($products as $product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update(['product_code' => 'product-' . $product->id]);
        }

        // Make product_code non-nullable and unique
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('product_code');
        });
    }
};

