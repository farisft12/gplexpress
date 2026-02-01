<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Determine if user can view any shipments.
     */
    public function viewAny(User $user): bool
    {
        // Owner can view all
        if ($user->isOwner()) {
            return true;
        }
        
        // Manager & Admin can view their branch shipments (even if branch_id is null, allow access)
        if ($user->isManager() || $user->isAdmin()) {
            return true;
        }
        
        // Kurir can view their own shipments
        if ($user->isKurir()) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine if user can view the shipment.
     */
    public function view(User $user, Shipment $shipment): bool
    {
        // Owner can view all
        if ($user->isOwner()) {
            return true;
        }
        
        // Manager & Admin: same branch (if branch_id is set)
        // If branch_id is null, allow access (for legacy data or unassigned users)
        if ($user->isManager() || $user->isAdmin()) {
            // If user has branch_id, check if it matches shipment's branch_id
            if ($user->branch_id) {
            return $shipment->branch_id === $user->branch_id;
            }
            // If user has no branch_id, allow access (consistent with viewAny)
            return true;
        }
        
        // Kurir: own shipments only
        if ($user->isKurir()) {
            return $shipment->courier_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine if user can create shipments.
     */
    public function create(User $user): bool
    {
        // Owner & Admin can create
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can update the shipment.
     */
    public function update(User $user, Shipment $shipment): bool
    {
        // Owner can update all
        if ($user->isOwner()) {
            return true;
        }
        
        // Admin: same branch and status is pickup
        if ($user->isAdmin() && $user->branch_id) {
            return $shipment->branch_id === $user->branch_id && $shipment->status === 'pickup';
        }
        
        return false;
    }

    /**
     * Determine if user can delete the shipment.
     */
    public function delete(User $user, Shipment $shipment): bool
    {
        // Only Owner can delete
        return $user->isOwner();
    }

    /**
     * Determine if user can assign courier (general permission).
     */
    public function assign(User $user, ?Shipment $shipment = null): bool
    {
        // Owner & Admin can assign
        if (!$user->isOwner() && !$user->isAdmin()) {
            return false;
        }
        
        // If checking specific shipment, verify branch match
        if ($shipment) {
            return $shipment->branch_id === $user->branch_id || $user->isOwner();
        }
        
        // General permission check (for assignForm)
        return true;
    }
}
