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
        Schema::create('shipment_sla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('sla_id')->constrained('sla_definitions')->onDelete('restrict');
            $table->timestamp('deadline_at'); // Calculated deadline
            $table->enum('status', ['on_time', 'late', 'failed'])->default('on_time')->index();
            $table->timestamp('actual_delivered_at')->nullable(); // When actually delivered
            $table->integer('hours_difference')->nullable(); // Difference from deadline (positive = late)
            $table->timestamps();
            
            $table->index(['shipment_id', 'status']);
            $table->index('deadline_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_sla');
    }
};
