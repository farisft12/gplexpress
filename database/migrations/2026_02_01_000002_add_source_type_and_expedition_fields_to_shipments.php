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
            $table->string('source_type')->default('perorangan')->after('resi_number');
            $table->foreignId('expedition_id')->nullable()->after('source_type')->constrained('expeditions')->nullOnDelete();
            $table->string('external_resi_number')->nullable()->after('expedition_id');
            $table->decimal('cod_shipping_cost', 15, 2)->nullable()->after('cod_amount');
            $table->decimal('cod_admin_fee', 15, 2)->nullable()->after('cod_shipping_cost');
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->string('sender_phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['expedition_id']);
            $table->dropColumn([
                'source_type',
                'expedition_id',
                'external_resi_number',
                'cod_shipping_cost',
                'cod_admin_fee',
            ]);
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->string('sender_phone')->nullable(false)->change();
        });
    }
};
