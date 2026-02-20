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
            
            $table->index(['courier_id', 'period_type', 'period_date']);
            $table->index(['branch_id', 'period_type', 'period_date']);
        });
        
        // Add unique constraint separately
        try {
            DB::statement("
                ALTER TABLE courier_performance_snapshots 
                ADD CONSTRAINT courier_performance_snapshots_courier_id_period_type_period_date_unique 
                UNIQUE (courier_id, period_type, period_date)
            ");
        } catch (\Illuminate\Database\QueryException $e) {
            // If constraint already exists, that's okay
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
        Schema::dropIfExists('courier_performance_snapshots');
    }
};
