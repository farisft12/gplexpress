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
        // Check if table already exists
        if (Schema::hasTable('courier_performance_snapshots')) {
            // Table exists, check if constraint exists
            $constraintCheck = DB::select("
                SELECT conname 
                FROM pg_constraint 
                WHERE conname = 'courier_performance_snapshots_courier_id_period_type_period_date_unique'
                AND conrelid = (SELECT oid FROM pg_class WHERE relname = 'courier_performance_snapshots')
            ");
            
            if (empty($constraintCheck)) {
                try {
                    DB::statement("
                        ALTER TABLE courier_performance_snapshots 
                        ADD CONSTRAINT courier_performance_snapshots_courier_id_period_type_period_date_unique 
                        UNIQUE (courier_id, period_type, period_date)
                    ");
                } catch (\Exception $e) {
                    // Constraint might already exist, ignore error
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
            return;
        }
        
        Schema::create('courier_performance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->index();
            $table->date('period_date'); // The date this snapshot represents
            $table->jsonb('metrics'); // Performance metrics as JSON
            $table->timestamp('generated_at');
            $table->timestamps();
            
            // Metrics structure:
            // {
            //   "total_paket": 100,
            //   "on_time_count": 85,
            //   "late_count": 10,
            //   "failed_count": 5,
            //   "on_time_percentage": 85.0,
            //   "late_percentage": 10.0,
            //   "failed_percentage": 5.0,
            //   "avg_delivery_duration_hours": 24.5,
            //   "cod_collection_accuracy": 98.5
            // }
            
            $table->index(['courier_id', 'period_type', 'period_date']);
            $table->index(['branch_id', 'period_type', 'period_date']);
        });
        
        // Add unique constraint separately - wrap in try-catch to handle if already exists
        try {
            DB::statement("
                ALTER TABLE courier_performance_snapshots 
                ADD CONSTRAINT courier_performance_snapshots_courier_id_period_type_period_date_unique 
                UNIQUE (courier_id, period_type, period_date)
            ");
        } catch (\Illuminate\Database\QueryException $e) {
            // If constraint already exists, that's okay - migration might have been partially run
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop unique constraint first if exists
        DB::statement("
            DO \$\$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM pg_constraint 
                    WHERE conname = 'courier_performance_snapshots_courier_id_period_type_period_date_unique'
                    AND conrelid = (SELECT oid FROM pg_class WHERE relname = 'courier_performance_snapshots')
                ) THEN
                    ALTER TABLE courier_performance_snapshots 
                    DROP CONSTRAINT courier_performance_snapshots_courier_id_period_type_period_date_unique;
                END IF;
            END
            \$\$;
        ");
        
        Schema::dropIfExists('courier_performance_snapshots');
    }
};
