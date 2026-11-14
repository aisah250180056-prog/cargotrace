<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Menampilkan daftar pelabuhan/bandara dan koordinat Leaflet Map (Slide 09 & 12)
     */
    public function index(Request $request)
    {
        $query = Location::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('unlocode', 'like', '%' . $request->q . '%')
                  ->orWhere('city', 'like', '%' . $request->q . '%');
        }

        $locations = $query->orderBy('name')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($locations);
        }

        return view('locations.index', compact('locations'));
    }

    /**
     * Menyimpan lokasi baru dengan koordinat GPS (Hanya Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unlocode'     => ['required', 'string', 'max:10', 'unique:locations,unlocode'],
            'name'         => ['required', 'string', 'max:255'],
            'city'         => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'max:5'],
            'latitude'     => ['required', 'numeric', 'between:-90,90'],
            'longitude'    => ['required', 'numeric', 'between:-180,180'],
        ]);

        $location = Location::create($validated);

        if ($request->wantsJson()) {
            return response()->json($location, 201);
        }

        return redirect()->back()->with('success', 'Lokasi pelabuhan berhasil ditambahkan!');
    }
}