<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    /**
     * Otorisasi: Hanya Admin dan Operator yang boleh submit form ini (Slide 05)
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || $user->isOperator());
    }

    /**
     * Aturan Validasi Input (Slide 16)
     */
    public function rules(): array
    {
        return [
            'customer_id'        => ['required', 'exists:users,id'],
            'carrier_id'         => ['required', 'exists:carriers,id'],
            'origin_id'          => ['required', 'exists:locations,id', 'different:destination_id'],
            'destination_id'     => ['required', 'exists:locations,id'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival'   => ['required', 'date', 'after:expected_departure'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            
            // Validasi Barang Kargo (Array Items)
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.item_name'          => ['required', 'string', 'max:255'],
            'items.*.quantity'           => ['required', 'integer', 'min:1'],
            'items.*.weight_kg'          => ['required', 'numeric', 'min:0.01'],
            'items.*.volume_cbm'         => ['nullable', 'numeric', 'min:0'],
            'items.*.hs_code'            => ['nullable', 'string', 'max:50'],
            'items.*.is_dangerous_goods' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Pesan error yang ramah pengguna
     */
    public function messages(): array
    {
        return [
            'origin_id.different'    => 'Pelabuhan asal (origin) tidak boleh sama dengan pelabuhan tujuan (destination).',
            'expected_arrival.after' => 'Tanggal tiba (expected arrival) harus setelah tanggal keberangkatan (departure).',
            'items.min'              => 'Pengiriman kargo minimal harus memiliki 1 item barang.',
        ];
    }
}