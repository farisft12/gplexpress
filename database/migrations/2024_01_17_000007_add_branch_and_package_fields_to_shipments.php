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
            $table->foreignId('origin_branch_id')->nullable()->after('resi_number')->constrained('branches')->nullOnDelete();
            $table->foreignId('destination_branch_id')->nullable()->after('origin_branch_id')->constrained('branches')->nullOnDelete();
            $table->string('package_type')->nullable()->after('destination_branch_id');
            $table->decimal('weight', 8, 2)->nullable()->after('package_type')->comment('Berat dalam kg');
            
            $table->index('origin_branch_id');
            $table->index('destination_branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['origin_branch_id']);
            $table->dropForeign(['destination_branch_id']);
            $table->dropIndex(['origin_branch_id']);
            $table->dropIndex(['destination_branch_id']);
            $table->dropColumn(['origin_branch_id', 'destination_branch_id', 'package_type', 'weight']);
        });
    }
};







