<?php

namespace App\Services\Report;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NonCodReportService
{
    /**
     * Build Non-COD report query
     */
    public function buildQuery(User $user, array $filters = []): Builder
    {
        $query = Shipment::where('type', 'non_cod')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as jumlah_paket'),
                DB::raw('SUM(COALESCE(shipping_cost, 0)) as total_tarif'),
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
     * Get Non-COD report totals
     */
    public function getTotals(User $user, array $filters = []): object
    {
        $query = Shipment::where('type', 'non_cod');

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

        return $query->selectRaw('
                COUNT(*) as total_paket,
                SUM(COALESCE(shipping_cost, 0)) as total_tarif
            ')
            ->first();
    }
}

