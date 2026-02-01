<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierPerformanceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_id',
        'branch_id',
        'period_type',
        'period_date',
        'metrics',
        'generated_at',
    ];

    protected $casts = [
        'period_date' => 'date',
        'metrics' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Get courier
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Get branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get snapshot for courier and period
     */
    public static function getSnapshot(int $courierId, string $periodType, string $periodDate)
    {
        // Normalize period_type: 'week' -> 'weekly', 'day' -> 'daily', 'month' -> 'monthly'
        $normalizedType = match($periodType) {
            'week' => 'weekly',
            'day' => 'daily',
            'month' => 'monthly',
            default => $periodType,
        };
        
        return self::where('courier_id', $courierId)
            ->where('period_type', $normalizedType)
            ->where('period_date', $periodDate)
            ->first();
    }
}
