<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Update products table
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('stock', 10, 2)->change();
            if (!Schema::hasColumn('products', 'average_cost')) {
                $table->decimal('average_cost', 15, 2)->nullable()->after('cost_price');
            }
            if (!Schema::hasColumn('products', 'last_purchase_cost')) {
                $table->decimal('last_purchase_cost', 15, 2)->nullable()->after('average_cost');
            }
        });

        // Update sales_items table
        Schema::table('sales_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_items', 'unit_cogs')) {
                $table->decimal('unit_cogs', 15, 2)->nullable()->after('price_each');
            }
        });

        // Update sales_transactions table
        Schema::table('sales_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_transactions', 'cogs_amount')) {
                $table->decimal('cogs_amount', 15, 2)->nullable()->after('total_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->change();
            $table->dropColumn(['average_cost', 'last_purchase_cost']);
        });

        Schema::table('sales_items', function (Blueprint $table) {
            $table->dropColumn('unit_cogs');
        });

        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn('cogs_amount');
        });
    }
};

