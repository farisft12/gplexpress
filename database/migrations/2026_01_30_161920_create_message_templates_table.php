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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // e.g., 'paket_dikirim', 'kurir_otw', 'paket_terkirim'
            $table->string('name'); // Human-readable name
            $table->text('description')->nullable();
            $table->enum('channel', ['whatsapp', 'email', 'sms']);
            $table->text('content'); // Template content with {{variables}}
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete(); // null = global template
            $table->boolean('is_active')->default(true);
            $table->json('variables')->nullable(); // Available variables for this template
            $table->timestamps();
            
            // Unique constraint: code + channel + branch_id (allow same code for different channels)
            $table->unique(['code', 'channel', 'branch_id'], 'message_templates_code_channel_branch_unique');
            $table->index(['channel', 'is_active']);
            $table->index(['branch_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
