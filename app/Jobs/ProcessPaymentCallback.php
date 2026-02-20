<?php

namespace App\Jobs;

use App\Models\PaymentTransaction;
use App\Models\Shipment;
use App\Models\CourierBalance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaymentCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Retry 3 times
    public $timeout = 60; // 60 seconds timeout
    public $backoff = [10, 30, 60]; // Exponential backoff: 10s, 30s, 60s

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $notification,
        public string $callbackIp
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $orderId = $this->notification['order_id'] ?? null;
            $transactionId = $this->notification['transaction_id'] ?? null;
            $transactionStatus = $this->notification['transaction_status'] ?? null;
            $fraudStatus = $this->notification['fraud_status'] ?? 'accept';

            if (!$orderId || !$transactionId || !$transactionStatus) {
                Log::error('ProcessPaymentCallback: Missing required notification fields', [
                    'notification' => $this->notification
                ]);
                return;
            }

            // Extract shipment from order_id (format: GPL-{resi}-{timestamp})
            $orderParts = explode('-', $orderId);
            if (count($orderParts) < 3) {
                Log::error('ProcessPaymentCallback: Invalid order ID format', ['order_id' => $orderId]);
                return;
            }

            $resiNumber = $orderParts[1];
            
            // Use DB transaction with row locking to prevent race conditions
            DB::transaction(function () use ($resiNumber, $transactionId, $orderId, $transactionStatus, $fraudStatus) {
                // Lock shipment row for update (prevent concurrent modifications)
                $shipment = Shipment::where('resi_number', $resiNumber)
                    ->lockForUpdate()
                    ->first();

                if (!$shipment) {
                    Log::error('ProcessPaymentCallback: Shipment not found', ['resi_number' => $resiNumber]);
                    return;
                }

                // Find or create payment transaction record
                $paymentTransaction = PaymentTransaction::firstOrCreate(
                    ['transaction_id' => $transactionId],
                    [
                        'shipment_id' => $shipment->id,
                        'order_id' => $orderId,
                        'status' => $transactionStatus,
                        'payment_method' => $shipment->payment_method,

                        'gross_amount' => $this->notification['gross_amount'] ?? $shipment->total_cod_collectible,

                        'fraud_status' => $fraudStatus,
                        'notification_data' => $this->notification,
                        'callback_ip' => $this->callbackIp,
                    ]
                );

                // Idempotency check: Prevent double processing
                if ($paymentTransaction->isAlreadyProcessed()) {
                    Log::info('ProcessPaymentCallback: Transaction already processed', [
                        'transaction_id' => $transactionId,
                        'shipment_id' => $shipment->id
                    ]);
                    return;
                }

                // Update payment transaction status
                $paymentTransaction->update([
                    'status' => $transactionStatus,
                    'fraud_status' => $fraudStatus,
                    'notification_data' => $this->notification,
                ]);

                // Update shipment payment status
                $shipment->update([
                    'payment_status' => $transactionStatus,
                ]);

                // Process settlement (payment successful)
                if ($transactionStatus === 'settlement' && $fraudStatus === 'accept') {
                    // Double check: Prevent double balance increment
                    if ($shipment->cod_status === 'lunas') {
                        Log::warning('ProcessPaymentCallback: Shipment already marked as paid, skipping balance update', [
                            'shipment_id' => $shipment->id,
                            'transaction_id' => $transactionId
                        ]);
                        $paymentTransaction->markAsProcessed('Already paid');
                        return;
                    }

                    // Update shipment COD status
                    $shipment->update([
                        'cod_status' => 'lunas',
                        'payment_status' => 'settlement',
                    ]);

                    // Create status history
                    $shipment->statusHistories()->create([
                        'status' => 'diterima',
                        'updated_by' => 1, // System (callback doesn't have auth)
                        'notes' => 'Pembayaran COD dengan QRIS - Lunas (via callback)',
                    ]);

                    // Update status to diterima if not already
                    if ($shipment->status !== 'diterima') {
                        $shipment->update([
                            'status' => 'diterima',
                            'delivered_at' => now(),
                        ]);
                    }

                    // Record balance increment ONLY if courier exists and not already recorded
                    if ($shipment->courier_id) {
                        $existingBalance = CourierBalance::where('shipment_id', $shipment->id)
                            ->where('type', 'cod_collected')
                            ->first();

                        if (!$existingBalance) {
                            CourierBalance::create([
                                'courier_id' => $shipment->courier_id,
                                'shipment_id' => $shipment->id,
                                'type' => 'cod_collected',

                                'amount' => $shipment->total_cod_collectible,

                                'notes' => 'COD lunas via QRIS - ' . $shipment->resi_number,
                            ]);
                        } else {
                            Log::info('ProcessPaymentCallback: Balance already recorded for shipment', [
                                'shipment_id' => $shipment->id
                            ]);
                        }
                    }

                    // Mark transaction as processed
                    $paymentTransaction->markAsProcessed();

                    Log::info('ProcessPaymentCallback: Payment processed successfully', [
                        'shipment_id' => $shipment->id,
                        'transaction_id' => $transactionId
                    ]);
                } elseif (in_array($transactionStatus, ['expire', 'deny', 'cancel'])) {
                    // Mark failed transactions as processed
                    $paymentTransaction->markAsProcessed("Status: {$transactionStatus}");
                }
            });
        } catch (\Exception $e) {
            Log::error('ProcessPaymentCallback: Error processing payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'notification' => $this->notification
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessPaymentCallback: Job failed after all retries', [
            'error' => $exception->getMessage(),
            'notification' => $this->notification
        ]);

        // Optionally: Send alert to admin, update transaction with error, etc.
    }
}
