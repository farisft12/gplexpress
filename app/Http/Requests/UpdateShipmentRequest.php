<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentRequest extends FormRequest
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
            'origin_branch_id' => ['required', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'exists:branches,id', 'different:origin_branch_id'],
            'package_type' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0.1'],
            'type' => ['required', 'in:cod,non_cod'],
            'cod_amount' => ['nullable', 'required_if:type,cod', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'required_if:type,non_cod', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'in:cash,qris'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/'],
            'sender_address' => ['required', 'string'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/'],
            'receiver_address' => ['required', 'string'],
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
            'origin_branch_id.required' => 'Cabang asal harus dipilih.',
            'destination_branch_id.required' => 'Cabang tujuan harus dipilih.',
            'destination_branch_id.different' => 'Cabang tujuan harus berbeda dengan cabang asal.',
            'cod_amount.required_if' => 'Jumlah COD wajib diisi untuk paket COD.',
            'shipping_cost.required_if' => 'Biaya pengiriman wajib diisi untuk paket Non-COD.',
            'sender_phone.regex' => 'Format nomor telepon pengirim tidak valid.',
            'receiver_phone.regex' => 'Format nomor telepon penerima tidak valid.',
        ];
    }
}
