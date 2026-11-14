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
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict'); // Operator/Admin yang input
            
            // Tipe event milestone (Slide 07)
            $table->string('event_type'); // Contoh: PICKED_UP, DEPARTED_PORT, IN_TRANSIT, CUSTOMS_CLEARED, ARRIVED_PORT, OUT_FOR_DELIVERY, DELIVERED
            $table->string('status_code')->nullable(); // Kode status internal/carrier
            
            // Lokasi & Koordinat GPS untuk Leaflet.js
            $table->string('location_name'); // Contoh: Port of Tanjung Priok / Red Sea Approach
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->dateTime('event_time'); // Waktu aktual kejadian (bukan waktu input DB)
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};