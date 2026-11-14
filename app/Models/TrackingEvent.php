<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'created_by',
        'event_type',
        'status_code',
        'location_name',
        'latitude',
        'longitude',
        'event_time',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'latitude'   => 'decimal:7',
            'longitude'  => 'decimal:7',
            'event_time' => 'datetime',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}