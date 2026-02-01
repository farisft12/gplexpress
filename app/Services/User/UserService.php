<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserService
{
    /**
     * Normalize phone number to standard format (62xxxxxxxxxx)
     */
    public function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62 (Indonesia country code)
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with country code, add 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Get users query with filters
     */
    public function getUsersQuery(User $currentUser, array $filters = [])
    {
        $query = User::with('branch:id,name,code');

        // Branch scope for manager (must see only their branch)
        if ($currentUser->isManager() && $currentUser->branch_id) {
            $query->where('branch_id', $currentUser->branch_id)
                  ->whereIn('role', ['admin', 'kurir']);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                // Optimize: use prefix match when possible
                if (strlen($search) >= 3) {
                    $q->where('name', 'like', $search . '%')
                      ->orWhere('email', 'like', $search . '%');
                } else {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                }
            });
        }

        // Role filter
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Branch filter (for owners)
        if (!empty($filters['branch_id']) && $currentUser->isOwner()) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query;
    }

    /**
     * Get branches for user management
     */
    public function getBranchesForUserManagement(User $user)
    {
        if ($user->isOwner()) {
            return Cache::remember('active_branches', 3600, function () {
                return Branch::where('status', 'active')
                    ->select('id', 'name', 'code')
                    ->orderBy('name')
                    ->get();
            });
        }

        return collect();
    }

    /**
     * Clear user-related cache
     */
    public function clearUserCache(): void
    {
        Cache::forget('active_managers');
        Cache::forget('active_couriers');
        Cache::forget('active_branches');
    }
}

