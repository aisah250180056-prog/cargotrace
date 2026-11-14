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
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('item_name'); // Contoh: Industrial Electronics, Coffee Beans
            $table->integer('quantity')->default(1);
            $table->decimal('weight_kg', 10, 2); // Contoh: 4200.50 kg
            $table->decimal('volume_cbm', 10, 2)->nullable(); // Volume meter kubik (CBM)
            $table->string('hs_code')->nullable(); // Harmonized System Code (standar bea cukai)
            $table->boolean('is_dangerous_goods')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};