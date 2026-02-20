<?php

namespace App\Http\Controllers;

use App\Models\CourierPerformanceSnapshot;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    /**
     * Manager Cabang Dashboard - Branch Performance
     */
    public function managerDashboard(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isManager() && !$user->isOwner()) {
            abort(403);
        }

        // Owner can view all branches or specific branch
        $branchId = $user->isOwner() ? ($request->get('branch_id') ?: null) : $user->branch_id;
        $periodType = $request->get('period', 'week'); // day, week, month
        $periodDate = $request->get('date', now()->format('Y-m-d'));

        $dateRange = $this->getDateRange($periodType, $periodDate);

        // Branch SLA Achievement - allow null branchId for owner viewing all branches
        $slaMetrics = $this->getBranchSlaMetrics($branchId, $dateRange);

        // Courier Rankings (read-only) - allow null branchId for owner viewing all branches
        $courierRankings = $this->getCourierRankings($branchId, $dateRange);

        // Late Delivery Reasons - allow null branchId for owner viewing all branches
        $lateReasons = $this->getLateDeliveryReasons($branchId, $dateRange);

        return view('admin.performance.manager-dashboard', compact(
            'slaMetrics',
            'courierRankings',
            'lateReasons',
            'periodType',
            'periodDate',
            'branchId'
        ));
    }

    /**
     * Admin Cabang Dashboard - Monitoring Only
     */
    public function adminDashboard(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isAdminCabang() && !$user->isSuperAdmin()) {
            abort(403);
        }

        $branchId = $user->branch_id;
        $periodType = $request->get('period', 'week');
        $periodDate = $request->get('date', now()->format('Y-m-d'));

        $dateRange = $this->getDateRange($periodType, $periodDate);

        // Branch SLA Achievement
        $slaMetrics = $this->getBranchSlaMetrics($branchId, $dateRange);

        // Active Shipments
        $activeShipments = Shipment::where('branch_id', $branchId)
            ->whereIn('status', ['diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan'])
            ->with(['courier', 'shipmentSla'])
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.performance.admin-dashboard', compact(
            'slaMetrics',
            'activeShipments',
            'periodType',
            'periodDate'
        ));
    }

    /**
     * Courier Dashboard - Personal Performance
     */
    public function courierDashboard(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isCourierCabang() && !$user->isKurir()) {
            abort(403);
        }

        $periodType = $request->get('period', 'week');
        $periodDate = $request->get('date', now()->format('Y-m-d'));

        // Get performance snapshot
        $snapshot = CourierPerformanceSnapshot::getSnapshot(
            $user->id,
            $periodType,
            $periodDate
        );

        // If no snapshot, calculate on the fly
        if (!$snapshot) {
            $dateRange = $this->getDateRange($periodType, $periodDate);
            $metrics = $this->calculateCourierMetrics($user, $dateRange);
        } else {
            $metrics = $snapshot->metrics;
        }

        // Personal shipments
        $myShipments = Shipment::where('courier_id', $user->id)
            ->whereBetween('created_at', $this->getDateRange($periodType, $periodDate))
            ->with('shipmentSla')
            ->latest()
            ->paginate(20);

        return view('courier.performance.dashboard', compact(
            'metrics',
            'myShipments',
            'periodType',
            'periodDate'
        ));
    }

    /**
     * Get date range for period
     */
    protected function getDateRange(string $periodType, string $periodDate): array
    {
        $date = Carbon::parse($periodDate);
        $start = $date->copy();
        $end = $date->copy();

        switch ($periodType) {
            case 'day':
                $start->startOfDay();
                $end->endOfDay();
                break;
            case 'week':
                $start->startOfWeek();
                $end->endOfWeek();
                break;
            case 'month':
                $start->startOfMonth();
                $end->endOfMonth();
                break;
        }

        return [$start, $end];
    }

    /**
     * Get branch SLA metrics
     * Optimized for large datasets using database aggregation
     */
    protected function getBranchSlaMetrics(?int $branchId, array $dateRange): array
    {
        [$start, $end] = $dateRange;

        $query = Shipment::whereBetween('created_at', [$start, $end])
            ->whereHas('shipmentSla');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Use database aggregation instead of loading all records
        $total = $query->count();
        
        // Count on-time, late, and failed using database queries
        $onTimeQuery = clone $query;
        $onTime = $onTimeQuery->whereHas('shipmentSla', function($q) {
            $q->where('status', 'on_time');
        })->count();
        
        $lateQuery = clone $query;
        $late = $lateQuery->whereHas('shipmentSla', function($q) {
            $q->where('status', 'late');
        })->count();
        
        $failedQuery = clone $query;
        $failed = $failedQuery->whereHas('shipmentSla', function($q) {
            $q->where('status', 'failed');
        })->count();

        return [
            'total' => $total,
            'on_time' => $onTime,
            'late' => $late,
            'failed' => $failed,
            'on_time_percentage' => $total > 0 ? round(($onTime / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get courier rankings
     */
    protected function getCourierRankings(?int $branchId, array $dateRange): array
    {
        [$start, $end] = $dateRange;

        $query = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $couriers = $query->select('id', 'name', 'email', 'branch_id')->get();

        $rankings = [];
        foreach ($couriers as $courier) {
            // Calculate metrics on the fly for the date range
            $metrics = $this->calculateCourierMetrics($courier, $dateRange);
            
            // Get on-time and late counts from shipments with SLA
            $shipmentsQuery = Shipment::where('courier_id', $courier->id)
                ->whereBetween('created_at', [$start, $end])
                ->whereHas('shipmentSla');
            
            $onTime = (clone $shipmentsQuery)->whereHas('shipmentSla', function($q) {
                $q->where('status', 'on_time');
            })->count();
            
            $late = (clone $shipmentsQuery)->whereHas('shipmentSla', function($q) {
                $q->where('status', 'late');
            })->count();
            
            $total = $shipmentsQuery->count();
            $slaRate = $total > 0 ? round(($onTime / $total) * 100, 2) : 0;

            $rankings[] = [
                'courier_name' => $courier->name,
                'total' => $total,
                'on_time' => $onTime,
                'late' => $late,
                'sla_rate' => $slaRate,
            ];
        }

        // Sort by sla_rate descending
        usort($rankings, fn($a, $b) => $b['sla_rate'] <=> $a['sla_rate']);

        return $rankings;
    }

    /**
     * Get late delivery reasons
     * Optimized for large datasets using database aggregation
     */
    protected function getLateDeliveryReasons(?int $branchId, array $dateRange): array
    {
        [$start, $end] = $dateRange;

        $query = Shipment::whereBetween('created_at', [$start, $end])
            ->whereHas('shipmentSla', fn($q) => $q->where('status', 'late'));

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Use database aggregation instead of loading all records
        $reasons = $query->selectRaw('COALESCE(delivery_notes, \'Tidak ada catatan\') as reason, COUNT(*) as count')
            ->groupBy('delivery_notes')
            ->orderByDesc('count')
            ->limit(20) // Limit to top 20 reasons
            ->pluck('count', 'reason')
            ->toArray();

        return $reasons;
    }

    /**
     * Calculate courier metrics on the fly
     * Optimized for large datasets using database aggregation
     */
    protected function calculateCourierMetrics(User $courier, array $dateRange): array
    {
        [$start, $end] = $dateRange;

        $baseQuery = Shipment::where('courier_id', $courier->id)
            ->whereBetween('created_at', [$start, $end]);

        $total = $baseQuery->count();
        
        // Count on-time, late, and failed using database queries
        $onTime = (clone $baseQuery)->whereHas('shipmentSla', function($q) {
            $q->where('status', 'on_time');
        })->count();
        
        $late = (clone $baseQuery)->whereHas('shipmentSla', function($q) {
            $q->where('status', 'late');
        })->count();
        
        $failed = (clone $baseQuery)->where('status', 'gagal')->count();

        return [
            'total_paket' => $total,
            'on_time_count' => $onTime,
            'late_count' => $late,
            'failed_count' => $failed,
            'on_time_percentage' => $total > 0 ? round(($onTime / $total) * 100, 2) : 0,
            'late_percentage' => $total > 0 ? round(($late / $total) * 100, 2) : 0,
            'failed_percentage' => $total > 0 ? round(($failed / $total) * 100, 2) : 0,
            'avg_delivery_duration_hours' => 0, // Can be calculated if needed
            'cod_collection_accuracy' => 0, // Can be calculated if needed
        ];
    }
}
