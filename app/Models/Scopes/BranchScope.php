<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BranchScope implements Scope
{
    /**
     * Cache for column existence checks
     */
    protected static array $columnExistsCache = [];

    /**
     * Check if branch_id column exists in the table
     */
    protected function hasBranchIdColumn(string $table): bool
    {
        if (!isset(self::$columnExistsCache[$table])) {
            self::$columnExistsCache[$table] = Schema::hasColumn($table, 'branch_id');
        }
        
        return self::$columnExistsCache[$table];
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();
        
        // If branch_id column doesn't exist yet, skip scoping
        if (!$this->hasBranchIdColumn($table)) {
            return;
        }
        
        $user = Auth::user();
        
<<<<<<< HEAD
        // Owner can see all branches (no scope applied)
        if (!$user || $user->isOwner()) {
=======
        // Super Admin can see all branches
        if (!$user || $user->role === 'super_admin') {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
            return;
        }
        
        // Manager Cabang & Admin Cabang: scope to their branch
        if (in_array($user->role, ['manager_cabang', 'admin_cabang']) && $user->branch_id) {
            $builder->where($table . '.branch_id', $user->branch_id);
            return;
        }
        
        // Courier Cabang: scope to their own records only
        if ($user->role === 'courier_cabang') {
            // For shipments, courier can only see their assigned shipments
            if ($table === 'shipments') {
                $builder->where('courier_id', $user->id);
            } else {
                // For other tables, scope by branch
                if ($user->branch_id) {
                    $builder->where($table . '.branch_id', $user->branch_id);
                }
            }
            return;
        }
        
        // Legacy roles: admin, manager, kurir - scope by branch if exists
        if (in_array($user->role, ['admin', 'manager', 'kurir']) && $user->branch_id) {
            if ($table === 'shipments' && $user->role === 'kurir') {
                $builder->where('courier_id', $user->id);
            } else {
                $builder->where($table . '.branch_id', $user->branch_id);
            }
        }
    }
}
