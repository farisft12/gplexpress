<?php

namespace App\Services\Payment;

use App\Models\Shipment;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Process cash payment
     */
    public function processCashPayment(Shipment $shipment, ?int $userId = null): void
    {
        if ($shipment->type !== 'cod' || $shipment->status !== 'sampai_di_cabang_tujuan') {
            throw new \Exception('Pembayaran tidak dapat diproses.');
        }

        if ($shipment->cod_status === 'lunas') {
            throw new \Exception('Paket ini sudah lunas.');
        }

        $userId = $userId ?? auth()->id();

        DB::transaction(function () use ($shipment, $userId) {
            $shipment->update([
                'payment_method' => 'cash',
                'cod_status' => 'lunas',
                'payment_status' => 'settlement',
            ]);

            // Create status history (will be handled by observer)
            $shipment->statusHistories()->create([
                'status' => 'diterima',
                'updated_by' => $userId,
                'notes' => 'Pembayaran COD dengan Cash - Lunas',
            ]);

            // Update status to diterima
            $shipment->update([
                'status' => 'diterima',
                'delivered_at' => now(),
            ]);
        });
    }

    /**
     * Get payment transactions query
     */
    public function getPaymentTransactionsQuery(array $filters = [])
    {
        $query = PaymentTransaction::with('shipment');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc');
    }
}

