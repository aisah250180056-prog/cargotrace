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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique(); // Contoh: CT-2026-0891
            
            // Relasi Foreign Key (Slide 11)
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('restrict');
            $table->foreignId('origin_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('destination_id')->constrained('locations')->onDelete('restrict');
            
            // Lifecycle Status (Slide 06)
            $table->enum('status', [
                'CREATED',
                'BOOKED',
                'PICKED_UP',
                'IN_TRANSIT',
                'DELIVERED'
            ])->default('CREATED');

            // Tanggal Rencana & Estimasi Aktual (Slide 08)
            $table->dateTime('expected_departure');
            $table->dateTime('expected_arrival');
            $table->dateTime('estimated_arrival')->nullable(); // Diisi dinamis oleh EtaService
            $table->dateTime('actual_arrival')->nullable();

            // Aturan Keterlambatan (Slide 08: Deterministic Delay Rule)
            $table->boolean('is_delayed')->default(false);
            $table->integer('delay_hours')->default(0);
            $table->enum('delay_severity', ['NONE', 'LOW', 'MEDIUM', 'HIGH'])->default('NONE');

            $table->text('notes')->nullable();
            $table->softDeletes(); // Slide 11: Soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};