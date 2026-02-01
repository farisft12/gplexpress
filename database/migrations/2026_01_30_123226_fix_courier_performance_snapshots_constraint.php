<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop orphaned constraint/index if it exists
        // Try multiple approaches to clean up
        DB::statement("
            DO \$\$
            BEGIN
                -- Drop constraint if table exists
                IF EXISTS (SELECT 1 FROM pg_class WHERE relname = 'courier_performance_snapshots') THEN
                    ALTER TABLE courier_performance_snapshots 
                    DROP CONSTRAINT IF EXISTS courier_performance_snapshots_courier_id_period_type_period_date_unique CASCADE;
                END IF;
                
                -- Drop index if exists (sometimes constraint creates index)
                DROP INDEX IF EXISTS courier_performance_snapshots_courier_id_period_type_period_date_unique;
                
                -- Also try to drop from pg_constraint directly
                DELETE FROM pg_constraint 
                WHERE conname = 'courier_performance_snapshots_courier_id_period_type_period_date_unique';
            END
            \$\$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse
    }
};
