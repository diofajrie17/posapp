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
    Schema::create('notifications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
        $table->string('channel')->default('whatsapp'); // whatsapp / sms / email
        $table->text('message');
        $table->date('send_date');       // tanggal yang dijadwalkan (H-5)
        $table->dateTime('sent_at')->nullable(); // kapan benar2 terkirim
        $table->string('status')->default('pending'); // pending/sent/failed
        $table->string('meta')->nullable(); // optional JSON kecil
        $table->timestamps();

        $table->index(['send_date','status']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
