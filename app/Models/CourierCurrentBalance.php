<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierCurrentBalance extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'courier_id',
        'current_balance',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'updated_at' => 'datetime',
    ];

    /**
     * Get courier
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Update balance (with transaction safety)
     */
    public static function updateBalance(int $courierId, float $amount, string $operation = 'add'): void
    {
        \DB::transaction(function () use ($courierId, $amount, $operation) {
            $balance = self::firstOrCreate(
                ['courier_id' => $courierId],
                ['current_balance' => 0, 'updated_at' => now()]
            );

            if ($operation === 'add') {
                $balance->current_balance += $amount;
            } elseif ($operation === 'subtract') {
                $balance->current_balance -= $amount;
                if ($balance->current_balance < 0) {
                    throw new \Exception('Balance cannot be negative.');
                }
            }

            $balance->updated_at = now();
            $balance->save();
        });
    }

    /**
     * Get current balance for courier
     */
    public static function getBalance(int $courierId): float
    {
        $balance = self::where('courier_id', $courierId)->first();
        return $balance ? (float) $balance->current_balance : 0.0;
    }
}
