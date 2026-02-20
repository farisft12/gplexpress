<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\BranchScope;

class CourierSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'courier_id',
        'amount',
        'method',
        'status',
        'confirmed_by',
        'confirmed_at',
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
        'confirmed_at' => 'datetime',
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
     * Get admin who confirmed
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Check if settlement is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if settlement is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
