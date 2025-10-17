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
        Schema::create('batch_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_item_id')->constrained('sales_items')->cascadeOnDelete();
            $table->foreignId('inventory_batch_id')->constrained('inventory_batches')->restrictOnDelete();
            $table->decimal('quantity_allocated', 15, 4); // Quantity taken from this batch
            $table->decimal('cost_per_unit', 12, 2); // Cost at time of allocation (for COGS)
            $table->timestamps();
            
            // Indexes for queries
            $table->index('sales_item_id');
            $table->index('inventory_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_allocations');
    }
};
