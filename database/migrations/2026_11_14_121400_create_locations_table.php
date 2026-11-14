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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('unlocode', 10)->unique(); // Contoh: IDJKT, NLRTM, SGSIN
            $table->string('name'); // Port of Tanjung Priok
            $table->string('city');
            $table->string('country_code', 5); // ID, NL, SG
            $table->decimal('latitude', 10, 7); // Untuk Leaflet Map
            $table->decimal('longitude', 10, 7); // Untuk Leaflet Map
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};