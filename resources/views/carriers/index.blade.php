@extends('layouts.app')

@section('title', 'Daftar Maskapai (Carriers) - CargoTrace')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-white">Mitra Maskapai Logistik (Carriers)</h1>
            <p class="text-xs text-slate-400">Daftar operator pelayaran kontainer dan kargo udara internasional</p>
        </div>

        <!-- Filter Pencarian -->
        <form action="{{ route('carriers.index') }}" method="GET" class="flex gap-2 text-xs">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Carrier..." class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-white">
            <select name="mode" class="bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white">
                <option value="">Semua Mode</option>
                <option value="SEA" {{ request('mode') === 'SEA' ? 'selected' : '' }}>Laut (SEA)</option>
                <option value="AIR" {{ request('mode') === 'AIR' ? 'selected' : '' }}>Udara (AIR)</option>
                <option value="LAND" {{ request('mode') === 'LAND' ? 'selected' : '' }}>Darat (LAND)</option>
            </select>
            <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium">Cari</button>
        </form>
    </div>

    <!-- Carrier Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Kode SCAC</th>
                        <th class="px-4 py-3">Nama Maskapai / Perusahaan</th>
                        <th class="px-4 py-3">Mode Transportasi</th>
                        <th class="px-4 py-3">Email Operasional</th>
                        <th class="px-4 py-3">Total Kargo Aktif</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($carriers as $c)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3 font-mono font-bold text-blue-400">{{ $c->code }}</td>
                        <td class="px-4 py-3 font-medium text-white">{{ $c->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                {{ $c->transport_mode === 'SEA' ? 'bg-cyan-950 text-cyan-300 border border-cyan-800' : '' }}
                                {{ $c->transport_mode === 'AIR' ? 'bg-sky-950 text-sky-300 border border-sky-800' : '' }}
                                {{ $c->transport_mode === 'LAND' ? 'bg-amber-950 text-amber-300 border border-amber-800' : '' }}">
                                {{ $c->transport_mode }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ $c->contact_email ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $c->shipments_count }} shipment</td>
                        <td class="px-4 py-3">
                            <span class="text-emerald-400 font-semibold text-[11px]">Aktif ✓</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-6 text-slate-500">Tidak ada data carrier.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-800 bg-slate-950/60">
            {{ $carriers->links() }}
        </div>
    </div>
</div>
@endsection