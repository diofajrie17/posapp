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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'pcs', 'box', 'kg', 'liter'
            $table->string('symbol')->nullable(); // e.g., 'pcs', 'box', 'kg', 'L'
            $table->unsignedBigInteger('parent_unit_id')->nullable(); // for unit conversion
            $table->decimal('conversion_factor', 10, 4)->default(1); // e.g., 1 box = 12 pcs (conversion_factor = 12)
            $table->boolean('is_base_unit')->default(false); // true for base units like 'pcs'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('parent_unit_id')->references('id')->on('units')->onDelete('set null');
            $table->index(['is_active', 'is_base_unit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
