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
    Schema::create('stock_movements', function (Blueprint $t) {
        $t->id();
        $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
        $t->enum('direction', ['IN','OUT']);         // masuk/keluar
        $t->integer('quantity');                     // +/-
        $t->string('source')->default('manual');     // pos|opname|purchase|return|manual
        $t->unsignedBigInteger('source_id')->nullable(); // id referensi (trx/adjustment)
        $t->text('note')->nullable();
        $t->timestamp('moved_at')->useCurrent();
        $t->timestamps();
        $t->index(['product_id','moved_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
