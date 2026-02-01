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
        Schema::create('courier_current_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->unique()->constrained('users')->onDelete('cascade');
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->timestamp('updated_at');
            
            // Index
            $table->index('current_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_current_balances');
    }
};
