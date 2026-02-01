<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Composite index for branch filtering with status and date
            $table->index(['branch_id', 'status', 'created_at'], 'idx_shipments_branch_status_created');
            
            // Composite index for courier filtering with status and date
            $table->index(['courier_id', 'status', 'created_at'], 'idx_shipments_courier_status_created');
        });

        Schema::table('courier_balances', function (Blueprint $table) {
            // Composite index for courier filtering with type and date
            $table->index(['courier_id', 'type', 'created_at'], 'idx_balances_courier_type_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex('idx_shipments_branch_status_created');
            $table->dropIndex('idx_shipments_courier_status_created');
        });

        Schema::table('courier_balances', function (Blueprint $table) {
            $table->dropIndex('idx_balances_courier_type_created');
        });
    }
};
