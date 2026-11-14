<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentService
{
    /**
     * Definisi State Machine: Transisi status yang valid (Slide 06)
     */
    protected array $allowedTransitions = [
        'CREATED'    => ['BOOKED'],
        'BOOKED'     => ['PICKED_UP'],
        'PICKED_UP'  => ['IN_TRANSIT'],
        'IN_TRANSIT' => ['DELIVERED'],
        'DELIVERED'  => [], // Status Terminal: Terkunci (tidak boleh kembali)
    ];

    /**
     * Validasi transisi status di Backend (Slide 06)
     * Mencegah lompat status sembarangan (misal dari CREATED langsung ke DELIVERED)
     */
    public function canTransitionTo(Shipment $shipment, string $targetStatus): bool
    {
        $current = $shipment->status;

        // Jika status sama, tidak perlu transisi
        if ($current === $targetStatus) {
            return true;
        }

        return in_array($targetStatus, $this->allowedTransitions[$current] ?? []);
    }

    /**
     * Generate Nomor Resi / Shipment Number Otomatis Unik (Format: CT-2026-XXXXX)
     */
    public function generateShipmentNumber(): string
    {
        $year = date('Y');
        do {
            $number = 'CT-' . $year . '-' . strtoupper(Str::random(5));
        } while (Shipment::where('shipment_number', $number)->exists());

        return $number;
    }

    /**
     * Membuat Shipment Baru beserta barang dan initial tracking event (DB Transaction)
     */
    public function createShipment(array $shipmentData, array $itemsData, int $userId): Shipment
    {
        return DB::transaction(function () use ($shipmentData, $itemsData, $userId) {
            // 1. Generate nomor shipment jika belum ada
            if (empty($shipmentData['shipment_number'])) {
                $shipmentData['shipment_number'] = $this->generateShipmentNumber();
            }

            // 2. Set status awal default CREATED
            $shipmentData['status'] = 'CREATED';

            // 3. Simpan data shipment utama
            $shipment = Shipment::create($shipmentData);

            // 4. Simpan rincian barang kargo (items)
            foreach ($itemsData as $item) {
                $shipment->items()->create($item);
            }

            // 5. Catat initial tracking event (Slide 07)
            TrackingEvent::create([
                'shipment_id'   => $shipment->id,
                'created_by'    => $userId,
                'event_type'    => 'CREATED',
                'status_code'   => 'MANIFEST_CREATED',
                'location_name' => $shipment->origin->name ?? 'Origin Hub',
                'latitude'      => $shipment->origin->latitude ?? null,
                'longitude'     => $shipment->origin->longitude ?? null,
                'event_time'    => Carbon::now(),
                'description'   => 'Dokumen pengiriman dibuat dan terdaftar di sistem CargoTrace.',
            ]);

            // 6. Catat audit log (Slide 10)
            AuditLog::create([
                'user_id'     => $userId,
                'action'      => 'CREATE_SHIPMENT',
                'entity_type' => Shipment::class,
                'entity_id'   => $shipment->id,
                'new_values'  => $shipment->toArray(),
                'ip_address'  => request()->ip() ?? '127.0.0.1',
            ]);

            return $shipment;
        });
    }

    /**
     * Mengubah Status Pengiriman dengan Validasi State Machine (Slide 06)
     */
    public function updateStatus(Shipment $shipment, string $newStatus, int $userId, ?string $notes = null): Shipment
    {
        if (! $this->canTransitionTo($shipment, $newStatus)) {
            throw new Exception("Transisi status tidak valid: dari [{$shipment->status}] ke [{$newStatus}]. Status terminal DELIVERED tidak dapat diubah.");
        }

        return DB::transaction(function () use ($shipment, $newStatus, $userId, $notes) {
            $oldStatus = $shipment->status;
            
            $updateData = ['status' => $newStatus];
            if ($newStatus === 'DELIVERED') {
                $updateData['actual_arrival'] = Carbon::now();
            }

            $shipment->update($updateData);

            // Catat audit log perubahan status
            AuditLog::create([
                'user_id'     => $userId,
                'action'      => 'UPDATE_STATUS',
                'entity_type' => Shipment::class,
                'entity_id'   => $shipment->id,
                'old_values'  => ['status' => $oldStatus],
                'new_values'  => ['status' => $newStatus, 'notes' => $notes],
                'ip_address'  => request()->ip() ?? '127.0.0.1',
            ]);

            return $shipment;
        });
    }
}