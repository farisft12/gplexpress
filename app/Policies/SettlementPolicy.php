<?php

namespace App\Policies;

use App\Models\CourierSettlement;
use App\Models\User;

class SettlementPolicy
{
    /**
     * Determine if user can view any settlements.
     */
    public function viewAny(User $user): bool
    {
        // Owner can view all
        if ($user->isOwner()) {
            return true;
        }
        
        // Manager can view their branch settlements
        if ($user->isManager() && $user->branch_id) {
            return true;
        }
        
        // Admin can view their branch settlements (read-only)
        if ($user->isAdmin() && $user->branch_id) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine if user can view the settlement.
     */
    public function view(User $user, CourierSettlement $settlement): bool
    {
        // Owner can view all
        if ($user->isOwner()) {
            return true;
        }
        
        // Manager & Admin: same branch
        if (($user->isManager() || $user->isAdmin()) && $user->branch_id) {
            return $settlement->branch_id === $user->branch_id;
        }
        
        return false;
    }

    /**
     * Determine if user can create settlements.
     */
    public function create(User $user): bool
    {

        // Owner and Admin can create (draft)
        return $user->isOwner() || $user->isAdmin();

    }

    /**
     * Determine if user can update the settlement.
     */
    public function update(User $user, CourierSettlement $settlement): bool
    {

        // Owner can update any pending settlement
        if ($user->isOwner()) {
            return $settlement->status === 'pending';
        }
        

        // Admin can update if status is pending and same branch
        if ($user->isAdmin() && $user->branch_id) {
            return $settlement->branch_id === $user->branch_id && $settlement->status === 'pending';
        }
        
        return false;
    }

    /**
     * Determine if user can confirm the settlement.
     */
    public function confirm(User $user, CourierSettlement $settlement): bool
    {
        // Owner can confirm any
        if ($user->isOwner()) {
            return $settlement->status === 'pending';
        }
        
        // Manager can confirm their branch settlements
        if ($user->isManager() && $user->branch_id) {
            return $settlement->branch_id === $user->branch_id && $settlement->status === 'pending';
        }
        
        return false;
    }

    /**
     * Determine if user can delete the settlement.
     */
    public function delete(User $user, CourierSettlement $settlement): bool
    {
        // Only Owner can delete
        return $user->isOwner();
    }
}
