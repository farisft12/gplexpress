<?php

namespace App\Policies;

use App\Models\PricingTable;
use App\Models\User;

class PricingTablePolicy
{
    /**
     * Determine if user can view any pricing tables.
     */
    public function viewAny(User $user): bool
    {
        // Owner, Admin, and Manager can view pricing tables
        return $user->isOwner() || $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine if user can view the pricing table.
     */
    public function view(User $user, PricingTable $pricingTable): bool
    {
        // Owner, Admin, and Manager can view pricing tables
        return $user->isOwner() || $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine if user can create pricing tables.
     */
    public function create(User $user): bool
    {
        // Owner and Admin can create pricing tables
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can update the pricing table.
     */
    public function update(User $user, PricingTable $pricingTable): bool
    {
        // Owner can update all pricing tables
        if ($user->isOwner()) {
            return true;
        }
        
        // Admin can update pricing tables for their branch
        if ($user->isAdmin() && $user->branch_id) {
            return $pricingTable->origin_branch_id === $user->branch_id || 
                   $pricingTable->destination_branch_id === $user->branch_id;
        }
        
        return false;
    }

    /**
     * Determine if user can delete the pricing table.
     */
    public function delete(User $user, PricingTable $pricingTable): bool
    {
        // Owner can delete all pricing tables
        if ($user->isOwner()) {
            return true;
        }
        
        // Admin can delete pricing tables for their branch
        if ($user->isAdmin() && $user->branch_id) {
            return $pricingTable->origin_branch_id === $user->branch_id || 
                   $pricingTable->destination_branch_id === $user->branch_id;
        }
        
        return false;
    }
}
