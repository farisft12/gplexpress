<?php

namespace App\Services\Payment;

use App\Models\Shipment;
use App\Models\PaymentTransaction;
use Midtrans\Config;
use Midtrans\CoreApi;
use Illuminate\Support\Facades\Log;

class QrisPaymentService
{
    /**
     * Create QRIS payment via Midtrans
     */
    public function createQrisPayment(Shipment $shipment): array
    {
        if ($shipment->type !== 'cod' || $shipment->status !== 'sampai_di_cabang_tujuan') {
            throw new \Exception('Pembayaran tidak dapat diproses. Status paket harus "Sampai di Cabang Tujuan".');
        }

        if ($shipment->cod_status === 'lunas') {
            throw new \Exception('Paket ini sudah lunas.');
        }

        // Validate Midtrans configuration
        $serverKey = config('services.midtrans.server_key');
        
        if (empty($serverKey)) {
            Log::error('Midtrans server key is missing. Please check .env file for MIDTRANS_SERVER_KEY');
            throw new \Exception('Konfigurasi Midtrans belum lengkap.');
        }
        
        // Re-configure Midtrans
        Config::$serverKey = $serverKey;
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = config('services.midtrans.is_sanitized', true);
        Config::$is3ds = config('services.midtrans.is_3ds', true);

        // Prepare transaction data
        $orderId = 'GPL-' . $shipment->resi_number . '-' . time();

        $grossAmount = (int) $shipment->total_cod_collectible;

        
        if ($grossAmount <= 0) {
            throw new \Exception('Jumlah pembayaran tidak valid.');
        }

        // Prepare transaction parameters
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => [
                [
                    'id' => $shipment->resi_number,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => 'Pembayaran COD - ' . $shipment->resi_number,
                ],
            ],
            'customer_details' => [
                'first_name' => $shipment->receiver_name,
                'phone' => $shipment->receiver_phone,
                'email' => $shipment->receiver_phone . '@gplexpres.com', // Midtrans requires email
            ],
            'payment_type' => 'qris',
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        try {
            // Create transaction using Midtrans CoreApi
            $response = CoreApi::charge($params);
            
            if ($response->status_code !== '201') {
                Log::error('Midtrans API error: ' . json_encode($response));
                throw new \Exception('Gagal membuat transaksi pembayaran. Silakan coba lagi.');
            }

            // Create payment transaction record
            $paymentTransaction = PaymentTransaction::create([
                'shipment_id' => $shipment->id,
                'transaction_id' => $response->transaction_id,
                'order_id' => $orderId,
                'status' => 'pending',
                'payment_method' => 'qris',
                'gross_amount' => $grossAmount,
                'is_processed' => false,
            ]);

            return [
                'success' => true,
                'transaction_id' => $response->transaction_id,
                'payment_transaction_id' => $paymentTransaction->id,
                'order_id' => $orderId,
                'qr_code' => $response->actions[0]->url ?? null,
                'deep_link' => $response->actions[0]->url ?? null,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to create QRIS payment for shipment {$shipment->resi_number}: {$e->getMessage()}");
            throw new \Exception('Gagal membuat transaksi pembayaran: ' . $e->getMessage());
        }
    }
}

