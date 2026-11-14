<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TrackingService
{
    /**
     * Mengambil riwayat tracking kronologis terurut waktu (Slide 07)
     */
    public function getTimeline(Shipment $shipment): Collection
    {
        return $shipment->trackingEvents()
                        ->with('creator:id,name,role')
                        ->orderBy('event_time', 'asc')
                        ->get();
    }

    /**
     * Menambahkan Tracking Event Baru (Immutable History - Slide 07)
     */
    public function addEvent(Shipment $shipment, array $eventData, int $userId): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $eventData, $userId) {
            // Validasi: Status terminal DELIVERED tidak boleh ditambah event perjalanan baru
            if ($shipment->status === 'DELIVERED') {
                throw new Exception("Shipment telah berstatus DELIVERED. Riwayat perjalanan telah dikunci.");
            }

            // 1. Simpan Event Baru (Data historis lama tetap utuh, tidak pernah di-overwrite)
            $event = TrackingEvent::create([
                'shipment_id'   => $shipment->id,
                'created_by'    => $userId,
                'event_type'    => strtoupper($eventData['event_type']),
                'status_code'   => $eventData['status_code'] ?? null,
                'location_name' => $eventData['location_name'],
                'latitude'      => $eventData['latitude'] ?? null,
                'longitude'     => $eventData['longitude'] ?? null,
                'event_time'    => Carbon::parse($eventData['event_time']),
                'description'   => $eventData['description'] ?? null,
            ]);

            // 2. Sinkronisasi status pengiriman jika event_type mewakili status lifecycle
            $lifecycleStatuses = ['BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'];
            $normalizedEvent = strtoupper($eventData['event_type']);

            if (in_array($normalizedEvent, $lifecycleStatuses) && $shipment->status !== $normalizedEvent) {
                $shipment->update(['status' => $normalizedEvent]);
                
                if ($normalizedEvent === 'DELIVERED') {
                    $shipment->update(['actual_arrival' => $event->event_time]);
                }
            }

            // 3. Catat ke Audit Log (Slide 10)
            AuditLog::create([
                'user_id'     => $userId,
                'action'      => 'ADD_TRACKING_EVENT',
                'entity_type' => TrackingEvent::class,
                'entity_id'   => $event->id,
                'new_values'  => $event->toArray(),
                'ip_address'  => request()->ip() ?? '127.0.0.1',
            ]);

            return $event;
        });
    }
}