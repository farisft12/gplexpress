<?php

namespace App\Services\Report;

use App\Models\User;
use App\Models\CourierBalance;
use App\Models\CourierCurrentBalance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CourierBalanceReportService
{
    /**
     * Build courier balance report query
     */
    public function buildQuery(User $user, array $filters = []): Builder
    {
        $query = CourierBalance::with(['courier:id,name,email', 'shipment:id,resi_number'])
            ->select([
                'courier_id',
                DB::raw('SUM(CASE WHEN type = \'cod_collected\' THEN amount ELSE 0 END) as total_cod_collected'),
                DB::raw('SUM(CASE WHEN type = \'settlement\' THEN ABS(amount) ELSE 0 END) as total_settlement'),
                DB::raw('COUNT(CASE WHEN type = \'cod_collected\' THEN 1 END) as jumlah_paket_cod'),
            ])
            ->groupBy('courier_id');

        // Branch scope
        if (!$user->canAccessAllBranches() && $user->branch_id) {
            $query->whereHas('courier', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
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

        // Having clause for balance > 0
        $query->havingRaw('SUM(CASE WHEN type = \'cod_collected\' THEN amount ELSE 0 END) - SUM(CASE WHEN type = \'settlement\' THEN ABS(amount) ELSE 0 END) > 0');

        return $query;
    }

    /**
     * Get courier balance with current balance
     */
    public function getCourierBalance(int $courierId): float
    {
        return CourierCurrentBalance::getBalance($courierId);
    }

    /**
     * Get courier balance details
     */
    public function getCourierBalanceDetails(int $courierId, array $filters = []): array
    {
        $query = CourierBalance::where('courier_id', $courierId)
            ->with(['shipment:id,resi_number,created_at']);

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $currentBalance = $this->getCourierBalance($courierId);

        return [
            'current_balance' => $currentBalance,
            'transactions' => $transactions,
            'summary' => [
                'total_cod_collected' => $transactions->where('type', 'cod_collected')->sum('amount'),
                'total_settlement' => abs($transactions->where('type', 'settlement')->sum('amount')),
            ],
        ];
    }
}

