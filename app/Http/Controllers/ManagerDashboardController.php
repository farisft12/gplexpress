<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\CourierSettlement;
use App\Models\User;
use App\Models\Zone;
use App\Services\Dashboard\ManagerDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    protected ManagerDashboardService $dashboardService;

    public function __construct(ManagerDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
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
        $metrics = $this->dashboardService->getOverviewMetrics($branchId, $today, $thisWeek, $thisMonth);

        // Courier Performance Summary
        $courierPerformance = $this->dashboardService->getCourierPerformance($branchId, $thisWeek);

        // Recent Settlements
        $recentSettlements = CourierSettlement::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['courier', 'confirmedBy'])
            ->latest()
            ->limit(5)
            ->get();

        // SLA Achievement
        $slaMetrics = $this->dashboardService->getSlaMetrics($branchId, $thisWeek);

        // Zone Distribution
        $zoneDistribution = $this->dashboardService->getZoneDistribution($branchId, $thisWeek);

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
        $period = $request->get('period', 'bulanan'); // harian, mingguan, bulanan, tahunan

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

        // Revenue calculation helper
        $calculateRevenue = function($query) {
            return $query->selectRaw('
                COALESCE(SUM(
                    CASE 
                        WHEN type = \'cod\' THEN 
                            cod_amount + COALESCE(cod_shipping_cost, 0) + COALESCE(cod_admin_fee, 0)
                        ELSE 
                            COALESCE(shipping_cost, 0)
                    END
                ), 0) as total
            ')->value('total') ?? 0;
        };

        // Calculate revenue based on selected period
        $revenueData = [];
        $periodStart = null;
        
        switch ($period) {
            case 'harian':
                $periodStart = Carbon::parse($dateFrom)->startOfDay();
                $periodEnd = Carbon::parse($dateTo)->endOfDay();
                $revenueData = [
                    'revenue_keluar' => $calculateRevenue(
                        Shipment::where('origin_branch_id', $branchId)
                            ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ),
                    'revenue_masuk' => $calculateRevenue(
                        Shipment::where('destination_branch_id', $branchId)
                            ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ),
                ];
                break;
                
            case 'mingguan':
                $periodStart = Carbon::now()->startOfWeek();
                $revenueData = [
                    'revenue_keluar' => $calculateRevenue(
                        Shipment::where('origin_branch_id', $branchId)
                            ->where('created_at', '>=', $periodStart)
                    ),
                    'revenue_masuk' => $calculateRevenue(
                        Shipment::where('destination_branch_id', $branchId)
                            ->where('created_at', '>=', $periodStart)
                    ),
                ];
                break;
                
            case 'bulanan':
                $periodStart = Carbon::now()->startOfMonth();
                $revenueData = [
                    'revenue_keluar' => $calculateRevenue(
                        Shipment::where('origin_branch_id', $branchId)
                            ->where('created_at', '>=', $periodStart)
                    ),
                    'revenue_masuk' => $calculateRevenue(
                        Shipment::where('destination_branch_id', $branchId)
                            ->where('created_at', '>=', $periodStart)
                    ),
                ];
                break;
                
            case 'tahunan':
                $periodStart = Carbon::now()->startOfYear();
                $revenueData = [
                    'revenue_keluar' => $calculateRevenue(
                        Shipment::where('origin_branch_id', $branchId)
                            ->where('created_at', '>=', $periodStart)
                    ),
                    'revenue_masuk' => $calculateRevenue(
                        Shipment::where('destination_branch_id', $branchId)
                            ->where('created_at', '>=', $periodStart)
                    ),
                ];
                break;
        }

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
                ->selectRaw('SUM(cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)) as total')
                ->value('total') ?? 0,
            'cod_masuk' => Shipment::where('destination_branch_id', $branchId)
                ->where('type', 'cod')
                ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->selectRaw('SUM(cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)) as total')
                ->value('total') ?? 0,
        ];

        $branches = $user->isOwner() ? \App\Models\Branch::where('status', 'active')->get() : collect();
        $selectedBranch = $branchId ? \App\Models\Branch::find($branchId) : null;

        return view('manager.barang-keluar-masuk', compact(
            'paketKeluar',
            'paketMasuk',
            'summary',
            'revenueData',
            'period',
            'branchId',
            'dateFrom',
            'dateTo',
            'branches',
            'selectedBranch'
        ));
    }

}
