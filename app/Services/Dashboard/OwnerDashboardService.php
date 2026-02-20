<?php

namespace App\Services\Dashboard;

use App\Models\Shipment;
use App\Models\CourierSettlement;
use App\Models\Branch;
use App\Models\User;
use App\Models\OperationalCost;
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

        $codTotal = 'cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)';
        $stats = $baseQuery
            ->selectRaw("
                COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today_total_paket,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? AND type = 'cod' AND cod_status = 'lunas' THEN {$codTotal} END), 0) as today_cod_collected,
                COUNT(CASE WHEN DATE(delivered_at) = ? AND status = 'diterima' THEN 1 END) as today_delivered,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as week_total_paket,
                COALESCE(SUM(CASE WHEN created_at >= ? AND type = 'cod' AND cod_status = 'lunas' THEN {$codTotal} END), 0) as week_cod_collected,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as month_total_paket,
                COALESCE(SUM(CASE WHEN created_at >= ? AND type = 'cod' AND cod_status = 'lunas' THEN {$codTotal} END), 0) as month_cod_collected
            ", [
                $today->format('Y-m-d'), $today->format('Y-m-d'), $today->format('Y-m-d'),
                $thisWeek->format('Y-m-d H:i:s'), $thisWeek->format('Y-m-d H:i:s'),
                $thisMonth->format('Y-m-d H:i:s'), $thisMonth->format('Y-m-d H:i:s')
            ])
            ->first();

        // Calculate Revenue (Total Pendapatan - Biaya Operasional)
        // Total Pendapatan (COD + Non-COD)
        $codRevenueQuery = Shipment::query();
        if ($branchId) {
            $codRevenueQuery->where('branch_id', $branchId);
        }
        $codRevenue = $codRevenueQuery
            ->where('type', 'cod')
            ->where('cod_status', 'lunas')
            ->selectRaw("COALESCE(SUM({$codTotal}), 0) as total")
            ->value('total') ?? 0;
        
        $nonCodRevenueQuery = Shipment::query();
        if ($branchId) {
            $nonCodRevenueQuery->where('branch_id', $branchId);
        }
        $nonCodRevenue = $nonCodRevenueQuery
            ->where('type', 'non_cod')
            ->where('status', 'diterima')
            ->selectRaw("COALESCE(SUM(shipping_cost), 0) as total")
            ->value('total') ?? 0;
        
        $totalPendapatan = $codRevenue + $nonCodRevenue;
        
        // Biaya Operasional
        $operationalCostQuery = OperationalCost::query();
        if ($branchId) {
            $operationalCostQuery->where('branch_id', $branchId);
        }
        $operationalCosts = $operationalCostQuery
            ->selectRaw("COALESCE(SUM(amount), 0) as total")
            ->value('total') ?? 0;
        
        $revenue = $totalPendapatan - $operationalCosts;

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
            'revenue' => $revenue,
            'total_branches' => $totalBranches,
            'total_couriers' => $totalCouriers,
        ];
    }
}

