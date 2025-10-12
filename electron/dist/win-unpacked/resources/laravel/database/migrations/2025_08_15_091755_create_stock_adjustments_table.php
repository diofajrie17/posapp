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
    Schema::create('stock_adjustments', function (Blueprint $t) {
        $t->id();
        $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
        $t->integer('qty_system');      // stok versi sistem sebelum opname
        $t->integer('qty_actual');      // stok hasil hitung fisik
        $t->integer('difference');      // qty_actual - qty_system (bisa negatif/positif)
        $t->string('reason')->nullable();  // alasan/shift
        $t->foreignId('user_id')->constrained('users'); // siapa yang melakukan
        $t->timestamp('adjusted_at')->useCurrent();
        $t->timestamps();
        $t->index(['product_id','adjusted_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
