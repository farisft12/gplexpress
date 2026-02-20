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
        
<<<<<<< HEAD
        // Manager & Admin: same branch (origin) OR destination branch (for in-transit packages)
        // If branch_id is null, allow access (for legacy data or unassigned users)
        if ($user->isManager() || $user->isAdmin()) {
            if ($user->branch_id) {
                // Check origin branch access
                if ($shipment->branch_id === $user->branch_id) {
                    return true;
                }
                
                // Check destination branch access (for packages in transit or arrived)
                if ($shipment->destination_branch_id === $user->branch_id) {
                    $allowedStatuses = ['dalam_pengiriman', 'sampai_di_cabang_tujuan', 'diterima'];
                    return in_array($shipment->status, $allowedStatuses);
                }
                
                return false;
=======
        // Manager & Admin: same branch (if branch_id is set)
        // If branch_id is null, allow access (for legacy data or unassigned users)
        if ($user->isManager() || $user->isAdmin()) {
            // If user has branch_id, check if it matches shipment's branch_id
            if ($user->branch_id) {
            return $shipment->branch_id === $user->branch_id;
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD
        // Owner can update all shipments regardless of status or branch
=======
        // Owner can update all
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
        if ($user->isOwner()) {
            return true;
        }
        
<<<<<<< HEAD
        // Admin: can update if status is pickup
        if ($user->isAdmin()) {
            // Status must be pickup to allow editing
            if ($shipment->status !== 'pickup') {
                return false;
            }
            
            // If user has branch_id, check if it matches shipment's branch_id
            if ($user->branch_id) {
                return $shipment->branch_id === $user->branch_id;
            }
            
            // If user has no branch_id, allow access (consistent with view)
            return true;
        }
        
        // Manager: can update if status is pickup and from their branch
        if ($user->isManager()) {
            // Status must be pickup to allow editing
            if ($shipment->status !== 'pickup') {
                return false;
            }
            
            // Must be from their branch
            if ($user->branch_id) {
                return $shipment->branch_id === $user->branch_id;
            }
            
            return false;
        }
        
        return false;
    }

    /**
     * Determine if user can update shipment status.
     */
    public function updateStatus(User $user, Shipment $shipment): bool
    {
        // Owner can update status of all shipments
        if ($user->isOwner()) {
            return true;
        }
        
        // Admin & Manager: can update status if same branch (origin) OR destination branch (for in-transit packages)
        if ($user->isAdmin() || $user->isManager()) {
            if ($user->branch_id) {
                // Check origin branch access - can always update if from their branch
                if ($shipment->branch_id === $user->branch_id) {
                    return true;
                }
                
                // Check destination branch access (for packages in transit or arrived)
                if ($shipment->destination_branch_id === $user->branch_id) {
                    // Allow updating if status is in transit or arrived
                    $allowedStatuses = ['dalam_pengiriman', 'sampai_di_cabang_tujuan', 'diterima'];
                    return in_array($shipment->status, $allowedStatuses);
                }
                
                return false;
            }
            // If user has no branch_id, allow access (consistent with view)
            return true;
        }
        
        // Kurir: can update status of their own shipments
        if ($user->isKurir()) {
            return $shipment->courier_id === $user->id;
=======
        // Admin: same branch and status is pickup
        if ($user->isAdmin() && $user->branch_id) {
            return $shipment->branch_id === $user->branch_id && $shipment->status === 'pickup';
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD

    /**
     * Determine if user can send notification for the shipment.
     */
    public function sendNotification(User $user, Shipment $shipment): bool
    {
        // Log for debugging
        \Log::info('ShipmentPolicy::sendNotification: Checking authorization', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_branch_id' => $user->branch_id,
            'shipment_id' => $shipment->id,
            'shipment_resi' => $shipment->resi_number,
            'shipment_branch_id' => $shipment->branch_id,
            'shipment_destination_branch_id' => $shipment->destination_branch_id,
            'shipment_status' => $shipment->status,
        ]);
        
        // Only allow if status is 'sampai_di_cabang_tujuan'
        if ($shipment->status !== 'sampai_di_cabang_tujuan') {
            \Log::warning('ShipmentPolicy::sendNotification: Status not allowed', [
                'shipment_status' => $shipment->status,
                'required_status' => 'sampai_di_cabang_tujuan',
            ]);
            return false;
        }
        
        // Owner can send notification for all shipments
        if ($user->isOwner()) {
            \Log::info('ShipmentPolicy::sendNotification: Authorized (Owner)');
            return true;
        }
        
        // Admin & Manager: can send if origin branch OR destination branch
        if ($user->isAdmin() || $user->isManager()) {
            if ($user->branch_id) {
                // Check origin branch access
                if ($shipment->branch_id === $user->branch_id) {
                    \Log::info('ShipmentPolicy::sendNotification: Authorized (Origin Branch)');
                    return true;
                }
                
                // Check destination branch access (for packages that have arrived)
                if ($shipment->destination_branch_id === $user->branch_id) {
                    \Log::info('ShipmentPolicy::sendNotification: Authorized (Destination Branch)');
                    return true;
                }
                
                \Log::warning('ShipmentPolicy::sendNotification: Not authorized (Branch mismatch)', [
                    'user_branch_id' => $user->branch_id,
                    'shipment_branch_id' => $shipment->branch_id,
                    'shipment_destination_branch_id' => $shipment->destination_branch_id,
                ]);
                return false;
            }
            // If user has no branch_id, allow access
            \Log::info('ShipmentPolicy::sendNotification: Authorized (No branch_id)');
            return true;
        }
        
        \Log::warning('ShipmentPolicy::sendNotification: Not authorized (Role not allowed)', [
            'user_role' => $user->role,
        ]);
        return false;
    }
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
}
