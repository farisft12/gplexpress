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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('resi_number')->unique()->index();
            $table->enum('type', ['cod', 'non_cod'])->default('non_cod');
            $table->decimal('cod_amount', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'qris'])->nullable();
            $table->enum('cod_status', ['belum_lunas', 'lunas'])->default('belum_lunas');
            
            // Pengirim
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->text('sender_address');
            
            // Penerima
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->text('receiver_address');
            
            // Status
            $table->enum('status', [
                'menunggu',
                'dibawa_kurir',
                'terkirim',
                'gagal'
            ])->default('menunggu')->index();
            
            // Kurir
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            
            // Timestamps
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
            
            // Indexes (status already indexed above, courier_id has FK index)
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

