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
            $table->timestamp('out_for_delivery_at')->nullable()->after('assigned_at');
            $table->timestamp('failed_at')->nullable()->after('delivered_at');
            $table->timestamp('eta_at')->nullable()->after('assigned_at'); // Estimated Time of Arrival
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['out_for_delivery_at', 'failed_at', 'eta_at']);
        });
    }
};
