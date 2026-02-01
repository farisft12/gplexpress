<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\User;
use App\Services\Report\CodReportService;
use App\Services\Report\NonCodReportService;
use App\Services\Report\CourierBalanceReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected CodReportService $codReportService;
    protected NonCodReportService $nonCodReportService;
    protected CourierBalanceReportService $courierBalanceReportService;

    public function __construct(
        CodReportService $codReportService,
        NonCodReportService $nonCodReportService,
        CourierBalanceReportService $courierBalanceReportService
    ) {
        $this->codReportService = $codReportService;
        $this->nonCodReportService = $nonCodReportService;
        $this->courierBalanceReportService = $courierBalanceReportService;
    }
    /**
     * COD Report
     */
    public function codReport(Request $request)
    {
        $user = auth()->user();
        $filters = $request->only(['date_from', 'date_to', 'courier_id', 'group_by']);
        
        $query = $this->codReportService->buildQuery($user, $filters);
        $reports = $query->orderBy('date', 'desc')->paginate(30);
        
        $totals = $this->codReportService->getTotals($user, $filters);

        // Cache couriers list as it doesn't change frequently
        $couriers = cache()->remember('active_couriers', 3600, function () use ($user) {
            $query = User::whereIn('role', ['kurir', 'courier_cabang'])
                ->where('status', 'active')
                ->select('id', 'name', 'email', 'branch_id');
            
            // Branch scope for manager/admin
            if (!$user->canAccessAllBranches() && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
            
            return $query->orderBy('name')->get();
        });

        $groupBy = $filters['group_by'] ?? 'day';
        return view('admin.reports.cod', compact('reports', 'couriers', 'totals', 'groupBy'));
    }

    /**
     * Non-COD Report
     */
    public function nonCodReport(Request $request)
    {
        $user = auth()->user();
        $filters = $request->only(['date_from', 'date_to', 'courier_id', 'group_by']);
        
        $query = $this->nonCodReportService->buildQuery($user, $filters);
        $reports = $query->orderBy('date', 'desc')->paginate(30);
        
        $totals = $this->nonCodReportService->getTotals($user, $filters);

        // Cache couriers list as it doesn't change frequently
        $couriers = cache()->remember('active_couriers', 3600, function () use ($user) {
            $query = User::whereIn('role', ['kurir', 'courier_cabang'])
                ->where('status', 'active')
                ->select('id', 'name', 'email', 'branch_id');
            
            // Branch scope for manager/admin
            if (!$user->canAccessAllBranches() && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
            
            return $query->orderBy('name')->get();
        });

        $groupBy = $filters['group_by'] ?? 'day';
        return view('admin.reports.non-cod', compact('reports', 'couriers', 'totals', 'groupBy'));
    }

    /**
     * COD Report Detail (Paket per periode)
     */
    public function codDetail(Request $request)
    {
        $user = auth()->user();
        $filters = $request->only(['date_from', 'date_to', 'courier_id', 'cod_status', 'date', 'year', 'week', 'month', 'group_by']);
        
        $query = $this->codReportService->getDetailQuery($user, $filters);

        // Build date filter based on group_by
        $groupBy = $request->get('group_by', 'day');
        $date = $request->get('date');
        $year = $request->get('year');
        $week = $request->get('week');
        $month = $request->get('month');

        if ($groupBy === 'week' && $year && $week) {
            // PostgreSQL week calculation
            $query->whereRaw("EXTRACT(YEAR FROM created_at) = ?", [$year])
                ->whereRaw("EXTRACT(WEEK FROM created_at) = ?", [$week]);
            $periodLabel = "Minggu {$week}, {$year}";
        } elseif ($groupBy === 'month' && $year && $month) {
            $query->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
            $periodLabel = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
        } else {
            // Day
            $query->whereDate('created_at', $date);
            $periodLabel = \Carbon\Carbon::parse($date)->format('d M Y');
        }

        // Additional filters
        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }

        // Get summary before pagination - optimized with single query
        $summary = $query->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN cod_status = \'lunas\' THEN 1 ELSE 0 END) as lunas,
            SUM(CASE WHEN cod_status = \'belum_lunas\' THEN 1 ELSE 0 END) as belum_lunas,
            SUM(cod_amount) as total_nilai,
            SUM(CASE WHEN cod_status = \'lunas\' THEN cod_amount ELSE 0 END) as nilai_lunas,
            SUM(CASE WHEN cod_status = \'belum_lunas\' THEN cod_amount ELSE 0 END) as nilai_belum_lunas
        ')->first();

        $shipments = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.reports.cod-detail', compact('shipments', 'periodLabel', 'groupBy', 'summary'));
    }

    /**
     * Non-COD Report Detail (Paket per periode)
     */
    public function nonCodDetail(Request $request)
    {
        $user = auth()->user();
        
        $query = Shipment::where('type', 'non_cod')
            ->with(['courier', 'originBranch', 'destinationBranch']);

        // Branch scope: Super Admin/Owner sees all, others see only their branch
        if (!$user->canAccessAllBranches() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Build date filter based on group_by
        $groupBy = $request->get('group_by', 'day');
        $date = $request->get('date');
        $year = $request->get('year');
        $week = $request->get('week');
        $month = $request->get('month');

        if ($groupBy === 'week' && $year && $week) {
            $query->whereRaw("EXTRACT(YEAR FROM created_at) = ?", [$year])
                ->whereRaw("EXTRACT(WEEK FROM created_at) = ?", [$week]);
            $periodLabel = "Minggu {$week}, {$year}";
        } elseif ($groupBy === 'month' && $year && $month) {
            $query->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
            $periodLabel = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
        } else {
            // Day
            $query->whereDate('created_at', $date);
            $periodLabel = \Carbon\Carbon::parse($date)->format('d M Y');
        }

        // Additional filters
        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }

        $shipments = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.reports.non-cod-detail', compact('shipments', 'periodLabel', 'groupBy'));
    }

    /**
     * Courier Balance Report
     */
    public function courierBalance(Request $request)
    {
        $user = auth()->user();
        $filters = $request->only(['date_from', 'date_to', 'courier_id']);
        
        $query = $this->courierBalanceReportService->buildQuery($user, $filters);
        $couriers = $query->orderBy('total_cod_collected', 'desc')->get()
            ->map(function ($item) {
                $balance = $this->courierBalanceReportService->getCourierBalance($item->courier_id);
                return [
                    'id' => $item->courier_id,
                    'name' => $item->courier->name,
                    'email' => $item->courier->email,
                    'balance' => $balance,
                    'total_cod_collected' => $item->total_cod_collected,
                    'total_settlement' => $item->total_settlement,
                    'jumlah_paket_cod' => $item->jumlah_paket_cod,
                ];
            })
            ->values();

        return view('admin.reports.courier-balance', compact('couriers'));
    }
}
