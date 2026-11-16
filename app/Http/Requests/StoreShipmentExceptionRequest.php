<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentExceptionRequest extends FormRequest
{
    /**
     * Otorisasi: Hanya Admin dan Operator yang boleh melaporkan kendala (Slide 05)
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || $user->isOperator());
    }

    /**
     * Aturan Validasi Exception (Slide 08)
     */
    public function rules(): array
    {
        return [
            'exception_type' => [
                'required',
                'in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE',
            ],
            'severity'           => ['nullable', 'in:LOW,MEDIUM,HIGH'],
            'delay_impact_hours' => ['required', 'integer', 'min:0'],
            'root_cause'         => ['required', 'string', 'max:1000'],
            'resolution_notes'   => ['nullable', 'string', 'max:1000'],
            'detected_at'        => ['nullable', 'date'],
        ];
    }
}