<?php

namespace App\Services\Dashboard;

use App\Models\Shipment;
use App\Models\CourierSettlement;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManagerDashboardService
{
    /**
     * Get overview metrics
     */
    public function getOverviewMetrics(?int $branchId, Carbon $today, Carbon $thisWeek, Carbon $thisMonth): array
    {
        $baseQuery = Shipment::query();
        
        if ($branchId) {
            $baseQuery->where('branch_id', $branchId);
        }

<<<<<<< HEAD
        $codTotal = 'cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)';
        // Optimize: Use single query with conditional aggregation
        $stats = $baseQuery
            ->selectRaw("
                COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today_total_paket,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? AND type = 'cod' AND cod_status = 'lunas' THEN {$codTotal} END), 0) as today_cod_collected,
                COUNT(CASE WHEN DATE(delivered_at) = ? AND status = 'diterima' THEN 1 END) as today_delivered,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as week_total_paket,
                COALESCE(SUM(CASE WHEN created_at >= ? AND type = 'cod' AND cod_status = 'lunas' THEN {$codTotal} END), 0) as week_cod_collected,
                COUNT(CASE WHEN delivered_at >= ? AND status = 'diterima' THEN 1 END) as week_delivered,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as month_total_paket,
                COALESCE(SUM(CASE WHEN created_at >= ? AND type = 'cod' AND cod_status = 'lunas' THEN {$codTotal} END), 0) as month_cod_collected,
                COUNT(CASE WHEN delivered_at >= ? AND status = 'diterima' THEN 1 END) as month_delivered
            ", [
=======
        // Optimize: Use single query with conditional aggregation
        $stats = $baseQuery
            ->selectRaw('
                COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today_total_paket,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? AND type = \'cod\' AND cod_status = \'lunas\' THEN cod_amount END), 0) as today_cod_collected,
                COUNT(CASE WHEN DATE(delivered_at) = ? AND status = \'diterima\' THEN 1 END) as today_delivered,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as week_total_paket,
                COALESCE(SUM(CASE WHEN created_at >= ? AND type = \'cod\' AND cod_status = \'lunas\' THEN cod_amount END), 0) as week_cod_collected,
                COUNT(CASE WHEN delivered_at >= ? AND status = \'diterima\' THEN 1 END) as week_delivered,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as month_total_paket,
                COALESCE(SUM(CASE WHEN created_at >= ? AND type = \'cod\' AND cod_status = \'lunas\' THEN cod_amount END), 0) as month_cod_collected,
                COUNT(CASE WHEN delivered_at >= ? AND status = \'diterima\' THEN 1 END) as month_delivered
            ', [
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
                $today->format('Y-m-d'), $today->format('Y-m-d'), $today->format('Y-m-d'),
                $thisWeek->format('Y-m-d H:i:s'), $thisWeek->format('Y-m-d H:i:s'), $thisWeek->format('Y-m-d H:i:s'),
                $thisMonth->format('Y-m-d H:i:s'), $thisMonth->format('Y-m-d H:i:s'), $thisMonth->format('Y-m-d H:i:s')
            ])
            ->first();

        $settlementQuery = CourierSettlement::query();
        if ($branchId) {
            $settlementQuery->where('branch_id', $branchId);
        }
        $pendingSettlements = $settlementQuery->where('status', 'pending')->count();

        $courierQuery = User::whereIn('role', ['kurir', 'courier_cabang'])->where('status', 'active');
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
                'delivered' => $stats->week_delivered ?? 0,
            ],
            'this_month' => [
                'total_paket' => $stats->month_total_paket ?? 0,
                'cod_collected' => $stats->month_cod_collected ?? 0,
                'delivered' => $stats->month_delivered ?? 0,
            ],
            'pending_settlements' => $pendingSettlements,
            'total_couriers' => $totalCouriers,
        ];
    }

    /**
     * Get courier performance summary
     */
    public function getCourierPerformance(?int $branchId, Carbon $thisWeek): array
    {
        $query = Shipment::where('created_at', '>=', $thisWeek)
            ->whereNotNull('courier_id')
            ->select([
                'courier_id',
                DB::raw('COUNT(*) as total_paket'),
                DB::raw('COUNT(CASE WHEN status = \'diterima\' THEN 1 END) as delivered'),
                DB::raw('COUNT(CASE WHEN status = \'gagal\' THEN 1 END) as failed'),
<<<<<<< HEAD
                DB::raw('COALESCE(SUM(CASE WHEN type = \'cod\' AND cod_status = \'lunas\' THEN (cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)) END), 0) as cod_collected'),
=======
                DB::raw('COALESCE(SUM(CASE WHEN type = \'cod\' AND cod_status = \'lunas\' THEN cod_amount END), 0) as cod_collected'),
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
            ])
            ->groupBy('courier_id');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $performance = $query->with('courier:id,name,email')->get();

        return $performance->map(function ($item) {
            return [
                'courier' => $item->courier,
                'total_paket' => $item->total_paket,
                'delivered' => $item->delivered,
                'failed' => $item->failed,
                'success_rate' => $item->total_paket > 0 ? round(($item->delivered / $item->total_paket) * 100, 2) : 0,
                'cod_collected' => $item->cod_collected,
            ];
        })->toArray();
    }

    /**
     * Get SLA metrics
     */
    public function getSlaMetrics(?int $branchId, Carbon $thisWeek): array
    {
        $query = Shipment::where('created_at', '>=', $thisWeek)
            ->whereNotNull('delivered_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $stats = $query
            ->selectRaw('
                COUNT(*) as total_delivered,
                COUNT(CASE WHEN EXISTS (
                    SELECT 1 FROM shipment_slas 
                    WHERE shipment_slas.shipment_id = shipments.id 
                    AND shipment_slas.status = \'on_time\'
                ) THEN 1 END) as on_time,
                COUNT(CASE WHEN EXISTS (
                    SELECT 1 FROM shipment_slas 
                    WHERE shipment_slas.shipment_id = shipments.id 
                    AND shipment_slas.status = \'late\'
                ) THEN 1 END) as late
            ')
            ->first();

        $total = $stats->total_delivered ?? 0;

        return [
            'total_delivered' => $total,
            'on_time' => $stats->on_time ?? 0,
            'late' => $stats->late ?? 0,
            'on_time_percentage' => $total > 0 ? round((($stats->on_time ?? 0) / $total) * 100, 2) : 0,
            'late_percentage' => $total > 0 ? round((($stats->late ?? 0) / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get zone distribution
     */
    public function getZoneDistribution(?int $branchId, Carbon $thisWeek): array
    {
        $query = Shipment::where('created_at', '>=', $thisWeek)
            ->whereNotNull('zone_id')
            ->select([
                'zone_id',
                DB::raw('COUNT(*) as jumlah_paket'),
            ])
            ->groupBy('zone_id');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->with('zone:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'zone' => $item->zone,
                    'jumlah_paket' => $item->jumlah_paket,
                ];
            })
            ->toArray();
    }
}

