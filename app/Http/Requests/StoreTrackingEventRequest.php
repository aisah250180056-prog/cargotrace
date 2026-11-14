<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrackingEventRequest extends FormRequest
{
    /**
     * Otorisasi: Hanya Admin dan Operator yang boleh menambah event pelacakan (Slide 05)
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || $user->isOperator());
    }

    /**
     * Aturan Validasi Event Pelacakan (Slide 07)
     */
    public function rules(): array
    {
        return [
            'event_type'    => ['required', 'string', 'max:50'], // Contoh: PICKED_UP, DEPARTED_PORT, IN_TRANSIT, DELIVERED
            'status_code'   => ['nullable', 'string', 'max:50'],
            'location_name' => ['required', 'string', 'max:255'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'event_time'    => ['required', 'date'],
            'description'   => ['nullable', 'string', 'max:1000'],
        ];
    }
}