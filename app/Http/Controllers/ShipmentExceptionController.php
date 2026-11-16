<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentExceptionRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShipmentExceptionController extends Controller
{
    public function __construct(
        protected ExceptionService $exceptionService
    ) {}

    /**
     * Mengambil daftar exception pada kargo (Slide 12: GET /api/shipments/{id}/exceptions)
     */
    public function index(Request $request, Shipment $shipment)
    {
        Gate::authorize('view', $shipment);

        $exceptions = $shipment->exceptions()
                               ->with('logger:id,name,role')
                               ->latest()
                               ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'shipment_number'  => $shipment->shipment_number,
                'total_exceptions' => $exceptions->count(),
                'exceptions'       => $exceptions,
            ]);
        }

        return view('exceptions.index', compact('shipment', 'exceptions'));
    }

    /**
     * Melaporkan kendala baru (Slide 12: POST /api/shipments/{id}/exceptions)
     */
    public function store(StoreShipmentExceptionRequest $request, Shipment $shipment)
    {
        Gate::authorize('update', $shipment);

        $exception = $this->exceptionService->logException(
            $shipment,
            $request->validated(),
            $request->user()->id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message'   => 'Exception berhasil dicatat dan ETA kargo diperbarui otomatis!',
                'exception' => $exception,
                'updated_shipment' => [
                    'estimated_arrival' => $shipment->fresh()->estimated_arrival,
                    'is_delayed'        => $shipment->fresh()->is_delayed,
                    'delay_hours'       => $shipment->fresh()->delay_hours,
                    'delay_severity'    => $shipment->fresh()->delay_severity,
                ],
            ], 201);
        }

        return redirect()->back()->with('success', "Kendala {$exception->exception_type} berhasil dilaporkan!");
    }

    /**
     * Memperbarui status penanganan / Menyelesaikan kendala (Slide 12: PATCH /api/exceptions/{id})
     */
    public function update(Request $request, ShipmentException $exception)
    {
        // Hanya Admin & Operator yang boleh mengubah status investigasi
        $user = $request->user();
        if (! $user || (! $user->isAdmin() && ! $user->isOperator())) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status'           => ['required', 'in:OPEN,INVESTIGATING,RESOLVED'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $updatedException = $this->exceptionService->updateExceptionStatus(
            $exception,
            $validated,
            $user->id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message'   => "Status kendala berhasil diperbarui menjadi {$updatedException->status}!",
                'exception' => $updatedException,
            ]);
        }

        return redirect()->back()->with('success', "Status penanganan kendala berhasil diubah ke {$updatedException->status}!");
    }
}