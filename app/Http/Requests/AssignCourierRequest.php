<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignCourierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('assign', \App\Models\Shipment::class);
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
            'shipment_ids.*' => ['exists:shipments,id'],
        ];
    }
}
