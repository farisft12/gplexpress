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
            $table->foreignId('destination_courier_id')->nullable()->after('cod_collection_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('destination_courier_assigned_at')->nullable()->after('destination_courier_id');
            $table->timestamp('destination_courier_out_for_delivery_at')->nullable()->after('destination_courier_assigned_at');
            
            $table->index('destination_courier_id');
            $table->index('destination_courier_assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['destination_courier_id']);
            $table->dropIndex(['destination_courier_id']);
            $table->dropIndex(['destination_courier_assigned_at']);
            $table->dropColumn(['destination_courier_id', 'destination_courier_assigned_at', 'destination_courier_out_for_delivery_at']);
        });
    }
};
