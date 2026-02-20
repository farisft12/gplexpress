<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if user can view any users
     */
    public function viewAny(User $user): bool
    {
        // Owner can view all users
        if ($user->isOwner()) {
            return true;
        }
        
<<<<<<< HEAD
        // Manager and Admin can view users in their branch
        if (($user->isManager() || $user->isAdmin()) && $user->branch_id) {
=======
        // Manager can view users in their branch (for viewing admin and kurir)
        if ($user->isManager() && $user->branch_id) {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
            return true;
        }
        
        return false;
    }

    /**
     * Determine if user can view a user
     */
    public function view(User $user, User $model): bool
    {
        // Owner can view all
        if ($user->isOwner()) {
            return true;
        }
        
<<<<<<< HEAD
        // Manager and Admin can view users in their branch
        if (($user->isManager() || $user->isAdmin()) && $user->branch_id && $model->branch_id === $user->branch_id) {
=======
        // Manager can view users in their branch
        if ($user->isManager() && $user->branch_id && $model->branch_id === $user->branch_id) {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
            return true;
        }
        
        // User can view their own profile
        if ($user->id === $model->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine if user can create users
     */
    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can update a user
     */
    public function update(User $user, User $model): bool
    {
        return $user->isOwner() || $user->id === $model->id;
    }

    /**
     * Determine if user can delete a user
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isOwner();
    }
}


