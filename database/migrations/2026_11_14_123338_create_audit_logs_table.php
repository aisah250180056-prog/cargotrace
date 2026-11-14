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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Aktor (Admin/Operator)
            
            $table->string('action'); // Contoh: CREATE_SHIPMENT, UPDATE_STATUS, LOG_EXCEPTION
            $table->string('entity_type'); // Contoh: App\Models\Shipment
            $table->unsignedBigInteger('entity_id');
            
            // Menyimpan snapshot data sebelum dan sesudah perubahan (JSON format)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};