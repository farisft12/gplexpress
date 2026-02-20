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
        Schema::create('courier_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $table->enum('type', ['cod_collected', 'settlement'])->index();
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes (type already indexed above, courier_id has FK index)
            $table->index(['courier_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_balances');
    }
};

