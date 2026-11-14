<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TrackingEventController extends Controller
{
    /**
     * Dependency Injection TrackingService (Slide 15)
     */
    public function __construct(
        protected TrackingService $trackingService
    ) {}

    /**
     * Mengambil riwayat tracking kronologis (Slide 07 & 12: GET /api/shipments/{id}/tracking)
     */
    public function index(Request $request, Shipment $shipment)
    {
        // Pemeriksaan Hak Akses: Customer hanya bisa melihat kargo miliknya (Slide 05)
        Gate::authorize('view', $shipment);

        $events = $this->trackingService->getTimeline($shipment);

        if ($request->wantsJson()) {
            return response()->json([
                'shipment_number'  => $shipment->shipment_number,
                'current_status'   => $shipment->status,
                'total_events'     => $events->count(),
                'timeline'         => $events,
            ]);
        }

        return view('tracking.index', compact('shipment', 'events'));
    }

    /**
     * Menambahkan milestone event pelacakan baru (Slide 07 & 12: POST /api/shipments/{id}/tracking)
     */
    public function store(StoreTrackingEventRequest $request, Shipment $shipment)
    {
        Gate::authorize('update', $shipment);

        try {
            $event = $this->trackingService->addEvent(
                $shipment,
                $request->validated(),
                $request->user()->id
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Tracking event berhasil ditambahkan ke riwayat pengiriman!',
                    'event'   => $event,
                ], 201);
            }

            return redirect()->back()->with('success', 'Milestone perjalanan kargo berhasil diperbarui!');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()->withErrors(['event' => $e->getMessage()]);
        }
    }
}