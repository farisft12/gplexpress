<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Zone;

class ZonePolicy
{
    /**
     * Determine if user can view any zones.
     */
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if user can view the zone.
     */
    public function view(User $user, Zone $zone): bool
    {
        if ($user->isOwner()) {
            return true;
        }
        
        return ($user->isManager() || $user->isAdmin()) && $user->branch_id === $zone->branch_id;
    }

    /**
     * Determine if user can create zones.
     */
    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can update the zone.
     */
    public function update(User $user, Zone $zone): bool
    {
        if ($user->isOwner()) {
            return true;
        }
        
        return $user->isAdmin() && $user->branch_id === $zone->branch_id;
    }

    /**
     * Determine if user can delete the zone.
     */
    public function delete(User $user, Zone $zone): bool
    {
        if ($user->isOwner()) {
            return true;
        }
        
        return $user->isAdmin() && $user->branch_id === $zone->branch_id;
    }
}
