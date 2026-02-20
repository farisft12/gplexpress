<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserManagementService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Create new user
     */
    public function create(array $data, User $currentUser): User
    {
        // Normalize phone if provided
        if (!empty($data['phone'])) {
            $data['phone'] = $this->userService->normalizePhone($data['phone']);
        }

        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            $user = User::create($data);
            
            // Clear cache
            $this->userService->clearUserCache();
            
            return $user;
        });
    }

    /**
     * Update user
     */
    public function update(User $user, array $data, User $currentUser): User
    {
        // Normalize phone if provided
        if (!empty($data['phone'])) {
            $data['phone'] = $this->userService->normalizePhone($data['phone']);
        }

        return DB::transaction(function () use ($user, $data) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);
            
            // Clear cache
            $this->userService->clearUserCache();
            
            return $user->fresh();
        });
    }

    /**
     * Delete user
     */
    public function delete(User $user, User $currentUser): void
    {
        if ($user->id === $currentUser->id) {
            throw new \Exception('Anda tidak dapat menghapus akun sendiri.');
        }

        DB::transaction(function () use ($user) {
            $user->delete();
            
            // Clear cache
            $this->userService->clearUserCache();
        });
    }

    /**
     * Get branches for create/edit form
     */
    public function getBranchesForForm(): \Illuminate\Support\Collection
    {
        return Cache::remember('active_branches', 3600, function () {
            return Branch::where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();
        });
    }
}

