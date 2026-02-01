<?php

namespace App\Observers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Log user creation
        try {
            AuditLog::log(
                'user_created',
                'system',
                auth()->id(),
                'User',
                $user->id,
                null,
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                "User {$user->name} created"
            );
        } catch (\Exception $e) {
            Log::error("Failed to log user creation {$user->id}: {$e->getMessage()}");
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Log role changes
        if ($user->wasChanged('role')) {
            try {
                AuditLog::log(
                    'role_changed',
                    'security',
                    auth()->id(),
                    'User',
                    $user->id,
                    ['role' => $user->getOriginal('role')],
                    ['role' => $user->role],
                    "User {$user->name} role changed from {$user->getOriginal('role')} to {$user->role}"
                );
            } catch (\Exception $e) {
                Log::error("Failed to log role change for user {$user->id}: {$e->getMessage()}");
            }
        }

        // Log status changes
        if ($user->wasChanged('status')) {
            try {
                AuditLog::log(
                    'status_changed',
                    'system',
                    auth()->id(),
                    'User',
                    $user->id,
                    ['status' => $user->getOriginal('status')],
                    ['status' => $user->status],
                    "User {$user->name} status changed from {$user->getOriginal('status')} to {$user->status}"
                );
            } catch (\Exception $e) {
                Log::error("Failed to log status change for user {$user->id}: {$e->getMessage()}");
            }
        }

        // Clear cache when user data changes
        if ($user->wasChanged(['role', 'status', 'branch_id'])) {
            cache()->forget('active_managers');
            cache()->forget('active_couriers');
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Log user deletion
        try {
            AuditLog::log(
                'user_deleted',
                'system',
                auth()->id(),
                'User',
                $user->id,
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                null,
                "User {$user->name} deleted"
            );
        } catch (\Exception $e) {
            Log::error("Failed to log user deletion {$user->id}: {$e->getMessage()}");
        }

        // Clear cache
        cache()->forget('active_managers');
        cache()->forget('active_couriers');
    }
}
