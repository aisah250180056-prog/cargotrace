@extends('layouts.app')

@section('title', "Pelacakan Kargo {$shipment->shipment_number} - CargoTrace")

@section('content')
<div class="space-y-6">

    <!-- Top Shipment Header Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-800 pb-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold font-mono text-white">{{ $shipment->shipment_number }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                        {{ $shipment->status }}
                    </span>
                    @if ($shipment->is_delayed)
                        <span class="px-2.5 py-0.5 rounded text-xs font-medium bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            ⚠️ Delayed: +{{ $shipment->delay_hours }} Jam ({{ $shipment->delay_severity }})
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            ✓ On Schedule
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Customer: <strong class="text-slate-200">{{ $shipment->customer->name }}</strong> | 
                    Carrier: <strong class="text-slate-200">{{ $shipment->carrier->name }}</strong> ({{ $shipment->carrier->transport_mode }})
                </p>
            </div>

            <!-- Quick Metrics & External Weather API Card (Slide 09) -->
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <div class="bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700/60">
                    <span class="text-slate-400 block text-[10px]">Expected Arrival:</span>
                    <span class="font-medium text-slate-200">{{ $shipment->expected_arrival->format('d M Y, H:i') }} UTC</span>
                </div>

                <div class="bg-amber-950/40 px-3 py-1.5 rounded-lg border border-amber-700/60">
                    <span class="text-amber-300/80 block text-[10px]">Calculated ETA:</span>
                    <span class="font-bold text-amber-300">
                        {{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y, H:i') : $shipment->expected_arrival->format('d M Y, H:i') }} UTC
                    </span>
                </div>

                <!-- Weather Card dari Open-Meteo API -->
                @if ($weather)
                <div class="bg-sky-950/40 px-3 py-1.5 rounded-lg border border-sky-700/60 flex items-center space-x-2">
                    <span class="text-lg">🌤️</span>
                    <div>
                        <span class="text-sky-300/80 block text-[10px]">Live Weather (Posisi Kapal):</span>
                        <span class="font-semibold text-sky-300">{{ $weather['temperature'] }}°C, Angin {{ $weather['wind_speed'] }} km/h ({{ $weather['condition'] }})</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Shipment Lifecycle Stepper (Slide 06) -->
        <div>
            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Lifecycle Progression (State Machine):</h4>
            @php
                $steps = ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'];
                $currentIndex = array_search($shipment->status, $steps);
            @endphp
            <div class="grid grid-cols-5 gap-2">
                @foreach ($steps as $index => $step)
                    @php
                        $isPassed = $index < $currentIndex;
                        $isCurrent = $index === $currentIndex;
                    @endphp
                    <div class="p-2.5 rounded-xl text-center border transition
                        {{ $isCurrent ? 'bg-blue-950/60 border-blue-500 shadow-md shadow-blue-500/20' : '' }}
                        {{ $isPassed ? 'bg-slate-800/80 border-emerald-500/40' : '' }}
                        {{ !$isPassed && !$isCurrent ? 'bg-slate-900 border-slate-800 opacity-50' : '' }}">
                        <div class="text-[11px] font-bold 
                            {{ $isCurrent ? 'text-blue-300' : '' }}
                            {{ $isPassed ? 'text-emerald-300' : '' }}
                            {{ !$isPassed && !$isCurrent ? 'text-slate-500' : '' }}">
                            {{ $step }}
                        </div>
                        <div class="text-[9px] text-slate-400 mt-0.5">
                            @if ($isPassed) Selesai ✓ @elseif ($isCurrent) Posisi Saat Ini @else Belum Tercapai @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Main Content: 2-Columns (Left: Leaflet Map | Right: Timeline & Actions) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Interactive Map (Slide 09) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm flex flex-col h-[560px]">
                <div class="px-4 py-3 bg-slate-950 border-b border-slate-800 flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span class="font-semibold text-white">Visualisasi Rute Geografis (Leaflet.js + OpenStreetMap)</span>
                    </div>
                    <span class="text-slate-400 text-[11px]">
                        {{ $shipment->origin->name }} ➔ {{ $shipment->destination->name }}
                    </span>
                </div>

                <!-- Leaflet Map Container -->
                <div id="map" class="flex-1 w-full bg-slate-950 z-10"></div>
            </div>

            <!-- Cargo Manifest Items Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">Manifest Barang Kargo (Items)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 text-[11px]">
                            <tr>
                                <th class="py-2 px-3">Nama Barang</th>
                                <th class="py-2 px-3">Jumlah</th>
                                <th class="py-2 px-3">Berat (kg)</th>
                                <th class="py-2 px-3">Volume (CBM)</th>
                                <th class="py-2 px-3">Dangerous Goods</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach ($shipment->items as $item)
                            <tr>
                                <td class="py-2.5 px-3 font-medium text-white">{{ $item->item_name }}</td>
                                <td class="py-2.5 px-3">{{ $item->quantity }} unit</td>
                                <td class="py-2.5 px-3">{{ number_format($item->weight_kg, 2) }} kg</td>
                                <td class="py-2.5 px-3">{{ $item->volume_cbm ?? '-' }} CBM</td>
                                <td class="py-2.5 px-3">
                                    @if ($item->is_dangerous_goods)
                                        <span class="text-[10px] text-rose-400 font-bold">⚠️ Dangerous Goods</span>
                                    @else
                                        <span class="text-slate-500">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Timeline & Operational Actions -->
        <div class="lg:col-span-5 space-y-4">
            
            <!-- Operator Quick Action Panel (Khusus Admin & Operator - Slide 05 & 13) -->
            @if (auth()->user()->isAdmin() || auth()->user()->isOperator())
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-400 flex items-center justify-between">
                    <span>⚡ Tindakan Operasional (Operator)</span>
                    <span class="text-[10px] text-slate-500">Slide 05 & 13</span>
                </h3>

                <!-- Update Status Lifecycle Form -->
                <form action="{{ route('shipments.updateStatus', $shipment->id) }}" method="POST" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="bg-slate-800 border border-slate-700 text-xs rounded-lg px-2.5 py-1.5 text-white flex-1 focus:outline-none">
                        <option value="CREATED" {{ $shipment->status === 'CREATED' ? 'selected' : '' }}>Set: CREATED</option>
                        <option value="BOOKED" {{ $shipment->status === 'BOOKED' ? 'selected' : '' }}>Set: BOOKED</option>
                        <option value="PICKED_UP" {{ $shipment->status === 'PICKED_UP' ? 'selected' : '' }}>Set: PICKED_UP</option>
                        <option value="IN_TRANSIT" {{ $shipment->status === 'IN_TRANSIT' ? 'selected' : '' }}>Set: IN_TRANSIT</option>
                        <option value="DELIVERED" {{ $shipment->status === 'DELIVERED' ? 'selected' : '' }}>Set: DELIVERED (Terminal)</option>
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold transition">
                        Update Status
                    </button>
                </form>

                <!-- Tombol Buka Modal Form Event & Exception -->
                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-800 text-xs">
                    <button onclick="document.getElementById('modal-event').classList.remove('hidden')" class="py-1.5 px-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-center transition">
                        + Tambah Event
                    </button>
                    <button onclick="document.getElementById('modal-exception').classList.remove('hidden')" class="py-1.5 px-2 bg-rose-950/40 hover:bg-rose-900/40 text-rose-300 border border-rose-700/50 rounded-lg text-center transition">
                        ⚠️ Laporkan Kendala
                    </button>
                </div>
            </div>
            @endif

            <!-- Chronological Timeline (Slide 07) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col h-[450px]">
                <div class="border-b border-slate-800 pb-2 mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">Riwayat Perjalanan (Tracking Timeline)</h3>
                    <p class="text-[10px] text-slate-500">Data historis kronologis (Slide 07)</p>
                </div>

                <div class="flex-1 overflow-y-auto space-y-3 pr-1 text-xs">
                    
                    <!-- Tampilkan Exception Aktif Terlebih Dahulu Bila Ada (Slide 08) -->
                    @foreach ($shipment->exceptions as $exc)
                    <div class="relative pl-5 pb-2 border-l-2 border-rose-500">
                        <div class="absolute -left-1.5 top-0 w-3 h-3 rounded-full bg-rose-500 border-2 border-slate-900"></div>
                        <div class="bg-rose-950/30 border border-rose-600/40 rounded-xl p-3 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-rose-300 text-xs">⚠️ {{ $exc->exception_type }}</span>
                                <span class="text-[10px] text-slate-400">{{ $exc->detected_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="text-[11px] text-slate-300">{{ $exc->root_cause }}</p>
                            <div class="flex items-center justify-between text-[10px] pt-1">
                                <span class="text-amber-400 font-mono">Dampak: +{{ $exc->delay_impact_hours }} Jam ({{ $exc->severity }})</span>
                                <span class="px-1.5 py-0.2 rounded bg-rose-900/60 text-rose-300 font-bold uppercase">{{ $exc->status }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Tampilkan Tracking Events Kronologis -->
                    @forelse ($shipment->trackingEvents->reverse() as $event)
                    <div class="relative pl-5 pb-2 border-l-2 border-blue-500">
                        <div class="absolute -left-1.5 top-0 w-3 h-3 rounded-full bg-blue-500 border-2 border-slate-900"></div>
                        <div class="bg-slate-800/60 border border-slate-700/60 rounded-xl p-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-white text-xs">{{ $event->event_type }}</span>
                                <span class="text-[10px] text-slate-400">{{ $event->event_time->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="text-[11px] text-slate-300 mt-0.5">{{ $event->description }}</p>
                            <div class="mt-1 text-[10px] text-slate-400 flex items-center justify-between">
                                <span>📍 {{ $event->location_name }}</span>
                                <span class="text-slate-500">Oleh: {{ $event->creator->name ?? 'System' }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-500 text-center py-6 text-xs">Belum ada riwayat tracking event.</p>
                    @endforelse

                </div>
            </div>

        </div>

    </div>

</div>

<!-- ========================================== -->
<!-- MODAL 1: TAMBAH EVENT TRACKING (Slide 07) -->
<!-- ========================================== -->
<div id="modal-event" class="hidden fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <h3 class="text-sm font-bold text-white">Tambah Milestone Tracking Baru</h3>
            <button onclick="document.getElementById('modal-event').classList.add('hidden')" class="text-slate-400 hover:text-white">✕</button>
        </div>
        <form action="{{ route('tracking.store', $shipment->id) }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 mb-1">Event Type</label>
                <select name="event_type" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
                    <option value="PICKED_UP">PICKED_UP</option>
                    <option value="DEPARTED_PORT">DEPARTED_PORT</option>
                    <option value="PORT_CALL">PORT_CALL (Transit Singgah)</option>
                    <option value="IN_TRANSIT">IN_TRANSIT</option>
                    <option value="CUSTOMS_CLEARED">CUSTOMS_CLEARED</option>
                    <option value="ARRIVED_PORT">ARRIVED_PORT</option>
                    <option value="DELIVERED">DELIVERED (Final)</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 mb-1">Nama Lokasi</label>
                <input type="text" name="location_name" placeholder="Contoh: Port of Singapore" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-300 mb-1">Latitude (Peta)</label>
                    <input type="number" step="any" name="latitude" placeholder="1.2902700" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
                </div>
                <div>
                    <label class="block text-slate-300 mb-1">Longitude (Peta)</label>
                    <input type="number" step="any" name="longitude" placeholder="103.8519590" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 mb-1">Waktu Kejadian (UTC)</label>
                <input type="datetime-local" name="event_time" required value="{{ date('Y-m-d\TH:i') }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
            </div>
            <div>
                <label class="block text-slate-300 mb-1">Keterangan / Deskripsi</label>
                <textarea name="description" rows="2" placeholder="Detail milestone perjalanan..." class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-event').classList.add('hidden')" class="px-3 py-1.5 bg-slate-800 text-slate-300 rounded-lg">Batal</button>
                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg">Simpan Event</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: LAPORKAN EXCEPTION (Slide 08)    -->
<!-- ========================================== -->
<div id="modal-exception" class="hidden fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <h3 class="text-sm font-bold text-rose-400">Laporkan Kendala Kargo (Exception)</h3>
            <button onclick="document.getElementById('modal-exception').classList.add('hidden')" class="text-slate-400 hover:text-white">✕</button>
        </div>
        <form action="{{ route('exceptions.store', $shipment->id) }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 mb-1">Jenis Kendala (Slide 08)</label>
                <select name="exception_type" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
                    <option value="PORT_CONGESTION">PORT_CONGESTION (Kepadatan Pelabuhan)</option>
                    <option value="CUSTOMS_HOLD">CUSTOMS_HOLD (Tertahan Bea Cukai)</option>
                    <option value="WEATHER_DELAY">WEATHER_DELAY (Cuaca Buruk / Badai)</option>
                    <option value="DAMAGED">DAMAGED (Kerusakan Fisik)</option>
                    <option value="MISROUTED">MISROUTED (Salah Rute)</option>
                    <option value="DOCUMENTATION_ISSUE">DOCUMENTATION_ISSUE (Izin/Dokumen)</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 mb-1">Dampak Keterlambatan (Jam)</label>
                <input type="number" name="delay_impact_hours" value="12" min="0" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
            </div>
            <div>
                <label class="block text-slate-300 mb-1">Penyebab Utama (Root Cause)</label>
                <textarea name="root_cause" rows="2" placeholder="Jelaskan alasan kendala..." required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-exception').classList.add('hidden')" class="px-3 py-1.5 bg-slate-800 text-slate-300 rounded-lg">Batal</button>
                <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-lg">Laporkan Exception</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi Peta Leaflet.js (Slide 09)
        const map = L.map('map');

        // Tile Layer OpenStreetMap + Attribution Wajib (Slide 09)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors | CargoTrace'
        }).addTo(map);

        const routeCoordinates = [];

        // 1. Marker Origin (Pelabuhan Asal - Hijau)
        @if ($shipment->origin && $shipment->origin->latitude && $shipment->origin->longitude)
            const originCoord = [{{ $shipment->origin->latitude }}, {{ $shipment->origin->longitude }}];
            routeCoordinates.push(originCoord);
            L.circleMarker(originCoord, {
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.9,
                radius: 8
            }).addTo(map).bindPopup("<strong>Origin (Asal):</strong><br>{{ $shipment->origin->name }} ({{ $shipment->origin->unlocode }})");
        @endif

        // 2. Marker Waypoints / Tracking Events (Biru)
        @foreach ($shipment->trackingEvents as $ev)
            @if ($ev->latitude && $ev->longitude)
                const evCoord_{{ $ev->id }} = [{{ $ev->latitude }}, {{ $ev->longitude }}];
                routeCoordinates.push(evCoord_{{ $ev->id }});
                L.circleMarker(evCoord_{{ $ev->id }}, {
                    color: '#3b82f6',
                    fillColor: '#3b82f6',
                    fillOpacity: 0.8,
                    radius: 6
                }).addTo(map).bindPopup("<strong>{{ $ev->event_type }}</strong><br>{{ $ev->location_name }}<br><small>{{ $ev->event_time->format('d M Y, H:i') }}</small>");
            @endif
        @endforeach

        // 3. Marker Destination (Pelabuhan Tujuan - Merah)
        @if ($shipment->destination && $shipment->destination->latitude && $shipment->destination->longitude)
            const destCoord = [{{ $shipment->destination->latitude }}, {{ $shipment->destination->longitude }}];
            routeCoordinates.push(destCoord);
            L.circleMarker(destCoord, {
                color: '#ef4444',
                fillColor: '#ef4444',
                fillOpacity: 0.9,
                radius: 8
            }).addTo(map).bindPopup("<strong>Destination (Tujuan):</strong><br>{{ $shipment->destination->name }} ({{ $shipment->destination->unlocode }})");
        @endif

        // 4. Gambar Garis Rute Pelayaran (Polyline)
        if (routeCoordinates.length > 1) {
            const polyline = L.polyline(routeCoordinates, {
                color: '#38bdf8',
                weight: 3.5,
                opacity: 0.8,
                dashArray: '8, 6'
            }).addTo(map);

            // Auto-fit batas peta agar seluruh rute terlihat sempurna
            map.fitBounds(polyline.getBounds(), { padding: [40, 40] });
        } else if (routeCoordinates.length === 1) {
            map.setView(routeCoordinates[0], 5);
        } else {
            map.setView([0, 0], 2);
        }
    });
</script>
@endpush