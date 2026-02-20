<?php

namespace App\Observers;

use App\Models\PaymentTransaction;
use App\Models\Shipment;
use App\Models\CourierBalance;
use App\Models\CourierCurrentBalance;
use Illuminate\Support\Facades\Log;

class PaymentTransactionObserver
{
    /**
     * Handle the PaymentTransaction "created" event.
     */
    public function created(PaymentTransaction $paymentTransaction): void
    {
        // Update shipment payment status when transaction is created
        if ($paymentTransaction->shipment) {
            $paymentTransaction->shipment->update([
                'payment_transaction_id' => $paymentTransaction->id,
                'payment_status' => 'pending',
            ]);
        }
    }

    /**
     * Handle the PaymentTransaction "updated" event.
     */
    public function updated(PaymentTransaction $paymentTransaction): void
    {
        // Handle payment status changes
        if ($paymentTransaction->wasChanged('status')) {
            $this->handleStatusChange($paymentTransaction);
        }
    }

    /**
     * Handle payment status change
     */
    protected function handleStatusChange(PaymentTransaction $paymentTransaction): void
    {
        $shipment = $paymentTransaction->shipment;
        
        if (!$shipment) {
            return;
        }

        // Only process settlement status (successful payment)
        if ($paymentTransaction->status === 'settlement' && !$paymentTransaction->isAlreadyProcessed()) {
            try {
                \DB::transaction(function () use ($paymentTransaction, $shipment) {
                    // Update shipment payment status
                    $shipment->update([
                        'payment_method' => $paymentTransaction->payment_method ?? 'qris',
                        'cod_status' => 'lunas',
                        'payment_status' => 'settlement',
                    ]);

                    // If COD, record in courier balance
                    if ($shipment->type === 'cod' && $shipment->courier_id) {
                        CourierBalance::create([
                            'courier_id' => $shipment->courier_id,
                            'shipment_id' => $shipment->id,
                            'type' => 'cod_collected',
                            'amount' => $shipment->total_cod_collectible,
                            'notes' => 'COD collected via ' . ($paymentTransaction->payment_method ?? 'qris'),
                        ]);

                        // Update current balance
                        CourierCurrentBalance::updateBalance($shipment->courier_id, $shipment->total_cod_collectible, 'add');
                    }

                    // Mark transaction as processed
                    $paymentTransaction->markAsProcessed();
                });
            } catch (\Exception $e) {
                Log::error("Failed to process payment transaction {$paymentTransaction->id}: {$e->getMessage()}");
                $paymentTransaction->markAsProcessed($e->getMessage());
            }
        } elseif (in_array($paymentTransaction->status, ['expire', 'deny', 'cancel'])) {
            // Update shipment payment status for failed payments
            $shipment->update([
                'payment_status' => $paymentTransaction->status,
            ]);
        }
    }
}
