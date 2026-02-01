<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'reference_id',
        'courier_id',
        'amount',
        'actor_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get courier
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Get actor (who performed the action)
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Prevent updates and deletes (immutable)
     */
    public static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw new \Exception('Financial logs are immutable and cannot be updated.');
        });

        static::deleting(function ($model) {
            throw new \Exception('Financial logs are immutable and cannot be deleted.');
        });
    }
}
