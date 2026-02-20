<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'transaction_id',
        'order_id',
        'status',
        'payment_method',
        'gross_amount',
        'fraud_status',
        'notification_data',
        'callback_ip',
        'is_processed',
        'processed_at',
        'processing_error',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'is_processed' => 'boolean',
        'notification_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get shipment
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Check if transaction is already processed (idempotency check)
     */
    public function isAlreadyProcessed(): bool
    {
        return $this->is_processed && $this->status === 'settlement';
    }

    /**
     * Mark as processed
     */
    public function markAsProcessed(?string $error = null): void
    {
        $this->update([
            'is_processed' => true,
            'processed_at' => now(),
            'processing_error' => $error,
        ]);
    }
}
