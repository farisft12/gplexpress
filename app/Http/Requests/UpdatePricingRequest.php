<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('pricing'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'origin_branch_id' => ['required', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'exists:branches,id', 'different:origin_branch_id'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'cod_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cod_fee_fixed' => ['nullable', 'numeric', 'min:0'],
            'service_type' => ['required', 'in:reguler,express,same_day'],
            'estimated_days' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
