<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettlementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\CourierSettlement::class);
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,transfer'],
            'notes' => ['nullable', 'string', 'max:500'],
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
            'courier_id.exists' => 'Kurir yang dipilih tidak ditemukan.',
            'amount.required' => 'Jumlah settlement harus diisi.',
            'amount.min' => 'Jumlah settlement minimal Rp 0.01.',
            'method.required' => 'Metode pembayaran harus dipilih.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $courier = \App\Models\User::find($this->courier_id);
            
            if ($courier && !$courier->isKurir()) {
                $validator->errors()->add('courier_id', 'User yang dipilih bukan kurir.');
            }

            // Check balance if courier is valid
            if ($courier && $courier->isKurir()) {
                $currentBalance = \App\Models\CourierCurrentBalance::getBalance($courier->id);
                if ($this->amount > $currentBalance) {
                    $validator->errors()->add('amount', 'Jumlah settlement melebihi saldo kurir. Saldo tersedia: Rp ' . number_format($currentBalance, 0, ',', '.'));
                }
            }
        });
    }
}
