<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Shipment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'source_type' => ['required', 'in:perorangan,ekspedisi_lain'],
            'expedition_id' => ['nullable', 'required_if:source_type,ekspedisi_lain', 'exists:expeditions,id'],
            'external_resi_number' => ['nullable', 'required_if:source_type,ekspedisi_lain', 'string', 'max:100'],

            'origin_branch_id' => ['required', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'exists:branches,id', 'different:origin_branch_id'],
            'package_type' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0.1'],
            'type' => ['required', 'in:cod,non_cod'],
            'cod_amount' => ['nullable', 'required_if:type,cod', 'numeric', 'min:0'],

            'cod_shipping_cost' => ['nullable', 'required_if:type,cod', 'numeric', 'min:0'],
            'cod_admin_fee' => ['nullable', 'required_if:type,cod', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'required_if:type,non_cod', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'in:cash,qris'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['nullable', 'required_if:source_type,perorangan', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/'],

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

            'source_type.required' => 'Sumber paket harus dipilih.',
            'expedition_id.required_if' => 'Ekspedisi asal wajib diisi untuk paket dari ekspedisi lain.',
            'external_resi_number.required_if' => 'Resi ekspedisi awal wajib diisi untuk paket dari ekspedisi lain.',
            'origin_branch_id.required' => 'Cabang asal harus dipilih.',
            'destination_branch_id.required' => 'Cabang tujuan harus dipilih.',
            'destination_branch_id.different' => 'Cabang tujuan harus berbeda dengan cabang asal.',
            'cod_amount.required_if' => 'Nominal COD wajib diisi untuk paket COD.',
            'cod_shipping_cost.required_if' => 'Ongkir COD wajib diisi untuk paket COD.',
            'cod_admin_fee.required_if' => 'Admin COD wajib diisi untuk paket COD.',
            'shipping_cost.required_if' => 'Biaya pengiriman wajib diisi untuk paket Non-COD.',
            'sender_phone.required_if' => 'No. HP pengirim wajib diisi untuk paket perorangan.',

            'sender_phone.regex' => 'Format nomor telepon pengirim tidak valid.',
            'receiver_phone.regex' => 'Format nomor telepon penerima tidak valid.',
        ];
    }
}
