<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_number',
        'customer_id',
        'carrier_id',
        'origin_id',
        'destination_id',
        'status',
        'expected_departure',
        'expected_arrival',
        'estimated_arrival',
        'actual_arrival',
        'is_delayed',
        'delay_hours',
        'delay_severity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expected_departure' => 'datetime',
            'expected_arrival'   => 'datetime',
            'estimated_arrival'  => 'datetime',
            'actual_arrival'     => 'datetime',
            'is_delayed'         => 'boolean',
            'delay_hours'        => 'integer',
        ];
    }

    // --- Relasi BelongsTo ---

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    // --- Relasi HasMany ---

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'asc');
    }

    /**
     * Membantu mengambil posisi/event terkini dengan cepat
     */
    public function latestTrackingEvent(): HasOne
    {
        return $this->hasOne(TrackingEvent::class)->latestOfMany('event_time');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class);
    }
}