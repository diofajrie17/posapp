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
        Schema::create('sales_transactions', function (Blueprint $table) {
    $table->id();
    $table->dateTime('date_time');
    $table->decimal('total_amount', 12, 2);
    $table->string('payment_type');
    $table->boolean('is_daily_guest')->default(false);
    $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_transactions');
    }
};
