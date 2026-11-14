<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentException extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'logged_by',
        'exception_type',
        'severity',
        'status',
        'delay_impact_hours',
        'root_cause',
        'resolution_notes',
        'detected_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'delay_impact_hours' => 'integer',
            'detected_at'        => 'datetime',
            'resolved_at'        => 'datetime',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}