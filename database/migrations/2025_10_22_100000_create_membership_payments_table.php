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
        Schema::create('membership_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('membership_package_id')->constrained()->onDelete('cascade');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->enum('type', ['registration', 'daily'])->default('registration');
            $table->string('payment_type'); // Cash, QR, Transfer
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_payments');
    }
};
