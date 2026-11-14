<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'item_name',
        'quantity',
        'weight_kg',
        'volume_cbm',
        'hs_code',
        'is_dangerous_goods',
    ];

    protected function casts(): array
    {
        return [
            'quantity'           => 'integer',
            'weight_kg'          => 'decimal:2',
            'volume_cbm'         => 'decimal:2',
            'is_dangerous_goods' => 'boolean',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}