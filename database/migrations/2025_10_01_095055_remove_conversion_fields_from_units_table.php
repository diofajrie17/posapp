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
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['parent_unit_id']);
            $table->dropColumn(['parent_unit_id', 'conversion_factor', 'is_base_unit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_unit_id')->nullable();
            $table->decimal('conversion_factor', 10, 4)->default(1);
            $table->boolean('is_base_unit')->default(true);
            
            $table->foreign('parent_unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }
};
