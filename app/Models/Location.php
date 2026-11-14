<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'unlocode',
        'name',
        'city',
        'country_code',
        'latitude',
        'longitude',
    ];

    /**
     * Relasi: 1 Lokasi bisa menjadi titik keberangkatan (origin) untuk banyak shipment
     */
    public function originShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_id');
    }

    /**
     * Relasi: 1 Lokasi bisa menjadi titik tujuan (destination) untuk banyak shipment
     */
    public function destinationShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_id');
    }
}