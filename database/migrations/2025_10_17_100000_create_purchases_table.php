<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number', 50)->unique();
            $table->string('supplier_name');
            $table->string('supplier_phone', 50)->nullable();
            $table->text('supplier_address')->nullable();
            $table->date('purchase_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index('purchase_date');
            $table->index('supplier_name');
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('quantity', 10, 2); // Quantity in purchase unit
            $table->decimal('unit_cost', 15, 2); // Cost per purchase unit
            $table->decimal('base_quantity', 10, 2); // Converted to base units
            $table->decimal('base_unit_cost', 15, 2); // Cost per base unit
            $table->decimal('subtotal', 15, 2); // quantity * unit_cost
            $table->timestamps();
            
            $table->index('product_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};

