<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationalCostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isOwner() || auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'operational_costs' => ['required', 'array', 'min:1'],
            'operational_costs.*.date' => ['required', 'date'],
            'operational_costs.*.description' => ['required', 'string', 'max:500'],
            'operational_costs.*.branch_id' => ['nullable', 'exists:branches,id'],
            'operational_costs.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'operational_costs.required' => 'Minimal harus ada satu baris data.',
            'operational_costs.array' => 'Format data tidak valid.',
            'operational_costs.min' => 'Minimal harus ada satu baris data.',
            'operational_costs.*.date.required' => 'Tanggal harus diisi pada semua baris.',
            'operational_costs.*.date.date' => 'Tanggal tidak valid.',
            'operational_costs.*.description.required' => 'Uraian operasional harus diisi pada semua baris.',
            'operational_costs.*.description.max' => 'Uraian operasional maksimal 500 karakter.',
            'operational_costs.*.branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'operational_costs.*.amount.required' => 'Tarif harus diisi pada semua baris.',
            'operational_costs.*.amount.numeric' => 'Tarif harus berupa angka.',
            'operational_costs.*.amount.min' => 'Tarif minimal 0.01.',
        ];
    }
}
