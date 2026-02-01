<?php

namespace App\Services\Settlement;

use App\Models\CourierSettlement;
use App\Models\User;
use App\Models\CourierCurrentBalance;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    /**
     * Create settlement
     */
    public function create(array $data, User $user): CourierSettlement
    {
        $courier = User::findOrFail($data['courier_id']);

        // Verify courier is from same branch (unless super admin)
        if ($user->role !== 'super_admin' && $user->branch_id) {
            if ($courier->branch_id !== $user->branch_id) {
                throw new \Exception('Kurir tidak berada di cabang yang sama.');
            }
        }

        // Determine branch_id
        $branchId = $courier->branch_id;
        if (!$branchId && $user->branch_id) {
            $branchId = $user->branch_id;
        }

        return DB::transaction(function () use ($data, $branchId) {
            // Create settlement (observer will create financial log)
            return CourierSettlement::create([
                'branch_id' => $branchId,
                'courier_id' => $data['courier_id'],
                'amount' => $data['amount'],
                'method' => $data['method'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Confirm settlement
     */
    public function confirm(CourierSettlement $settlement, User $user): CourierSettlement
    {
        if ($settlement->status !== 'pending') {
            throw new \Exception('Settlement sudah dikonfirmasi atau dibatalkan.');
        }

        // Check balance
        $currentBalance = CourierCurrentBalance::getBalance($settlement->courier_id);
        if ($settlement->amount > $currentBalance) {
            throw new \Exception('Jumlah settlement melebihi saldo kurir.');
        }

        return DB::transaction(function () use ($settlement, $user) {
            // Update settlement (observer will update balance)
            $settlement->update([
                'status' => 'confirmed',
                'confirmed_by' => $user->id,
                'confirmed_at' => now(),
            ]);

            return $settlement->fresh();
        });
    }

    /**
     * Get settlements query
     */
    public function getSettlementsQuery(User $user, array $filters = [])
    {
        $query = CourierSettlement::with(['courier', 'confirmedBy'])->latest();

        // Branch scope
        if ($user->isManager() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['courier_id'])) {
            $query->where('courier_id', $filters['courier_id']);
        }

        return $query;
    }
}

