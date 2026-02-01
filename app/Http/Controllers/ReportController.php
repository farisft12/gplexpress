<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * COD Report
     */
    public function codReport(Request $request)
    {
        $user = auth()->user();
        
        $query = Shipment::where('type', 'cod')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as jumlah_paket'),
                DB::raw('SUM(cod_amount) as total_nilai_cod'),
                DB::raw('SUM(CASE WHEN cod_status = \'lunas\' THEN 1 ELSE 0 END) as cod_lunas'),
                DB::raw('SUM(CASE WHEN cod_status = \'belum_lunas\' THEN 1 ELSE 0 END) as cod_belum_lunas'),
                DB::raw('SUM(CASE WHEN cod_status = \'lunas\' THEN cod_amount ELSE 0 END) as nilai_lunas'),
                DB::raw('SUM(CASE WHEN cod_status = \'belum_lunas\' THEN cod_amount ELSE 0 END) as nilai_belum_lunas'),
            ])
            ->groupBy(DB::raw('DATE(created_at)'));

        // Branch scope: Super Admin/Owner sees all, others see only their branch
        if (!$user->canAccessAllBranches() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }

        // Grouping
        $groupBy = $request->get('group_by', 'day');
        if ($groupBy === 'week') {
            // PostgreSQL uses EXTRACT for week
            $query->selectRaw('EXTRACT(YEAR FROM created_at)::integer as year, EXTRACT(WEEK FROM created_at)::integer as week')
                ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at), EXTRACT(WEEK FROM created_at)'));
        } elseif ($groupBy === 'month') {
            $query->selectRaw('EXTRACT(YEAR FROM created_at)::integer as year, EXTRACT(MONTH FROM created_at)::integer as month')
                ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at), EXTRACT(MONTH FROM created_at)'));
        }

        $reports = $query->orderBy('date', 'desc')->paginate(30);

        // Calculate totals
        $totals = Shipment::where('type', 'cod')
            ->when(!$user->canAccessAllBranches() && $user->branch_id, function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->filled('courier_id'), function ($q) use ($request) {
                $q->where('courier_id', $request->courier_id);
            })
            ->selectRaw('
                COUNT(*) as total_paket,
                SUM(cod_amount) as total_nilai_cod,
                SUM(CASE WHEN cod_status = \'lunas\' THEN 1 ELSE 0 END) as total_lunas,
                SUM(CASE WHEN cod_status = \'belum_lunas\' THEN 1 ELSE 0 END) as total_belum_lunas,
                SUM(CASE WHEN cod_status = \'lunas\' THEN cod_amount ELSE 0 END) as total_nilai_lunas,
                SUM(CASE WHEN cod_status = \'belum_lunas\' THEN cod_amount ELSE 0 END) as total_nilai_belum_lunas
            ')
            ->first();

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

        return view('admin.reports.cod', compact('reports', 'couriers', 'totals', 'groupBy'));
    }

    /**
     * Non-COD Report
     */
    public function nonCodReport(Request $request)
    {
        $user = auth()->user();
        
        $query = Shipment::where('type', 'non_cod')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as jumlah_paket'),
                DB::raw('SUM(COALESCE(shipping_cost, 0)) as total_tarif'),
            ])
            ->groupBy(DB::raw('DATE(created_at)'));

        // Branch scope: Super Admin/Owner sees all, others see only their branch
        if (!$user->canAccessAllBranches() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }

        // Grouping
        $groupBy = $request->get('group_by', 'day');
        if ($groupBy === 'week') {
            // PostgreSQL uses EXTRACT for week
            $query->selectRaw('EXTRACT(YEAR FROM created_at)::integer as year, EXTRACT(WEEK FROM created_at)::integer as week')
                ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at), EXTRACT(WEEK FROM created_at)'));
        } elseif ($groupBy === 'month') {
            $query->selectRaw('EXTRACT(YEAR FROM created_at)::integer as year, EXTRACT(MONTH FROM created_at)::integer as month')
                ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at), EXTRACT(MONTH FROM created_at)'));
        }

        $reports = $query->orderBy('date', 'desc')->paginate(30);

        // Calculate totals
        $totals = Shipment::where('type', 'non_cod')
            ->when(!$user->canAccessAllBranches() && $user->branch_id, function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->filled('courier_id'), function ($q) use ($request) {
                $q->where('courier_id', $request->courier_id);
            })
            ->selectRaw('
                COUNT(*) as total_paket,
                SUM(COALESCE(shipping_cost, 0)) as total_tarif
            ')
            ->first();

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

        return view('admin.reports.non-cod', compact('reports', 'couriers', 'totals', 'groupBy'));
    }

    /**
     * COD Report Detail (Paket per periode)
     */
    public function codDetail(Request $request)
    {
        $user = auth()->user();
        
        $query = Shipment::where('type', 'cod')
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
     * Optimized: Use database join instead of get()->map()->filter()
     */
    public function courierBalance(Request $request)
    {
        $user = auth()->user();
        
        // Use join to get couriers with balances in a single query
        // This is more efficient than get()->map()->filter()
        $query = User::whereIn('users.role', ['kurir', 'courier_cabang'])
            ->where('users.status', 'active')
            ->leftJoin('courier_current_balances', 'users.id', '=', 'courier_current_balances.courier_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.branch_id',
                DB::raw('COALESCE(courier_current_balances.current_balance, 0) as balance')
            )
            ->having('balance', '>', 0) // Only show couriers with balance > 0
            ->orderBy('balance', 'desc');
        
        // Branch scope: Super Admin/Owner sees all, others see only their branch
        if (!$user->canAccessAllBranches() && $user->branch_id) {
            $query->where('users.branch_id', $user->branch_id);
        }
        
        $couriers = $query->get()
            ->map(function ($courier) {
                return [
                    'id' => $courier->id,
                    'name' => $courier->name,
                    'email' => $courier->email,
                    'balance' => (float) $courier->balance,
                ];
            })
            ->values();

        return view('admin.reports.courier-balance', compact('couriers'));
    }
}
