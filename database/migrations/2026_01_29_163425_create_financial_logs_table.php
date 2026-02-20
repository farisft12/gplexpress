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
        Schema::create('financial_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['COD_COLLECTED', 'SETTLEMENT'])->index();
            $table->unsignedBigInteger('reference_id')->nullable(); // ID dari shipment atau settlement
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->foreignId('actor_id')->constrained('users')->onDelete('restrict'); // Who performed the action
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional data for audit
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['type', 'created_at']);
            $table->index(['courier_id', 'created_at']);
            $table->index('actor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_logs');
    }
};
