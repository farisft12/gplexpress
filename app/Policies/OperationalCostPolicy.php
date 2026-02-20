<?php

namespace App\Policies;

use App\Models\OperationalCost;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OperationalCostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OperationalCost $operationalCost): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OperationalCost $operationalCost): bool
    {
        if ($user->isOwner()) {
            return true;
        }
        
        if ($user->isAdmin() && $user->branch_id) {
            return $operationalCost->branch_id === $user->branch_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OperationalCost $operationalCost): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OperationalCost $operationalCost): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OperationalCost $operationalCost): bool
    {
        return false;
    }
}
