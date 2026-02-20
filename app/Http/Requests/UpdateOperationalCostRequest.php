<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateOperationalCostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->isOwner() || Auth::user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];

        // Branch ID is optional for owner, but restricted for admin
        if (Auth::user()->isOwner()) {
            $rules['branch_id'] = ['nullable', 'exists:branches,id'];
        } elseif (Auth::user()->isAdmin()) {
            // Admin can only update for their own branch, so branch_id is implicitly set
            $rules['branch_id'] = ['nullable'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal harus diisi.',
            'date.date' => 'Tanggal tidak valid.',
            'description.required' => 'Uraian operasional harus diisi.',
            'description.max' => 'Uraian operasional maksimal 500 karakter.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'amount.required' => 'Tarif harus diisi.',
            'amount.numeric' => 'Tarif harus berupa angka.',
            'amount.min' => 'Tarif minimal 0.01.',
        ];
    }
}
