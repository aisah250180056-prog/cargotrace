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
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Contoh: MAEU, MSC, CMA
            $table->string('name'); // Contoh: Maersk Line, Mediterranean Shipping Co
            $table->enum('transport_mode', ['SEA', 'AIR', 'LAND'])->default('SEA');
            $table->string('contact_email')->nullable();
            $table->string('tracking_url_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};