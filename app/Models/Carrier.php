<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'transport_mode',
        'contact_email',
        'tracking_url_template',
        'is_active',
    ];

    /**
     * Relasi: 1 Carrier memiliki banyak Shipment
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}