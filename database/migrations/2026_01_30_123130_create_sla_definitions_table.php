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
        Schema::create('sla_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // same-day, next-day, regular
            $table->string('name'); // Same Day, Next Day, Regular
            $table->integer('deadline_hours'); // Hours from pickup to delivery deadline
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_definitions');
    }
};
