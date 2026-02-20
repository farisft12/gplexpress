<?php

namespace App\Services\Dashboard;

use App\Models\CourierManifest;
use App\Models\Shipment;
use App\Models\CourierCurrentBalance;
use App\Models\User;

class CourierDashboardService
{
    /**
     * Get courier dashboard data
     */
    public function getDashboardData(User $courier): array
    {
        // Get today's manifest
        $manifest = CourierManifest::where('courier_id', $courier->id)
            ->where('manifest_date', today())
            ->where('status', 'active')
            ->first();

        // Get active shipments - limit to prevent large dataset issues
        // Include both packages assigned as origin courier (courier_id) and destination courier (destination_courier_id)
        // Disable BranchScope for courier to see all their assigned packages regardless of branch
        $activeShipments = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where(function($query) use ($courier) {
                $query->where('courier_id', $courier->id)
                      ->orWhere('destination_courier_id', $courier->id);
            })
            ->whereIn('status', ['diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan'])
            ->with([
                'originBranch:id,name,code', 
                'destinationBranch:id,name,code', 
                'courier:id,name', 
                'destinationCourier:id,name'
            ])
            ->withExists(['statusHistories as has_reached_destination' => function($query) {
                $query->where('status', 'sampai_di_cabang_tujuan');
            }])
            ->latest()
            ->limit(50)
            ->get();

        // Get balance from current balance table
        $balance = CourierCurrentBalance::getBalance($courier->id);

        return [
            'manifest' => $manifest,
            'active_shipments' => $activeShipments,
            'balance' => $balance,
            'total_today' => $manifest ? $manifest->total_packages : 0,
            'delivered_today' => $manifest ? $manifest->delivered_count : 0,
            'failed_today' => $manifest ? $manifest->failed_count : 0,
        ];
    }
}

