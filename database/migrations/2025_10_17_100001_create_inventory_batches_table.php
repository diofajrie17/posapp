<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->onDelete('set null');
            $table->decimal('quantity_remaining', 10, 2);
            $table->decimal('unit_cost', 15, 2); // Cost per base unit
            $table->timestamp('purchased_at');
            $table->timestamps();
            
            $table->index(['product_id', 'purchased_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_batches');
    }
};

