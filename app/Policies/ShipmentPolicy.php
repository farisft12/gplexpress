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
        // Owner cannot access shipments page
        if ($user->isOwner()) {
            return false;
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

            }
            // If user has no branch_id, allow access (consistent with viewAny)
            return true;
        }
        
        // Kurir: own shipments only
        if ($user->isKurir()) {
            // Can view if assigned as courier or as COD collector
            return $shipment->courier_id === $user->id || $shipment->cod_collected_by === $user->id;
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
        // Owner can update all shipments regardless of status or branch
        if ($user->isOwner()) {
            return true;
        }
        
        // Admin: can update ONLY outgoing packages (from their branch)
        // Incoming packages CANNOT be edited (data), only status can be updated
        if ($user->isAdmin()) {
            // Don't allow editing if already delivered
            if ($shipment->status === 'diterima') {
                return false;
            }
            
            // Only allow editing outgoing packages (from origin branch)
            // Incoming packages (destination branch) cannot edit data
            if ($user->branch_id) {
                // Check origin branch access - can edit if from their branch (outgoing packages only)
                // Check both branch_id (legacy) and origin_branch_id (new)
                if (($shipment->branch_id === $user->branch_id) || 
                    ($shipment->origin_branch_id === $user->branch_id)) {
                    return true;
                }
                
                // Block editing for incoming packages (destination branch)
                // They can only update status, not data
                return false;
            }
            
            // If user has no branch_id, allow access (consistent with view)
            return true;
        }
        
        // Manager: can update ONLY outgoing packages (from their branch)
        // Incoming packages CANNOT be edited (data), only status can be updated
        if ($user->isManager()) {
            // Don't allow editing if already delivered
            if ($shipment->status === 'diterima') {
                return false;
            }
            
            // Only allow editing outgoing packages (from origin branch)
            // Incoming packages (destination branch) cannot edit data
            if ($user->branch_id) {
                // Check origin branch access - can edit if from their branch (outgoing packages only)
                // Check both branch_id (legacy) and origin_branch_id (new)
                if (($shipment->branch_id === $user->branch_id) || 
                    ($shipment->origin_branch_id === $user->branch_id)) {
                    return true;
                }
                
                // Block editing for incoming packages (destination branch)
                // They can only update status, not data
                return false;
            }
            
            return false;
        }
        
        \Log::warning('ShipmentPolicy::update: Denied - role not allowed', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'shipment_id' => $shipment->id,
        ]);
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

    /**
     * Determine if the user can assign COD to courier
     */
    public function assignCod(User $user, Shipment $shipment): bool
    {
        // Owner can assign all COD shipments
        if ($user->isOwner()) {
            return true;
        }

        // Admin/Manager from destination branch can assign COD
        if (($user->isAdmin() || $user->isManager()) && $user->branch_id) {
            return $shipment->destination_branch_id === $user->branch_id
                && $shipment->status === 'sampai_di_cabang_tujuan'
                && $shipment->type === 'cod'
                && $shipment->cod_status === 'belum_lunas';
        }

        return false;
    }

    /**
     * Determine if the user can collect COD (input payment)
     */
    public function collectCod(User $user, Shipment $shipment): bool
    {
        // Admin/Manager from destination branch can collect COD
        if (($user->isAdmin() || $user->isManager()) && $user->branch_id) {
            return $shipment->destination_branch_id === $user->branch_id
                && $shipment->cod_status === 'belum_lunas';
        }
        
        // Only destination courier can collect COD (not origin courier)
        if ($user->isKurir()) {
            // Kurir pengantar (courier_id) TIDAK BISA menagih COD
            // Hanya kurir tujuan (destination_courier_id) yang bisa menagih COD
            return $shipment->destination_courier_id === $user->id
                && $shipment->cod_status === 'belum_lunas';
        }

        return false;
    }

}
