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
        Schema::create('pricing_tables', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama tarif (contoh: "Jakarta - Bandung", "Reguler", dll)
            $table->foreignId('origin_branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('destination_branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('base_price', 15, 2); // Harga dasar
            $table->decimal('cod_fee_percentage', 5, 2)->default(0); // Fee COD dalam persen
            $table->decimal('cod_fee_fixed', 15, 2)->default(0); // Fee COD tetap
            $table->enum('service_type', ['reguler', 'express', 'same_day'])->default('reguler');
            $table->integer('estimated_days')->default(1); // Estimasi hari pengiriman
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index(['origin_branch_id', 'destination_branch_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_tables');
    }
};







