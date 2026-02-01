<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('shipment'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pickup,diproses,dalam_pengiriman,sampai_di_cabang_tujuan,diterima,gagal'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'in:cash,qris'],
        ];
    }
}
