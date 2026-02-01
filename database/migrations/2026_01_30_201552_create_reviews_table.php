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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // User yang memberikan review
            $table->string('reviewer_name')->nullable(); // Nama reviewer (jika tidak login)
            $table->string('reviewer_email')->nullable(); // Email reviewer
            $table->integer('rating')->default(1); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            // Prevent duplicate reviews per shipment
            $table->unique(['shipment_id', 'user_id']);
            $table->index(['shipment_id', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
