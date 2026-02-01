<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\CourierSettlement;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    /**
     * Manager Dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Verify user is manager or owner
        if (!$user->isManager() && !$user->isOwner()) {
            abort(403);
        }

        // Owner can filter by branch, otherwise use user's branch
        $branchId = $user->isOwner() ? $request->get('branch_id') : $user->branch_id;
        $today = today();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Overview Metrics
        $metrics = $this->getOverviewMetrics($branchId, $today, $thisWeek, $thisMonth);

        // Courier Performance Summary
        $courierPerformance = $this->getCourierPerformance($branchId, $thisWeek);

        // Recent Settlements
        $recentSettlements = CourierSettlement::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['courier', 'confirmedBy'])
            ->latest()
            ->limit(5)
            ->get();

        // SLA Achievement
        $slaMetrics = $this->getSlaMetrics($branchId, $thisWeek);

        // Zone Distribution
        $zoneDistribution = $this->getZoneDistribution($branchId, $thisWeek);

        // Recent Shipments
        $recentShipments = Shipment::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['courier', 'zone'])
            ->latest()
            ->limit(10)
            ->get();

        // Get branches list for owner filter
        $branches = $user->isOwner() ? \App\Models\Branch::all() : collect();

        return view('dashboard.manager', compact(
            'metrics',
            'courierPerformance',
            'recentSettlements',
            'slaMetrics',
            'zoneDistribution',
            'recentShipments',
            'branches',
            'branchId'
        ));
    }

    /**
     * Data Barang Keluar Masuk per Cabang
     */
    public function barangKeluarMasuk(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isManager() && !$user->isOwner()) {
            abort(403);
        }

        $branchId = $user->isOwner() ? $request->get('branch_id') : $user->branch_id;
        
        if (!$branchId && !$user->isOwner()) {
            abort(403, 'Anda harus terhubung ke cabang untuk melihat data ini.');
        }

        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Paket Keluar (dari cabang ini)
        $paketKeluar = Shipment::where('origin_branch_id', $branchId)
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->with(['destinationBranch', 'courier'])
            ->latest()
            ->paginate(20, ['*'], 'keluar');

        // Paket Masuk (ke cabang ini)
        $paketMasuk = Shipment::where('destination_branch_id', $branchId)
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->with(['originBranch', 'courier'])
            ->latest()
            ->paginate(20, ['*'], 'masuk');

        // Summary
        $summary = [
            'total_keluar' => Shipment::where('origin_branch_id', $branchId)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
            'total_masuk' => Shipment::where('destination_branch_id', $branchId)
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
            'cod_keluar' => Shipment::where('origin_branch_id', $branchId)
                ->where('type', 'cod')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('cod_amount'),
            'cod_masuk' => Shipment::where('destination_branch_id', $branchId)
                ->where('type', 'cod')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('cod_amount'),
        ];

        $branches = $user->isOwner() ? \App\Models\Branch::where('status', 'active')->get() : collect();
        $selectedBranch = $branchId ? \App\Models\Branch::find($branchId) : null;

        return view('manager.barang-keluar-masuk', compact(
            'paketKeluar',
            'paketMasuk',
            'summary',
            'branchId',
            'dateFrom',
            'dateTo',
            'branches',
            'selectedBranch'
        ));
    }

    /**
     * Get overview metrics
     */
    protected function getOverviewMetrics(?int $branchId, Carbon $today, Carbon $thisWeek, Carbon $thisMonth): array
    {
        $query = Shipment::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return [
            'today' => [
                'total_paket' => (clone $query)->whereDate('created_at', $today)->count(),
                'cod_collected' => (clone $query)->whereDate('created_at', $today)
                    ->where('type', 'cod')
                    ->where('cod_status', 'lunas')
                    ->sum('cod_amount'),
                'delivered' => (clone $query)->whereDate('delivered_at', $today)
                    ->where('status', 'diterima')
                    ->count(),
            ],
            'this_week' => [
                'total_paket' => (clone $query)->where('created_at', '>=', $thisWeek)->count(),
                'cod_collected' => (clone $query)->where('created_at', '>=', $thisWeek)
                    ->where('type', 'cod')
                    ->where('cod_status', 'lunas')
                    ->sum('cod_amount'),
                'delivered' => (clone $query)->where('delivered_at', '>=', $thisWeek)
                    ->where('status', 'diterima')
                    ->count(),
            ],
            'this_month' => [
                'total_paket' => (clone $query)->where('created_at', '>=', $thisMonth)->count(),
                'cod_collected' => (clone $query)->where('created_at', '>=', $thisMonth)
                    ->where('type', 'cod')
                    ->where('cod_status', 'lunas')
                    ->sum('cod_amount'),
                'delivered' => (clone $query)->where('delivered_at', '>=', $thisMonth)
                    ->where('status', 'diterima')
                    ->count(),
            ],
            'pending_settlements' => CourierSettlement::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->where('status', 'pending')
                ->count(),
            'total_couriers' => User::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('role', ['kurir', 'courier_cabang'])
                ->where('status', 'active')
                ->count(),
        ];
    }

    /**
     * Get courier performance summary
     * Optimized for large datasets using database aggregation
     */
    protected function getCourierPerformance(?int $branchId, Carbon $thisWeek): array
    {
        $couriers = User::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'branch_id')
            ->get();

        $performance = [];
        foreach ($couriers as $courier) {
            // Use database aggregation instead of loading all shipments
            $total = Shipment::where('courier_id', $courier->id)
                ->where('created_at', '>=', $thisWeek)
                ->count();
            
            $delivered = Shipment::where('courier_id', $courier->id)
                ->where('created_at', '>=', $thisWeek)
                ->where('status', 'diterima')
                ->count();

            $performance[] = [
                'courier' => $courier,
                'total_paket' => $total,
                'delivered' => $delivered,
                'success_rate' => $total > 0 ? round(($delivered / $total) * 100, 1) : 0,
            ];
        }

        // Sort by success rate descending
        usort($performance, fn($a, $b) => $b['success_rate'] <=> $a['success_rate']);

        return $performance;
    }

    /**
     * Get SLA metrics
     * Optimized for large datasets using database aggregation
     */
    protected function getSlaMetrics(?int $branchId, Carbon $thisWeek): array
    {
        $query = Shipment::where('created_at', '>=', $thisWeek)
            ->whereHas('shipmentSla');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Use database aggregation instead of loading all records
        $total = $query->count();
        
        $onTime = (clone $query)->whereHas('shipmentSla', function($q) {
            $q->where('status', 'on_time');
        })->count();
        
        $late = (clone $query)->whereHas('shipmentSla', function($q) {
            $q->where('status', 'late');
        })->count();
        
        $failed = (clone $query)->whereHas('shipmentSla', function($q) {
            $q->where('status', 'failed');
        })->count();

        return [
            'total' => $total,
            'on_time' => $onTime,
            'late' => $late,
            'failed' => $failed,
            'on_time_percentage' => $total > 0 ? round(($onTime / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Get zone distribution
     */
    protected function getZoneDistribution(?int $branchId, Carbon $thisWeek): array
    {
        $zones = Zone::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'active')
            ->get();

        $distribution = [];
        foreach ($zones as $zone) {
            $count = Shipment::where('zone_id', $zone->id)
                ->where('created_at', '>=', $thisWeek)
                ->count();

            $distribution[] = [
                'zone' => $zone,
                'count' => $count,
            ];
        }

        return $distribution;
    }
}
