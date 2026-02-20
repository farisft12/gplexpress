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
            // Add indexes for report queries
            $table->index(['type', 'created_at']);
            $table->index(['courier_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'status', 'created_at']);
            $table->index(['cod_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex(['type', 'created_at']);
            $table->dropIndex(['courier_id', 'created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['type', 'status', 'created_at']);
            $table->dropIndex(['cod_status', 'created_at']);
        });
    }
};
