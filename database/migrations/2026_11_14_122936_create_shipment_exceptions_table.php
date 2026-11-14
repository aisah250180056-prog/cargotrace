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
        Schema::create('shipment_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('logged_by')->constrained('users')->onDelete('restrict');
            
            // 6 Jenis Exception Wajib (Slide 08)
            $table->enum('exception_type', [
                'CUSTOMS_HOLD',
                'WEATHER_DELAY',
                'PORT_CONGESTION',
                'DAMAGED',
                'MISROUTED',
                'DOCUMENTATION_ISSUE'
            ]);

            // Severity Level (Slide 08)
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH'])->default('LOW');

            // Status Penanganan
            $table->enum('status', ['OPEN', 'INVESTIGATING', 'RESOLVED'])->default('OPEN');

            $table->integer('delay_impact_hours')->default(0); // Estimasi penambahan jam keterlambatan
            $table->text('root_cause')->nullable(); // Penyebab kendala
            $table->text('resolution_notes')->nullable(); // Catatan penanganan
            
            $table->dateTime('detected_at');
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_exceptions');
    }
};