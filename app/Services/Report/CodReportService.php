<?php

namespace App\Services\Report;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CodReportService
{
    /**
     * Build COD report query
     */
    public function buildQuery(User $user, array $filters = []): Builder
    {
        $codTotal = 'cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)';
        $query = Shipment::where('type', 'cod')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as jumlah_paket'),
                DB::raw("SUM({$codTotal}) as total_nilai_cod"),
                DB::raw('SUM(CASE WHEN cod_status = \'lunas\' THEN 1 ELSE 0 END) as cod_lunas'),
                DB::raw('SUM(CASE WHEN cod_status = \'belum_lunas\' THEN 1 ELSE 0 END) as cod_belum_lunas'),
                DB::raw("SUM(CASE WHEN cod_status = 'lunas' THEN {$codTotal} ELSE 0 END) as nilai_lunas"),
                DB::raw("SUM(CASE WHEN cod_status = 'belum_lunas' THEN {$codTotal} ELSE 0 END) as nilai_belum_lunas"),
            ])
            ->groupBy(DB::raw('DATE(created_at)'));

        // Branch scope
        if (!$user->canAccessAllBranches() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['courier_id'])) {
            $query->where('courier_id', $filters['courier_id']);
        }

        // Apply grouping
        $groupBy = $filters['group_by'] ?? 'day';
        $this->applyGrouping($query, $groupBy);

        return $query;
    }

    /**
     * Apply grouping to query
     */
    protected function applyGrouping(Builder $query, string $groupBy): void
    {
        if ($groupBy === 'week') {
            $query->selectRaw('EXTRACT(YEAR FROM created_at)::integer as year, EXTRACT(WEEK FROM created_at)::integer as week')
                ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at), EXTRACT(WEEK FROM created_at)'));
        } elseif ($groupBy === 'month') {
            $query->selectRaw('EXTRACT(YEAR FROM created_at)::integer as year, EXTRACT(MONTH FROM created_at)::integer as month')
                ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at), EXTRACT(MONTH FROM created_at)'));
        }
    }

    /**
     * Get COD report totals
     */
    public function getTotals(User $user, array $filters = []): object
    {
        $query = Shipment::where('type', 'cod');

        // Branch scope
        if (!$user->canAccessAllBranches() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['courier_id'])) {
            $query->where('courier_id', $filters['courier_id']);
        }

        $codTotal = 'cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)';
        return $query->selectRaw("
                COUNT(*) as total_paket,
                SUM({$codTotal}) as total_nilai_cod,
                SUM(CASE WHEN cod_status = 'lunas' THEN 1 ELSE 0 END) as total_lunas,
                SUM(CASE WHEN cod_status = 'belum_lunas' THEN 1 ELSE 0 END) as total_belum_lunas,
                SUM(CASE WHEN cod_status = 'lunas' THEN {$codTotal} ELSE 0 END) as total_nilai_lunas,
                SUM(CASE WHEN cod_status = 'belum_lunas' THEN {$codTotal} ELSE 0 END) as total_nilai_belum_lunas
            ")
            ->first();
    }

    /**
     * Get COD detail report (packages per period)
     */
    public function getDetailQuery(User $user, array $filters = []): Builder
    {
        $query = Shipment::where('type', 'cod')
            ->with(['courier', 'originBranch', 'destinationBranch']);

        // Branch scope
        if (!$user->canAccessAllBranches() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['courier_id'])) {
            $query->where('courier_id', $filters['courier_id']);
        }

        if (!empty($filters['cod_status'])) {
            $query->where('cod_status', $filters['cod_status']);
        }

        return $query;
    }
}

