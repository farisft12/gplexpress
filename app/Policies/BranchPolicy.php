<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    /**
     * Determine if user can view any branches.
     */
    public function viewAny(User $user): bool
    {
        // Owner can view all branches
        if ($user->isOwner()) {
            return true;
        }
        
        // Manager & Admin can view branches (for their own branch context)
        if ($user->isManager() || $user->isAdmin()) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine if user can view the branch.
     */
    public function view(User $user, Branch $branch): bool
    {
        // Owner can view all
        if ($user->isOwner()) {
            return true;
        }
        
        // Manager & Admin can view their own branch
        if (($user->isManager() || $user->isAdmin()) && $user->branch_id) {
            return $branch->id === $user->branch_id;
        }
        
        return false;
    }

    /**
     * Determine if user can create branches.
     */
    public function create(User $user): bool
    {
        // Only Owner can create branches
        return $user->isOwner();
    }

    /**
     * Determine if user can update the branch.
     */
    public function update(User $user, Branch $branch): bool
    {
        // Only Owner can update branches
        return $user->isOwner();
    }

    /**
     * Determine if user can delete the branch.
     */
    public function delete(User $user, Branch $branch): bool
    {
        // Only Owner can delete branches
        return $user->isOwner();
    }
}
