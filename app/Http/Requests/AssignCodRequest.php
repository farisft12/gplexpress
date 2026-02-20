<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignCodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'courier_id' => ['required', 'exists:users,id'],
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['required', 'exists:shipments,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'courier_id.required' => 'Kurir harus dipilih.',
            'courier_id.exists' => 'Kurir yang dipilih tidak valid.',
            'shipment_ids.required' => 'Minimal satu paket harus dipilih.',
            'shipment_ids.array' => 'Format paket tidak valid.',
            'shipment_ids.min' => 'Minimal satu paket harus dipilih.',
            'shipment_ids.*.exists' => 'Salah satu paket yang dipilih tidak valid.',
        ];
    }
}
