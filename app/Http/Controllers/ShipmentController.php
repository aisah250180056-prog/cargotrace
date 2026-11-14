<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
{
    /**
     * Dependency Injection: Menghubungkan Service Layer ke Controller (Slide 15)
     */
    public function __construct(
        protected ShipmentService $shipmentService
    ) {}

    /**
     * Menampilkan daftar pengiriman (Slide 05, 10, 13)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Shipment::with(['customer', 'carrier', 'origin', 'destination']);

        // ATURAN HAK AKSES SERVER-SIDE (Slide 05 & 13)
        // Jika login sebagai Customer, HANYA TAMPILKAN kargo miliknya sendiri
        if ($user && $user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        // Filter Pencarian No Resi / Notes (Slide 10)
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter Status Lifecycle
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Carrier
        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        // Filter Keterlambatan (Delayed / On Time)
        if ($request->filled('delay_status')) {
            if ($request->delay_status === 'delayed') {
                $query->where('is_delayed', true);
            } elseif ($request->delay_status === 'on_time') {
                $query->where('is_delayed', false);
            }
        }

        // Server-Side Pagination (Slide 10)
        $shipments = $query->latest()->paginate(10)->withQueryString();

        // Dukungan REST API (Slide 12)
        if ($request->wantsJson()) {
            return response()->json($shipments);
        }

        return view('shipments.index', compact('shipments'));
    }

    /**
     * Form tambah shipment baru (Hanya Admin / Operator)
     */
    public function create()
    {
        Gate::authorize('create', Shipment::class);

        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::orderBy('name')->get();
        $customers = User::where('role', 'customer')->get();

        return view('shipments.create', compact('carriers', 'locations', 'customers'));
    }

    /**
     * Menyimpan data shipment & items menggunakan ShipmentService (Slide 15)
     */
    public function store(StoreShipmentRequest $request)
    {
        $shipmentData = $request->safe()->except('items');
        $itemsData    = $request->safe()->only('items')['items'];

        $shipment = $this->shipmentService->createShipment(
            $shipmentData,
            $itemsData,
            $request->user()->id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Shipment berhasil dibuat!',
                'shipment' => $shipment->load('items'),
            ], 201);
        }

        return redirect()->route('shipments.show', $shipment->id)
                         ->with('success', "Shipment {$shipment->shipment_number} berhasil dibuat!");
    }

    /**
     * Menampilkan detail pengiriman, rute peta, dan riwayat tracking (Slide 07)
     */
    public function show(Request $request, Shipment $shipment)
    {
        // Pemeriksaan Hak Akses: Customer dilarang membuka ID kargo orang lain (HTTP 403)
        Gate::authorize('view', $shipment);

        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'items',
            'trackingEvents.creator',
            'exceptions.logger',
        ]);

        if ($request->wantsJson()) {
            return response()->json($shipment);
        }

        return view('shipments.show', compact('shipment'));
    }

    /**
     * Memperbarui Status Pengiriman dengan Validasi State Machine (Slide 06)
     */
    public function updateStatus(Request $request, Shipment $shipment)
    {
        Gate::authorize('update', $shipment);

        $request->validate([
            'status' => ['required', 'in:CREATED,BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->shipmentService->updateStatus(
                $shipment,
                $request->status,
                $request->user()->id,
                $request->notes
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Status pengiriman berhasil diperbarui!',
                    'status'  => $shipment->status,
                ]);
            }

            return redirect()->back()->with('success', "Status berhasil diperbarui ke {$request->status}!");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()->withErrors(['status' => $e->getMessage()]);
        }
    }
}