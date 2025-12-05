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
        Schema::table('members', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
            $table->enum('gender', ['male', 'female'])->nullable()->after('email');
            $table->foreignId('membership_package_id')->nullable()->after('gender')->constrained('membership_packages')->nullOnDelete();
            $table->text('notes')->nullable()->after('is_active');
            
            // Remove unused fields
            $table->dropColumn(['photo_face_id', 'rfid_wristband']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['membership_package_id']);
            $table->dropColumn(['email', 'gender', 'membership_package_id', 'notes']);
            
            // Restore removed fields
            $table->text('photo_face_id')->nullable();
            $table->string('rfid_wristband')->nullable();
        });
    }
};

