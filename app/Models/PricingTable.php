<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'origin_branch_id',
        'destination_branch_id',
        'base_price',
        'cod_fee_percentage',
        'cod_fee_fixed',
        'service_type',
        'estimated_days',
        'status',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'cod_fee_percentage' => 'decimal:2',
        'cod_fee_fixed' => 'decimal:2',
    ];

    /**
     * Get origin branch
     */
    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    /**
     * Get destination branch
     */
    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    /**
     * Calculate COD fee
     */
    public function calculateCodFee(float $amount): float
    {
        $percentageFee = ($amount * $this->cod_fee_percentage) / 100;
        return $percentageFee + $this->cod_fee_fixed;
    }

    /**
     * Check if pricing is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}







