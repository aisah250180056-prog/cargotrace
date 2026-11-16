@extends('layouts.app')

@section('title', 'Dashboard Pengiriman Kargo - CargoTrace')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards (Slide 10) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Shipments -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-medium uppercase tracking-wider">Total Shipments</span>
                <span class="text-blue-400 text-lg">📦</span>
            </div>
            <div class="text-2xl font-bold text-white">{{ $shipments->total() }}</div>
            <p class="text-[11px] text-slate-500 mt-1">Total kargo tercatat di sistem</p>
        </div>

        <!-- In Transit -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-medium uppercase tracking-wider text-cyan-300">In Transit</span>
                <span class="text-cyan-400 text-lg">🚢</span>
            </div>
            <div class="text-2xl font-bold text-cyan-300">
                {{ \App\Models\Shipment::where('status', 'IN_TRANSIT')->count() }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Sedang dalam perjalanan pelayaran/penerbangan</p>
        </div>

        <!-- Delayed Shipments (Slide 08) -->
        <div class="bg-slate-900 border border-amber-900/40 rounded-xl p-4 shadow-sm bg-amber-950/10">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-medium uppercase tracking-wider text-amber-300">Delayed</span>
                <span class="text-amber-400 text-lg">⚠️</span>
            </div>
            <div class="text-2xl font-bold text-amber-400">
                {{ \App\Models\Shipment::where('is_delayed', true)->count() }}
            </div>
            <p class="text-[11px] text-amber-300/70 mt-1">ETA melebihi batas waktu (Expected Arrival)</p>
        </div>

        <!-- Completed / Delivered -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-medium uppercase tracking-wider text-emerald-300">Delivered</span>
                <span class="text-emerald-400 text-lg">✓</span>
            </div>
            <div class="text-2xl font-bold text-emerald-400">
                {{ \App\Models\Shipment::where('status', 'DELIVERED')->count() }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Pengiriman sukses diterima (Terminal State)</p>
        </div>
    </div>

    <!-- Shipment Table Container with Search & Filter Header -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        
        <!-- Controls Header -->
        <div class="p-4 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-white">Daftar Pengiriman Kargo (Live Database)</h2>
                <p class="text-xs text-slate-400">Menampilkan kargo aktif sesuai izin role Anda ({{ auth()->user()->role }})</p>
            </div>

            <!-- Filter & Search Form (Slide 10) -->
            <form action="{{ route('shipments.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <!-- Search -->
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari No. Resi..." class="bg-slate-800 border border-slate-700 text-xs rounded-lg pl-3 pr-3 py-1.5 text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500 w-44">
                </div>

                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-xs rounded-lg px-2.5 py-1.5 text-slate-200 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="CREATED" {{ request('status') === 'CREATED' ? 'selected' : '' }}>CREATED</option>
                    <option value="BOOKED" {{ request('status') === 'BOOKED' ? 'selected' : '' }}>BOOKED</option>
                    <option value="PICKED_UP" {{ request('status') === 'PICKED_UP' ? 'selected' : '' }}>PICKED_UP</option>
                    <option value="IN_TRANSIT" {{ request('status') === 'IN_TRANSIT' ? 'selected' : '' }}>IN_TRANSIT</option>
                    <option value="DELIVERED" {{ request('status') === 'DELIVERED' ? 'selected' : '' }}>DELIVERED</option>
                </select>

                <!-- Delay Condition Filter -->
                <select name="delay_status" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-xs rounded-lg px-2.5 py-1.5 text-slate-200 focus:outline-none">
                    <option value="">Semua Kondisi</option>
                    <option value="on_time" {{ request('delay_status') === 'on_time' ? 'selected' : '' }}>Tepat Waktu (On Time)</option>
                    <option value="delayed" {{ request('delay_status') === 'delayed' ? 'selected' : '' }}>Mengalami Keterlambatan</option>
                </select>

                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-medium transition">
                    Cari
                </button>

                <!-- Tombol Buat Shipment Baru (Khusus Admin & Operator - Slide 05 & 13) -->
                @if (auth()->user()->isAdmin() || auth()->user()->isOperator())
                <a href="{{ route('shipments.create') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold flex items-center space-x-1 shadow-sm transition">
                    <span>+ Buat Shipment</span>
                </a>
                @endif
            </form>
        </div>

        <!-- Table Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[11px] border-b border-slate-800 font-semibold">
                    <tr>
                        <th class="px-4 py-3">Shipment No.</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Carrier / Route</th>
                        <th class="px-4 py-3">Expected vs ETA</th>
                        <th class="px-4 py-3">Status Lifecycle</th>
                        <th class="px-4 py-3">Delay Severity</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($shipments as $shipment)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-4 py-3.5 font-mono font-medium text-blue-400">
                            {{ $shipment->shipment_number }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-medium text-white">{{ $shipment->customer->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $shipment->items->count() }} item kargo tercatat</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="text-slate-200 font-medium">{{ $shipment->carrier->name }}</div>
                            <div class="text-[11px] text-slate-400">
                                {{ $shipment->origin->unlocode }} ({{ $shipment->origin->city }}) ➔ {{ $shipment->destination->unlocode }} ({{ $shipment->destination->city }})
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="text-slate-400">Exp: {{ $shipment->expected_arrival->format('d M, H:i') }}</div>
                            @if ($shipment->is_delayed)
                                <div class="text-amber-400 font-semibold">
                                    ETA: {{ $shipment->estimated_arrival->format('d M, H:i') }} (+{{ $shipment->delay_hours }}h)
                                </div>
                            @else
                                <div class="text-emerald-400 font-medium">On Schedule ✓</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                {{ $shipment->status === 'DELIVERED' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-blue-500/10 text-blue-400 border border-blue-500/30' }}">
                                {{ $shipment->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            @if ($shipment->is_delayed)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                    {{ $shipment->delay_severity === 'HIGH' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                    {{ $shipment->delay_severity }} (+{{ $shipment->delay_hours }} Jam)
                                </span>
                            @else
                                <span class="text-slate-500 text-[11px]">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-blue-400 border border-slate-700 rounded-lg text-xs font-medium transition inline-block">
                                Detail & Peta ➔
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-500 text-xs">
                            Tidak ditemukan data pengiriman yang sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Server-Side Pagination Footer (Slide 10) -->
        <div class="px-4 py-3 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between text-xs text-slate-400">
            <div>
                Menampilkan data ke-<strong>{{ $shipments->firstItem() ?? 0 }}</strong> sampai <strong>{{ $shipments->lastItem() ?? 0 }}</strong> dari total <strong>{{ $shipments->total() }}</strong> shipment
            </div>
            <div>
                {{ $shipments->links() }}
            </div>
        </div>

    </div>

</div>
@endsection