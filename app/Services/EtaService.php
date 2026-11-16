<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class EtaService
{
    /**
     * Ambang batas keterlambatan (Slide 08 & 23)
     */
    const THRESHOLD_LOW_MAX    = 6;  // 0 - 6 jam = LOW
    const THRESHOLD_MEDIUM_MAX = 24; // >6 - 24 jam = MEDIUM (di atas 24 jam = HIGH)

    /**
     * Menghitung status delay dan severity secara deterministik (Slide 08)
     */
    public function recalculateDelay(Shipment $shipment): Shipment
    {
        $expectedArrival = $shipment->expected_arrival;
        $estimatedArrival = $shipment->estimated_arrival ?? $expectedArrival;

        // Jika ETA melewati Expected Arrival -> Kargo dinyatakan DELAYED
        if ($estimatedArrival->greaterThan($expectedArrival)) {
            $delayHours = (int) $expectedArrival->diffInHours($estimatedArrival);
            $severity = $this->determineSeverity($delayHours);

            $shipment->update([
                'is_delayed'     => true,
                'delay_hours'    => $delayHours,
                'delay_severity' => $severity,
            ]);
        } else {
            // Tepat Waktu (On Schedule)
            $shipment->update([
                'is_delayed'     => false,
                'delay_hours'    => 0,
                'delay_severity' => 'NONE',
            ]);
        }

        return $shipment;
    }

    /**
     * Formula Klasifikasi Keparahan Delay (Slide 08)
     */
    public function determineSeverity(int $delayHours): string
    {
        if ($delayHours <= 0) {
            return 'NONE';
        }

        if ($delayHours <= self::THRESHOLD_LOW_MAX) {
            return 'LOW';
        }

        if ($delayHours <= self::THRESHOLD_MEDIUM_MAX) {
            return 'MEDIUM';
        }

        return 'HIGH';
    }
}