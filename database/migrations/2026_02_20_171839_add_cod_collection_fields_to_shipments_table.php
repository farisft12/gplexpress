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
            $table->foreignId('cod_collected_by')->nullable()->after('cod_status')->constrained('users')->nullOnDelete();
            $table->timestamp('cod_collected_at')->nullable()->after('cod_collected_by');
            $table->timestamp('cod_payment_received_at')->nullable()->after('cod_collected_at');
            $table->text('cod_collection_notes')->nullable()->after('cod_payment_received_at');
            
            $table->index('cod_collected_by');
            $table->index('cod_collected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['cod_collected_by']);
            $table->dropIndex(['cod_collected_by']);
            $table->dropIndex(['cod_collected_at']);
            $table->dropColumn(['cod_collected_by', 'cod_collected_at', 'cod_payment_received_at', 'cod_collection_notes']);
        });
    }
};
