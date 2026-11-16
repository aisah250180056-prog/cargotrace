@extends('layouts.app')

@section('title', 'Buat Pengiriman Kargo Baru - CargoTrace')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Buat Pengiriman Kargo Baru (New Consignment)</h1>
            <p class="text-xs text-slate-400">Pencatatan data shipment dan rincian manifest barang kargo</p>
        </div>
        <a href="{{ route('shipments.index') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-medium transition">
            ← Kembali ke Dashboard
        </a>
    </div>

    <form action="{{ route('shipments.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: Informasi Utama Pengiriman (Slide 06 & 11) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-sm">
            <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400 border-b border-slate-800 pb-2">
                1. Data Utama Pengiriman & Operator Logistik
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Customer (Pemilik Kargo) *</label>
                    <select name="customer_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2.5 text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">-- Pilih Customer --</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Carrier (Maskapai Pengiriman) *</label>
                    <select name="carrier_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2.5 text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">-- Pilih Carrier --</option>
                        @foreach ($carriers as $carrier)
                            <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                {{ $carrier->name }} (Mode: {{ $carrier->transport_mode }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Pelabuhan Asal (Origin) *</label>
                    <select name="origin_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2.5 text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">-- Pilih Pelabuhan Asal --</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('origin_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->unlocode }} - {{ $loc->country_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Pelabuhan Tujuan (Destination) *</label>
                    <select name="destination_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2.5 text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">-- Pilih Pelabuhan Tujuan --</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('destination_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->unlocode }} - {{ $loc->country_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Rencana Berangkat (Expected Departure) *</label>
                    <input type="datetime-local" name="expected_departure" value="{{ old('expected_departure', date('Y-m-d\T08:00')) }}" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2.5 text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Estimasi Tiba (Expected Arrival) *</label>
                    <input type="datetime-local" name="expected_arrival" value="{{ old('expected_arrival', date('Y-m-d\T18:00', strtotime('+7 days'))) }}" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2.5 text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-medium text-slate-300 mb-1">Catatan Tambahan (Notes)</label>
                <textarea name="notes" rows="2" placeholder="Instruksi khusus penanganan kargo kontainer..." class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2.5 text-white focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Section 2: Rincian Barang Kargo (Items Manifest) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">
                    2. Rincian Barang Kargo (Cargo Items)
                </h2>
                <button type="button" onclick="addItemRow()" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-blue-400 border border-slate-700 rounded-lg text-xs font-medium transition">
                    + Tambah Baris Barang
                </button>
            </div>

            <div id="items-container" class="space-y-3">
                <!-- Baris Item Pertama -->
                <div class="item-row grid grid-cols-1 md:grid-cols-12 gap-2 bg-slate-950/60 p-3 rounded-xl border border-slate-800 text-xs items-center">
                    <div class="md:col-span-4">
                        <label class="block text-slate-400 text-[10px] mb-1">Nama Barang *</label>
                        <input type="text" name="items[0][item_name]" placeholder="Contoh: Electronic Components" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-slate-400 text-[10px] mb-1">Jumlah (Qty) *</label>
                        <input type="number" name="items[0][quantity]" value="1" min="1" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-slate-400 text-[10px] mb-1">Berat (kg) *</label>
                        <input type="number" step="0.01" name="items[0][weight_kg]" placeholder="2500.00" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-slate-400 text-[10px] mb-1">Volume (CBM)</label>
                        <input type="number" step="0.01" name="items[0][volume_cbm]" placeholder="12.5" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between pt-4">
                        <label class="flex items-center space-x-1 cursor-pointer">
                            <input type="checkbox" name="items[0][is_dangerous_goods]" value="1" class="rounded bg-slate-800 text-rose-500">
                            <span class="text-[10px] text-rose-400 font-semibold">DG</span>
                        </label>
                        <button type="button" onclick="removeItemRow(this)" class="text-rose-400 hover:text-rose-300 font-bold px-2 py-1">✕</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('shipments.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-blue-600/30 transition">
                Simpan & Terbitkan Shipment
            </button>
        </div>
    </form>
</div>

<script>
    let itemCount = 1;

    function addItemRow() {
        const container = document.getElementById('items-container');
        const row = document.createElement('div');
        row.className = 'item-row grid grid-cols-1 md:grid-cols-12 gap-2 bg-slate-950/60 p-3 rounded-xl border border-slate-800 text-xs items-center';
        row.innerHTML = `
            <div class="md:col-span-4">
                <label class="block text-slate-400 text-[10px] mb-1">Nama Barang *</label>
                <input type="text" name="items[${itemCount}][item_name]" placeholder="Nama barang kargo..." required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-[10px] mb-1">Jumlah (Qty) *</label>
                <input type="number" name="items[${itemCount}][quantity]" value="1" min="1" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-[10px] mb-1">Berat (kg) *</label>
                <input type="number" step="0.01" name="items[${itemCount}][weight_kg]" placeholder="0.00" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-[10px] mb-1">Volume (CBM)</label>
                <input type="number" step="0.01" name="items[${itemCount}][volume_cbm]" placeholder="0.00" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-white">
            </div>
            <div class="md:col-span-2 flex items-center justify-between pt-4">
                <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" name="items[${itemCount}][is_dangerous_goods]" value="1" class="rounded bg-slate-800 text-rose-500">
                    <span class="text-[10px] text-rose-400 font-semibold">DG</span>
                </label>
                <button type="button" onclick="removeItemRow(this)" class="text-rose-400 hover:text-rose-300 font-bold px-2 py-1">✕</button>
            </div>
        `;
        container.appendChild(row);
        itemCount++;
    }

    function removeItemRow(button) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            button.closest('.item-row').remove();
        } else {
            alert('Minimal harus ada 1 item barang kargo!');
        }
    }
</script>
@endsection