<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExceptionService
{
    public function __construct(
        protected EtaService $etaService
    ) {}

    /**
     * Melaporkan Exception baru dan otomatis meng-update ETA kargo (Slide 08)
     */
    public function logException(Shipment $shipment, array $data, int $userId): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $data, $userId) {
            $delayImpact = (int) ($data['delay_impact_hours'] ?? 0);

            // 1. Simpan catatan Exception
            $exception = ShipmentException::create([
                'shipment_id'        => $shipment->id,
                'logged_by'          => $userId,
                'exception_type'     => $data['exception_type'],
                'severity'           => $data['severity'] ?? $this->etaService->determineSeverity($delayImpact),
                'status'             => 'OPEN',
                'delay_impact_hours' => $delayImpact,
                'root_cause'         => $data['root_cause'] ?? null,
                'resolution_notes'   => $data['resolution_notes'] ?? null,
                'detected_at'        => Carbon::parse($data['detected_at'] ?? now()),
            ]);

            // 2. Jika ada dampak jam penundaan, mundurkan ETA shipment dan hitung ulang severity
            if ($delayImpact > 0) {
                $currentEta = $shipment->estimated_arrival ?? $shipment->expected_arrival;
                $newEta = $currentEta->copy()->addHours($delayImpact);

                $shipment->update(['estimated_arrival' => $newEta]);
                $this->etaService->recalculateDelay($shipment);
            }

            // 3. Buat Notifikasi otomatis untuk Customer pemilik kargo (Slide 10)
            Notification::create([
                'user_id'     => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'type'        => 'EXCEPTION_ALERT',
                'title'       => "Kendala Pengiriman: {$exception->exception_type} pada {$shipment->shipment_number}",
                'message'     => "Kargo Anda mengalami kendala [{$exception->exception_type}]. Estimasi penundaan waktu sekitar {$delayImpact} jam. Tim operasional sedang melakukan investigasi.",
                'is_read'     => false,
            ]);

            // 4. Catat Jejak Audit (Slide 10 & 16)
            AuditLog::create([
                'user_id'     => $userId,
                'action'      => 'LOG_EXCEPTION',
                'entity_type' => ShipmentException::class,
                'entity_id'   => $exception->id,
                'new_values'  => $exception->toArray(),
                'ip_address'  => request()->ip() ?? '127.0.0.1',
            ]);

            return $exception;
        });
    }

    /**
     * Memperbarui status penanganan Exception (INVESTIGATING / RESOLVED) (Slide 08 & 12)
     */
    public function updateExceptionStatus(ShipmentException $exception, array $data, int $userId): ShipmentException
    {
        return DB::transaction(function () use ($exception, $data, $userId) {
            $oldStatus = $exception->status;
            $newStatus = $data['status'];

            $updateData = [
                'status'           => $newStatus,
                'resolution_notes' => $data['resolution_notes'] ?? $exception->resolution_notes,
            ];

            if ($newStatus === 'RESOLVED') {
                $updateData['resolved_at'] = Carbon::now();
            }

            $exception->update($updateData);

            // Catat Audit Log
            AuditLog::create([
                'user_id'     => $userId,
                'action'      => 'UPDATE_EXCEPTION_STATUS',
                'entity_type' => ShipmentException::class,
                'entity_id'   => $exception->id,
                'old_values'  => ['status' => $oldStatus],
                'new_values'  => ['status' => $newStatus, 'resolution_notes' => $updateData['resolution_notes']],
                'ip_address'  => request()->ip() ?? '127.0.0.1',
            ]);

            return $exception;
        });
    }
}