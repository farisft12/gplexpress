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
        Schema::create('courier_manifest_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_id')->constrained('courier_manifests')->onDelete('cascade');
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->unique(['manifest_id', 'shipment_id']);
            $table->index('shipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_manifest_shipments');
    }
};







