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
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('daily_plan_id')->nullable()->after('member_id')->constrained('membership_packages')->onDelete('set null');
            $table->decimal('payment_amount', 15, 2)->nullable()->after('daily_plan_id');
            $table->string('payment_type')->nullable()->after('payment_amount'); // Cash, QR, Transfer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['daily_plan_id']);
            $table->dropColumn(['daily_plan_id', 'payment_amount', 'payment_type']);
        });
    }
};
