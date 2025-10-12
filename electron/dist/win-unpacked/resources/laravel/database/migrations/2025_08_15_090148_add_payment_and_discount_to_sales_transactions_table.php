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
        Schema::table('sales_transactions', function (Blueprint $table) {
            // jika total_amount belum ada, tambahkan
            if (!Schema::hasColumn('sales_transactions', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0);
            }

            // kolom baru
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->enum('discount_type', ['fixed', 'percent'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->nullable();
            $table->decimal('change_amount', 12, 2)->nullable();
            $table->string('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_amount',
                'discount_type',
                'discount_value',
                'discount_amount',
                'paid_amount',
                'change_amount',
                'notes',
            ]);

            // Jika ingin ikut menghapus total_amount saat rollback, buka baris ini:
            // $table->dropColumn('total_amount');
        });
    }
};
