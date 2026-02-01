<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\SlaDefinition;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EtaCalculationService
{
    /**
     * Calculate ETA for shipment when assigned to courier
     */
    public function calculateEta(Shipment $shipment): ?Carbon
    {
        if (!$shipment->assigned_at) {
            return null;
        }

        // Get historical average delivery time for this route
        $avgDeliveryHours = $this->getHistoricalAverage($shipment);

        // If no historical data, use SLA deadline as fallback
        if (!$avgDeliveryHours) {
            $slaDefinition = SlaDefinition::findByCode('regular');
            $avgDeliveryHours = $slaDefinition ? $slaDefinition->deadline_hours : 24;
        }

        // Calculate ETA from assigned_at
        $etaAt = Carbon::parse($shipment->assigned_at)->addHours($avgDeliveryHours);

        // Update shipment
        $shipment->update(['eta_at' => $etaAt]);

        return $etaAt;
    }

    /**
     * Get historical average delivery time for similar shipments
     */
    protected function getHistoricalAverage(Shipment $shipment): ?float
    {
        // Get average delivery time for same origin-destination route
        $avgHours = Shipment::where('origin_branch_id', $shipment->origin_branch_id)
            ->where('destination_branch_id', $shipment->destination_branch_id)
            ->where('status', 'diterima')
            ->whereNotNull('assigned_at')
            ->whereNotNull('delivered_at')
            ->where('id', '!=', $shipment->id)
            ->selectRaw('
                AVG(EXTRACT(EPOCH FROM (delivered_at - assigned_at)) / 3600) as avg_hours
            ')
            ->value('avg_hours');

        return $avgHours ? (float) $avgHours : null;
    }

    /**
     * Recalculate ETA when shipment status changes
     */
    public function recalculateEta(Shipment $shipment): void
    {
        // Only recalculate if assigned and not yet delivered
        if ($shipment->assigned_at && !$shipment->delivered_at) {
            $this->calculateEta($shipment);
        }
    }
}
