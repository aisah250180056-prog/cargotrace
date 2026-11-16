@extends('layouts.app')

@section('title', 'Daftar Pelabuhan & Lokasi - CargoTrace')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-white">Jaringan Pelabuhan & Hub Logistik Dunia</h1>
            <p class="text-xs text-slate-400">Titik simpul koordinat geospasial pelayaran global (Leaflet.js Data Points)</p>
        </div>

        <!-- Filter Pencarian -->
        <form action="{{ route('locations.index') }}" method="GET" class="flex gap-2 text-xs">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Pelabuhan / UN/LOCODE..." class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-white w-52">
            <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium">Cari</button>
        </form>
    </div>

    <!-- Locations Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3">UN/LOCODE</th>
                        <th class="px-4 py-3">Nama Pelabuhan / Terminal</th>
                        <th class="px-4 py-3">Kota & Negara</th>
                        <th class="px-4 py-3">Koordinat GPS (Lat, Long)</th>
                        <th class="px-4 py-3 text-right">Peta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($locations as $loc)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3 font-mono font-bold text-blue-400">{{ $loc->unlocode }}</td>
                        <td class="px-4 py-3 font-medium text-white">{{ $loc->name }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ $loc->city }}, <span class="font-bold text-slate-400">{{ $loc->country_code }}</span></td>
                        <td class="px-4 py-3 font-mono text-slate-400">{{ $loc->latitude }}, {{ $loc->longitude }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="https://www.openstreetmap.org/?mlat={{ $loc->latitude }}&mlon={{ $loc->longitude }}#map=12/{{ $loc->latitude }}/{{ $loc->longitude }}" target="_blank" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-sky-400 rounded border border-slate-700 text-[11px] transition">
                                Lihat Titik ↗
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-slate-500">Tidak ada data lokasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-800 bg-slate-950/60">
            {{ $locations->links() }}
        </div>
    </div>
</div>
@endsection