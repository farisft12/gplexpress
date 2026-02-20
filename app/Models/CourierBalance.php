<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\BranchScope;

class CourierBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'courier_id',
        'shipment_id',
        'type',
        'amount',
        'notes',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope);
    }

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get courier
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Get shipment
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get current balance for courier
     */
    public static function getBalance(int $courierId): float
    {
        return self::where('courier_id', $courierId)
            ->where('type', 'cod_collected')
            ->sum('amount');
    }
}


