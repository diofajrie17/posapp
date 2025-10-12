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
       Schema::create('members', function (Blueprint $table) {
    $table->id();
    $table->string('full_name');
    $table->string('phone');
    $table->text('photo_face_id')->nullable();
    $table->string('rfid_wristband')->nullable();
    $table->enum('membership_type', ['bulanan', 'tahunan', 'harian']);
    $table->date('membership_start')->nullable();
    $table->date('membership_end')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
