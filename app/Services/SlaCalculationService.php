<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\SlaDefinition;
use App\Models\ShipmentSla;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SlaCalculationService
{
    /**
     * Calculate and assign SLA to shipment
     */
    public function assignSla(Shipment $shipment, ?string $slaCode = null): ?ShipmentSla
    {
        // Determine SLA code if not provided
        if (!$slaCode) {
            $slaCode = $this->determineSlaCode($shipment);
        }

        // Get SLA definition
        $slaDefinition = SlaDefinition::findByCode($slaCode);
        if (!$slaDefinition) {
            Log::warning('SLA definition not found', ['code' => $slaCode, 'shipment_id' => $shipment->id]);
            return null;
        }

        // Calculate deadline from pickup time
        $deadlineAt = $this->calculateDeadline($shipment->created_at, $slaDefinition->deadline_hours);

        // Create or update SLA record
        $shipmentSla = ShipmentSla::updateOrCreate(
            ['shipment_id' => $shipment->id],
            [
                'sla_id' => $slaDefinition->id,
                'deadline_at' => $deadlineAt,
                'status' => 'on_time', // Will be updated when delivered
            ]
        );

        return $shipmentSla;
    }

    /**
     * Determine SLA code based on shipment characteristics
     */
    protected function determineSlaCode(Shipment $shipment): string
    {
        // Default to regular
        // Can be enhanced based on pricing table service_type or other factors
        return 'regular';
    }

    /**
     * Calculate deadline from pickup time
     */
    protected function calculateDeadline(Carbon $pickupTime, int $deadlineHours): Carbon
    {
        // Add deadline hours to pickup time
        // Consider business hours if needed (future enhancement)
        return $pickupTime->copy()->addHours($deadlineHours);
    }

    /**
     * Update SLA status when shipment is delivered
     */
    public function updateSlaOnDelivery(Shipment $shipment): void
    {
        $shipmentSla = ShipmentSla::where('shipment_id', $shipment->id)->first();
        if (!$shipmentSla) {
            return;
        }

        if ($shipment->status === 'diterima' && $shipment->delivered_at) {
            $deliveredAt = Carbon::parse($shipment->delivered_at);
            $deadlineAt = Carbon::parse($shipmentSla->deadline_at);
            
            $hoursDifference = $deliveredAt->diffInHours($deadlineAt, false);
            
            $status = 'on_time';
            if ($deliveredAt->gt($deadlineAt)) {
                $status = 'late';
            }

            $shipmentSla->update([
                'status' => $status,
                'actual_delivered_at' => $deliveredAt,
                'hours_difference' => $hoursDifference,
            ]);
        } elseif ($shipment->status === 'gagal') {
            $shipmentSla->update([
                'status' => 'failed',
            ]);
        }
    }

    /**
     * Recalculate SLA status for all active shipments
     */
    public function recalculateSlaStatus(): void
    {
        $activeShipments = Shipment::whereIn('status', ['diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan'])
            ->whereHas('shipmentSla')
            ->get();

        foreach ($activeShipments as $shipment) {
            $shipmentSla = $shipment->shipmentSla;
            if (!$shipmentSla) {
                continue;
            }

            $deadlineAt = Carbon::parse($shipmentSla->deadline_at);
            $now = now();

            // If deadline passed and not delivered, mark as late
            if ($now->gt($deadlineAt) && $shipment->status !== 'diterima') {
                $hoursDifference = $now->diffInHours($deadlineAt, false);
                
                if ($shipmentSla->status !== 'late') {
                    $shipmentSla->update([
                        'status' => 'late',
                        'hours_difference' => $hoursDifference,
                    ]);
                }
            }
        }
    }
}
