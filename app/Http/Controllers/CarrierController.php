<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    /**
     * Menampilkan daftar maskapai/carrier (Slide 09 & 12)
     */
    public function index(Request $request)
    {
        $query = Carrier::query();

        // Filter pencarian
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('code', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('mode')) {
            $query->where('transport_mode', $request->mode);
        }

        $carriers = $query->withCount('shipments')->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($carriers);
        }

        return view('carriers.index', compact('carriers'));
    }

    /**
     * Menyimpan carrier baru (Hanya Admin - Slide 05)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                  => ['required', 'string', 'max:10', 'unique:carriers,code'],
            'name'                  => ['required', 'string', 'max:255'],
            'transport_mode'        => ['required', 'in:SEA,AIR,LAND'],
            'contact_email'         => ['nullable', 'email'],
            'tracking_url_template' => ['nullable', 'url'],
            'is_active'             => ['boolean'],
        ]);

        $carrier = Carrier::create($validated);

        if ($request->wantsJson()) {
            return response()->json($carrier, 201);
        }

        return redirect()->back()->with('success', 'Carrier berhasil ditambahkan!');
    }
}