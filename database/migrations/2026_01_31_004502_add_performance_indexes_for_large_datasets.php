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
        $connection = Schema::getConnection();
        
        // Helper function to check if index exists
        $indexExists = function($table, $indexName) use ($connection) {
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM pg_indexes WHERE schemaname = 'public' AND tablename = ? AND indexname = ?",
                [$table, $indexName]
            );
            return $result[0]->count > 0;
        };
        
        // Add index on users.branch_id for faster filtering
        // Note: Foreign key already creates an index, but we add composite indexes
        Schema::table('users', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('users', 'users_role_branch_id_index')) {
                $table->index(['role', 'branch_id'], 'users_role_branch_id_index');
            }
            
            if (!$indexExists('users', 'users_status_branch_id_index')) {
                $table->index(['status', 'branch_id'], 'users_status_branch_id_index');
            }
        });

        // Add indexes on shipments for better query performance
        Schema::table('shipments', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('shipments', 'shipments_delivered_at_index')) {
                $table->index('delivered_at', 'shipments_delivered_at_index');
            }
            
            if (!$indexExists('shipments', 'shipments_branch_id_status_index')) {
                $table->index(['branch_id', 'status'], 'shipments_branch_id_status_index');
            }
            
            if (!$indexExists('shipments', 'shipments_branch_id_created_at_index')) {
                $table->index(['branch_id', 'created_at'], 'shipments_branch_id_created_at_index');
            }
            
            if (!$indexExists('shipments', 'shipments_type_cod_status_delivered_at_index')) {
                $table->index(['type', 'cod_status', 'delivered_at'], 'shipments_type_cod_status_delivered_at_index');
            }
            
            if (!$indexExists('shipments', 'shipments_courier_id_status_index')) {
                $table->index(['courier_id', 'status'], 'shipments_courier_id_status_index');
            }
            
            if (!$indexExists('shipments', 'shipments_origin_branch_id_index')) {
                $table->index('origin_branch_id', 'shipments_origin_branch_id_index');
            }
            
            if (!$indexExists('shipments', 'shipments_destination_branch_id_index')) {
                $table->index('destination_branch_id', 'shipments_destination_branch_id_index');
            }
        });

        // Add indexes on settlements
        Schema::table('courier_settlements', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('courier_settlements', 'courier_settlements_branch_id_index')) {
                $table->index('branch_id', 'courier_settlements_branch_id_index');
            }
            
            if (!$indexExists('courier_settlements', 'courier_settlements_courier_id_index')) {
                $table->index('courier_id', 'courier_settlements_courier_id_index');
            }
            
            if (!$indexExists('courier_settlements', 'courier_settlements_status_index')) {
                $table->index('status', 'courier_settlements_status_index');
            }
            
            if (!$indexExists('courier_settlements', 'courier_settlements_branch_id_created_at_index')) {
                $table->index(['branch_id', 'created_at'], 'courier_settlements_branch_id_created_at_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropIndex('users_role_branch_id_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('users_status_branch_id_index');
            } catch (\Exception $e) {}
        });

        Schema::table('shipments', function (Blueprint $table) {
            try {
                $table->dropIndex('shipments_delivered_at_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('shipments_branch_id_status_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('shipments_branch_id_created_at_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('shipments_type_cod_status_delivered_at_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('shipments_courier_id_status_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('shipments_origin_branch_id_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('shipments_destination_branch_id_index');
            } catch (\Exception $e) {}
        });

        Schema::table('courier_settlements', function (Blueprint $table) {
            try {
                $table->dropIndex('courier_settlements_branch_id_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('courier_settlements_courier_id_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('courier_settlements_status_index');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('courier_settlements_branch_id_created_at_index');
            } catch (\Exception $e) {}
        });
    }
};
