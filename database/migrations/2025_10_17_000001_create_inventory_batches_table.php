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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('batch_code')->unique(); // Unique identifier for each batch
            $table->decimal('quantity_in_base_unit', 15, 4)->default(0); // Total quantity purchased in base unit
            $table->decimal('quantity_remaining', 15, 4)->default(0); // Remaining quantity in base unit
            $table->decimal('cost_per_base_unit', 12, 2); // Purchase cost per base unit
            $table->timestamp('purchased_at'); // For FIFO ordering
            $table->string('supplier')->nullable(); // Supplier name (optional)
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();
            
            // Index for FIFO queries
            $table->index(['product_id', 'purchased_at']);
            $table->index(['product_id', 'quantity_remaining']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
