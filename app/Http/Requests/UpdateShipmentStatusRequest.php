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
        // Get shipment ID from route parameter (could be shipmentId or shipment)
        $shipmentParam = $this->route('shipmentId') ?? $this->route('shipment');
        
        // Log for debugging
        \Log::info('UpdateShipmentStatusRequest: Checking authorization', [
            'shipmentId_param' => $this->route('shipmentId'),
            'shipment_param' => $this->route('shipment'),
            'resolved_param' => $shipmentParam,
            'user_id' => $this->user()->id,
            'user_role' => $this->user()->role,
            'user_branch_id' => $this->user()->branch_id,
        ]);
        
        // If it's already a model instance (from route model binding), use it
        if ($shipmentParam instanceof \App\Models\Shipment) {
            $result = $this->user()->can('updateStatus', $shipmentParam);
            \Log::info('UpdateShipmentStatusRequest: Authorization result (model instance)', [
                'authorized' => $result,
                'shipment_id' => $shipmentParam->id,
            ]);
            return $result;
        }
        
        // Otherwise resolve manually without BranchScope
        $shipment = \App\Models\Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->find($shipmentParam);
        
        if (!$shipment) {
            \Log::warning('UpdateShipmentStatusRequest: Shipment not found', [
                'shipment_param' => $shipmentParam,
            ]);
            return false;
        }
        
        $result = $this->user()->can('updateStatus', $shipment);
        \Log::info('UpdateShipmentStatusRequest: Authorization result', [
            'authorized' => $result,
            'shipment_id' => $shipment->id,
            'shipment_branch_id' => $shipment->branch_id,
            'shipment_destination_branch_id' => $shipment->destination_branch_id,
            'shipment_status' => $shipment->status,
        ]);
        
        return $result;
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
