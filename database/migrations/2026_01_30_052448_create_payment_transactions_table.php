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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('transaction_id')->unique()->index(); // Midtrans transaction ID
            $table->string('order_id')->index(); // Our order ID (GPL-{resi}-{timestamp})
            $table->enum('status', ['pending', 'settlement', 'expire', 'deny', 'cancel'])->default('pending')->index();
            $table->enum('payment_method', ['cash', 'qris'])->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->string('fraud_status')->nullable();
            $table->text('notification_data')->nullable(); // Store full notification JSON
            $table->string('callback_ip')->nullable(); // Store callback IP for security
            $table->boolean('is_processed')->default(false)->index(); // Prevent double processing
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['shipment_id', 'status']);
            $table->index(['status', 'is_processed']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
