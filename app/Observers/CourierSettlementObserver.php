<?php

namespace App\Observers;

use App\Models\CourierSettlement;
use App\Models\FinancialLog;
use App\Models\CourierCurrentBalance;
use Illuminate\Support\Facades\Log;

class CourierSettlementObserver
{
    /**
     * Handle the CourierSettlement "created" event.
     */
    public function created(CourierSettlement $settlement): void
    {
        // Create financial log
        try {
            FinancialLog::create([
                'type' => 'SETTLEMENT',
                'reference_id' => $settlement->id,
                'courier_id' => $settlement->courier_id,
                'amount' => -$settlement->amount, // Negative for settlement
                'actor_id' => auth()->id(),
                'notes' => 'Settlement dibuat: ' . ($settlement->notes ?? ''),
                'metadata' => [
                    'method' => $settlement->method,
                    'settlement_id' => $settlement->id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create financial log for settlement {$settlement->id}: {$e->getMessage()}");
        }
    }

    /**
     * Handle the CourierSettlement "updated" event.
     */
    public function updated(CourierSettlement $settlement): void
    {
        // Handle settlement confirmation
        if ($settlement->wasChanged('status') && $settlement->status === 'confirmed') {
            try {
                \DB::transaction(function () use ($settlement) {
                    // Update courier balance
                    CourierCurrentBalance::updateBalance(
                        $settlement->courier_id,
                        $settlement->amount,
                        'subtract'
                    );

                    // Update financial log
                    FinancialLog::where('reference_id', $settlement->id)
                        ->where('type', 'SETTLEMENT')
                        ->update([
                            'metadata' => array_merge(
                                FinancialLog::where('reference_id', $settlement->id)->first()->metadata ?? [],
                                [
                                    'confirmed_by' => $settlement->confirmed_by,
                                    'confirmed_at' => $settlement->confirmed_at,
                                ]
                            ),
                        ]);
                });
            } catch (\Exception $e) {
                Log::error("Failed to process settlement confirmation {$settlement->id}: {$e->getMessage()}");
            }
        }
    }
}
