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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Key name/description
            $table->string('key', 64)->unique(); // API key
            $table->string('secret', 64)->nullable(); // Optional secret for HMAC
            $table->boolean('is_active')->default(true);
            $table->integer('rate_limit')->default(100); // Requests per minute
            $table->json('allowed_ips')->nullable(); // IP whitelist (optional)
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['key', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
