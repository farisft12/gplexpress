<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create default branch if none exists
        $defaultBranch = DB::table('branches')->first();
        
        if (!$defaultBranch) {
            $defaultBranchId = DB::table('branches')->insertGetId([
                'code' => 'CAB-000001',
                'name' => 'Cabang Pusat',
                'city' => 'Jakarta',
                'address' => 'Alamat Cabang Pusat',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $defaultBranchId = $defaultBranch->id;
        }

        // Backfill branch_id for users (non-super_admin must have branch)
        DB::table('users')
            ->whereNull('branch_id')
            ->where('role', '!=', 'super_admin')
            ->update(['branch_id' => $defaultBranchId]);

        // Backfill branch_id for shipments (use origin_branch_id or default)
        DB::statement("
            UPDATE shipments 
            SET branch_id = COALESCE(origin_branch_id, {$defaultBranchId})
            WHERE branch_id IS NULL
        ");

        // Backfill branch_id for courier_balances (from courier's branch or shipment's branch)
        DB::statement("
            UPDATE courier_balances cb
            SET branch_id = COALESCE(
                (SELECT u.branch_id FROM users u WHERE u.id = cb.courier_id),
                (SELECT s.branch_id FROM shipments s WHERE s.id = cb.shipment_id),
                {$defaultBranchId}
            )
            WHERE cb.branch_id IS NULL
        ");

        // Backfill branch_id for settlements (from courier's branch)
        DB::statement("
            UPDATE courier_settlements cs
            SET branch_id = COALESCE(
                (SELECT u.branch_id FROM users u WHERE u.id = cs.courier_id),
                {$defaultBranchId}
            )
            WHERE cs.branch_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot safely reverse backfill
        // Data will remain with branch_id
    }
};
