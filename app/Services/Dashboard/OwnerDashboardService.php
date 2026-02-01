<?php

namespace App\Services\Dashboard;

use App\Models\Shipment;
use App\Models\CourierSettlement;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OwnerDashboardService
{
    /**
     * Get owner dashboard metrics
     */
    public function getMetrics(?int $branchId, Carbon $today, Carbon $thisWeek, Carbon $thisMonth): array
    {
        // Optimize: Use single query with conditional aggregation
        $baseQuery = Shipment::query();
        if ($branchId) {
            $baseQuery->where('branch_id', $branchId);
        }

        $stats = $baseQuery
            ->selectRaw('
                COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today_total_paket,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? AND type = \'cod\' AND cod_status = \'lunas\' THEN cod_amount END), 0) as today_cod_collected,
                COUNT(CASE WHEN DATE(delivered_at) = ? AND status = \'diterima\' THEN 1 END) as today_delivered,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as week_total_paket,
                COALESCE(SUM(CASE WHEN created_at >= ? AND type = \'cod\' AND cod_status = \'lunas\' THEN cod_amount END), 0) as week_cod_collected,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as month_total_paket,
                COALESCE(SUM(CASE WHEN created_at >= ? AND type = \'cod\' AND cod_status = \'lunas\' THEN cod_amount END), 0) as month_cod_collected
            ', [
                $today->format('Y-m-d'), $today->format('Y-m-d'), $today->format('Y-m-d'),
                $thisWeek->format('Y-m-d H:i:s'), $thisWeek->format('Y-m-d H:i:s'),
                $thisMonth->format('Y-m-d H:i:s'), $thisMonth->format('Y-m-d H:i:s')
            ])
            ->first();

        $settlementQuery = CourierSettlement::query();
        if ($branchId) {
            $settlementQuery->where('branch_id', $branchId);
        }
        $pendingSettlements = $settlementQuery->where('status', 'pending')->count();

        $totalBranches = Branch::where('status', 'active')->count();

        $courierQuery = User::where('role', 'kurir')->where('status', 'active');
        if ($branchId) {
            $courierQuery->where('branch_id', $branchId);
        }
        $totalCouriers = $courierQuery->count();

        return [
            'today' => [
                'total_paket' => $stats->today_total_paket ?? 0,
                'cod_collected' => $stats->today_cod_collected ?? 0,
                'delivered' => $stats->today_delivered ?? 0,
            ],
            'this_week' => [
                'total_paket' => $stats->week_total_paket ?? 0,
                'cod_collected' => $stats->week_cod_collected ?? 0,
            ],
            'this_month' => [
                'total_paket' => $stats->month_total_paket ?? 0,
                'cod_collected' => $stats->month_cod_collected ?? 0,
            ],
            'pending_settlements' => $pendingSettlements,
            'total_branches' => $totalBranches,
            'total_couriers' => $totalCouriers,
        ];
    }
}

