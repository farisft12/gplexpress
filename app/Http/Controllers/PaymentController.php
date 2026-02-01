<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\PaymentTransaction;
use App\Jobs\ProcessPaymentCallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Configure Midtrans from config/services.php (which reads from .env)
        $serverKey = config('services.midtrans.server_key');
        
        if (!empty($serverKey)) {
            Config::$serverKey = $serverKey;
            Config::$isProduction = config('services.midtrans.is_production', false);
            Config::$isSanitized = config('services.midtrans.is_sanitized', true);
            Config::$is3ds = config('services.midtrans.is_3ds', true);
        }
    }

    /**
     * Show payment form for COD shipment
     */
    public function showPaymentForm(Shipment $shipment)
    {
        // Only allow payment for COD shipments that are at destination branch
        if ($shipment->type !== 'cod') {
            return back()->withErrors(['error' => 'Paket ini bukan paket COD.']);
        }

        if ($shipment->status !== 'sampai_di_cabang_tujuan') {
            return back()->withErrors(['error' => 'Pembayaran hanya dapat dilakukan saat paket sudah sampai di cabang tujuan.']);
        }

        if ($shipment->cod_status === 'lunas') {
            return back()->withErrors(['error' => 'Paket ini sudah lunas.']);
        }

        return view('admin.shipments.payment', compact('shipment'));
    }

    /**
     * Show payment detail view (Admin)
     */
    public function showPaymentDetail(Shipment $shipment)
    {
        $paymentTransaction = PaymentTransaction::where('shipment_id', $shipment->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return view('admin.payments.detail', compact('shipment', 'paymentTransaction'));
    }

    /**
     * List failed/expired payments (Admin)
     */
    public function listFailedPayments(Request $request)
    {
        $query = PaymentTransaction::with('shipment')
            ->whereIn('status', ['expire', 'deny', 'cancel'])
            ->orderBy('created_at', 'desc');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $failedPayments = $query->paginate(20);

        return view('admin.payments.failed', compact('failedPayments'));
    }

    /**
     * List all payments with status (Admin)
     */
    public function listPayments(Request $request)
    {
        $query = PaymentTransaction::with('shipment')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Process Cash Payment
     */
    public function processCashPayment(Request $request, Shipment $shipment)
    {
        if ($shipment->type !== 'cod' || $shipment->status !== 'sampai_di_cabang_tujuan') {
            return back()->withErrors(['error' => 'Pembayaran tidak dapat diproses.']);
        }

        if ($shipment->cod_status === 'lunas') {
            return back()->withErrors(['error' => 'Paket ini sudah lunas.']);
        }

        DB::transaction(function () use ($shipment) {
            $shipment->update([
                'payment_method' => 'cash',
                'cod_status' => 'lunas',
                'payment_status' => 'settlement',
            ]);

            // Create status history
            $shipment->statusHistories()->create([
                'status' => 'diterima',
                'updated_by' => auth()->id(),
                'notes' => 'Pembayaran COD dengan Cash - Lunas',
            ]);

            // Update status to diterima
            $shipment->update([
                'status' => 'diterima',
                'delivered_at' => now(),
            ]);
        });

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Pembayaran Cash berhasil diproses. Status paket diubah menjadi Diterima.');
    }

    /**
     * Create QRIS Payment via Midtrans
     */
    public function createQrisPayment(Request $request, Shipment $shipment)
    {
        if ($shipment->type !== 'cod' || $shipment->status !== 'sampai_di_cabang_tujuan') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran tidak dapat diproses. Status paket harus "Sampai di Cabang Tujuan".',
            ], 400);
        }

        if ($shipment->cod_status === 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Paket ini sudah lunas.',
            ], 400);
        }

        try {
            // Validate Midtrans configuration from config/services.php (which reads from .env)
            $serverKey = config('services.midtrans.server_key');
            
            if (empty($serverKey)) {
                \Log::error('Midtrans server key is missing. Please check .env file for MIDTRANS_SERVER_KEY');
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi Midtrans belum lengkap. Pastikan MIDTRANS_SERVER_KEY sudah diisi di file .env',
                ], 500);
            }
            
            // Re-configure Midtrans to ensure it uses the correct key
            Config::$serverKey = $serverKey;
            Config::$isProduction = config('services.midtrans.is_production', false);
            Config::$isSanitized = config('services.midtrans.is_sanitized', true);
            Config::$is3ds = config('services.midtrans.is_3ds', true);

            // Prepare transaction data
            $orderId = 'GPL-' . $shipment->resi_number . '-' . time();
            $grossAmount = (int) $shipment->cod_amount;
            
            // Validate amount
            if ($grossAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran tidak valid.',
                ], 400);
            }

            // Prepare transaction parameters according to Midtrans documentation
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
                'payment_type' => 'qris',
                'customer_details' => [
                    'first_name' => $shipment->receiver_name,
                    'phone' => $shipment->receiver_phone,
                    'email' => $shipment->receiver_phone . '@gplexpres.com', // Midtrans requires email
                ],
                'item_details' => [
                    [
                        'id' => $shipment->resi_number,
                        'price' => $grossAmount,
                        'quantity' => 1,
                        'name' => 'COD - ' . $shipment->resi_number,
                    ],
                ],
                'custom_expiry' => [
                    'expiry_duration' => 15,
                    'unit' => 'minute',
                ],
            ];

            // Add notification URL if available
            $notificationUrl = route('admin.payments.midtrans-callback');
            if ($notificationUrl) {
                // Note: Notification URL should be set in Midtrans dashboard or via X-Override-Notification header
                // For now, we'll use custom fields to track shipment
                $params['custom_field1'] = (string) $shipment->id;
                $params['custom_field2'] = $shipment->resi_number;
            }

            // Use CoreApi SDK which handles authentication correctly
            try {
                $transaction = CoreApi::charge($params);
            } catch (\Exception $e) {
                \Log::error('Midtrans CoreApi charge error: ' . $e->getMessage());
                \Log::error('Midtrans params: ' . json_encode($params));
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat transaksi QRIS: ' . $e->getMessage(),
                ], 400);
            }
            
            // Convert object to array if needed
            if (is_object($transaction)) {
                $transaction = json_decode(json_encode($transaction), true);
            }

            // Log response for debugging
            \Log::info('Midtrans response: ' . json_encode($transaction));

            // Check if there's an error in response
            if (isset($transaction['status_code']) && $transaction['status_code'] != 201) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat transaksi QRIS: ' . ($transaction['status_message'] ?? 'Unknown error'),
                    'error_details' => $transaction,
                ], 400);
            }

            // Check response
            if (isset($transaction['transaction_status']) && $transaction['transaction_status'] === 'pending') {
                // SECURITY: Use DB transaction to ensure data consistency
                DB::transaction(function () use ($shipment, $transaction, $orderId, $grossAmount) {
                    // Save transaction ID
                    $shipment->update([
                        'payment_transaction_id' => $transaction['transaction_id'],
                        'payment_method' => 'qris',
                        'payment_status' => 'pending',
                    ]);

                    // Create payment transaction record for tracking
                    PaymentTransaction::create([
                        'shipment_id' => $shipment->id,
                        'transaction_id' => $transaction['transaction_id'],
                        'order_id' => $orderId,
                        'status' => 'pending',
                        'payment_method' => 'qris',
                        'gross_amount' => $grossAmount,
                        'fraud_status' => null,
                        'is_processed' => false,
                    ]);
                });

                // Get QR code URL from actions (for fetching QR code image)
                // According to Midtrans docs: For Core API, retrieve from charge API response
                // Format: actions array contains objects with 'name' and 'url'
                $qrCodeUrl = null;
                $qrCodeUrlV2 = null;
                $qrCodeUrlV4 = null;
                
                if (isset($transaction['actions']) && is_array($transaction['actions'])) {
                    foreach ($transaction['actions'] as $action) {
                        if (isset($action['name']) && isset($action['url'])) {
                            // Check for v2 (generate-qr-code) - Standard API endpoint
                            // This URL points to QR code image that can be used in simulator
                            if ($action['name'] === 'generate-qr-code') {
                                $qrCodeUrlV2 = $action['url'];
                            } 
                            // Check for v4 (generate-qr-code-v2) - Alternative endpoint
                            elseif ($action['name'] === 'generate-qr-code-v2') {
                                $qrCodeUrlV4 = $action['url'];
                            }
                        }
                    }
                }
                
                // For QRIS Simulator, we need the direct image URL
                // According to Midtrans docs and simulator requirements:
                // Simulator needs URL that points to QR code image that can be accessed directly
                // v2 URL format: https://api.sandbox.midtrans.com/v2/qris/{transaction_id}/qr-code
                // v4 URL format: https://merchants-app.sbx.midtrans.com/v4/qris/gopay/{qr_id}/qr-code
                // Both should work, but v2 is the standard API endpoint
                // Use v2 first (standard), fallback to v4
                $qrCodeUrl = $qrCodeUrlV2 ?: $qrCodeUrlV4;
                
                // Log both URLs for debugging
                \Log::info('Available QR Code URLs', [
                    'v2_url' => $qrCodeUrlV2,
                    'v4_url' => $qrCodeUrlV4,
                    'selected' => $qrCodeUrl
                ]);
                
                // If no URL from actions, try to construct from transaction_id
                // Format: https://api.sandbox.midtrans.com/v2/qris/{transaction_id}/qr-code
                if (!$qrCodeUrl && isset($transaction['transaction_id'])) {
                    $isProduction = config('services.midtrans.is_production', false);
                    $baseUrl = $isProduction 
                        ? 'https://api.midtrans.com'
                        : 'https://api.sandbox.midtrans.com';
                    $qrCodeUrl = $baseUrl . '/v2/qris/' . $transaction['transaction_id'] . '/qr-code';
                }
                
                // Log for debugging
                \Log::info('QR Code URLs from Midtrans Core API response', [
                    'actions_count' => count($transaction['actions'] ?? []),
                    'v2_url' => $qrCodeUrlV2,
                    'v4_url' => $qrCodeUrlV4,
                    'selected_url' => $qrCodeUrl,
                    'all_actions' => $transaction['actions'] ?? []
                ]);

                // Validate qr_string exists
                $qrString = $transaction['qr_string'] ?? null;
                if (!$qrString) {
                    \Log::error('QR string not found in Midtrans response', [
                        'transaction' => $transaction
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'QR Code tidak ditemukan dalam response Midtrans. Silakan coba lagi.',
                        'debug' => $transaction
                    ], 400);
                }

                // Return QR code data
                // Ensure URL is clean and ready for QRIS Simulator
                // Format should be: https://api.sandbox.midtrans.com/v2/qris/{transaction_id}/qr-code
                // IMPORTANT: URL must be plain text, NOT encoded, for simulator to parse it correctly
                $cleanQrCodeUrl = $qrCodeUrl;
                
                // Decode URL multiple times if needed (handle double/triple encoding)
                if ($cleanQrCodeUrl) {
                    $previousUrl = '';
                    $maxDecodeAttempts = 10; // Prevent infinite loop
                    $decodeAttempts = 0;
                    
                    while ($cleanQrCodeUrl !== $previousUrl && strpos($cleanQrCodeUrl, '%') !== false && $decodeAttempts < $maxDecodeAttempts) {
                        $previousUrl = $cleanQrCodeUrl;
                        $cleanQrCodeUrl = urldecode($cleanQrCodeUrl);
                        $decodeAttempts++;
                    }
                }
                
                // Ensure URL is absolute and valid
                if ($cleanQrCodeUrl && !filter_var($cleanQrCodeUrl, FILTER_VALIDATE_URL)) {
                    \Log::error('Invalid QR code URL format', ['url' => $cleanQrCodeUrl]);
                    $cleanQrCodeUrl = null;
                }
                
                // Log final URL for debugging
                \Log::info('Final QR Code URL for simulator', [
                    'original_url' => $qrCodeUrl,
                    'cleaned_url' => $cleanQrCodeUrl,
                    'is_valid_url' => $cleanQrCodeUrl ? filter_var($cleanQrCodeUrl, FILTER_VALIDATE_URL) : false,
                    'transaction_id' => $transaction['transaction_id']
                ]);
                
                return response()->json([
                    'success' => true,
                    'transaction_id' => $transaction['transaction_id'],
                    'qr_code_url' => $cleanQrCodeUrl, // Clean URL, plain text, NOT encoded, ready for simulator
                    'qr_string' => $qrString,
                    'order_id' => $orderId,
                    'expiry_time' => $transaction['expiry_time'] ?? null,
                    'merchant_id' => $transaction['merchant_id'] ?? null,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat transaksi QRIS: ' . ($transaction['status_message'] ?? 'Unknown error'),
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating QRIS payment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'shipment_id' => $shipment->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check QRIS Payment Status
     * SECURITY: Never trust frontend payment status - always verify with Midtrans
     */
    public function checkPaymentStatus(Shipment $shipment)
    {
        // SECURITY: Verify shipment ownership/access
        if (!$shipment->payment_transaction_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada transaksi pembayaran.',
            ], 400);
        }

        try {
            // SECURITY: Always verify with Midtrans API (never trust frontend)
            $status = Transaction::status($shipment->payment_transaction_id);

            // Convert to array if object
            if (is_object($status)) {
                $status = json_decode(json_encode($status), true);
            }

            $transactionStatus = $status['transaction_status'] ?? null;
            $fraudStatus = $status['fraud_status'] ?? 'accept';

            if (!$transactionStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat memverifikasi status pembayaran.',
                ], 500);
            }

            // Update payment status in database
            $shipment->update([
                'payment_status' => $transactionStatus,
            ]);

            // Update or create payment transaction record
            PaymentTransaction::updateOrCreate(
                ['transaction_id' => $shipment->payment_transaction_id],
                [
                    'shipment_id' => $shipment->id,
                    'order_id' => $status['order_id'] ?? null,
                    'status' => $transactionStatus,
                    'payment_method' => $shipment->payment_method,
                    'gross_amount' => $status['gross_amount'] ?? $shipment->cod_amount,
                    'fraud_status' => $fraudStatus,
                ]
            );

            // SECURITY: Use DB transaction with row locking to prevent race conditions
            if ($transactionStatus === 'settlement' && $fraudStatus === 'accept') {
                DB::transaction(function () use ($shipment) {
                    // Lock row for update (prevent concurrent modifications)
                    $shipment = Shipment::where('id', $shipment->id)
                        ->lockForUpdate()
                        ->first();

                    // Idempotency check: Prevent double processing
                    if ($shipment->cod_status === 'lunas') {
                        Log::info('checkPaymentStatus: Shipment already paid', [
                            'shipment_id' => $shipment->id
                        ]);
                        return;
                    }

                    $shipment->update([
                        'cod_status' => 'lunas',
                        'payment_status' => 'settlement',
                    ]);

                    // Create status history
                    $shipment->statusHistories()->create([
                        'status' => 'diterima',
                        'updated_by' => auth()->id(),
                        'notes' => 'Pembayaran COD dengan QRIS - Lunas',
                    ]);

                    // Update status to diterima
                    $shipment->update([
                        'status' => 'diterima',
                        'delivered_at' => now(),
                    ]);
                });

                return response()->json([
                    'success' => true,
                    'status' => 'settlement',
                    'message' => 'Pembayaran berhasil. Status paket diubah menjadi Diterima.',
                ]);
            } elseif ($transactionStatus === 'expire') {
                return response()->json([
                    'success' => false,
                    'status' => 'expire',
                    'message' => 'Transaksi telah kedaluwarsa.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'status' => $transactionStatus,
                    'message' => 'Status pembayaran: ' . $transactionStatus,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('checkPaymentStatus error: ' . $e->getMessage(), [
                'shipment_id' => $shipment->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Midtrans Callback
     * SECURITY: Validates IP, signature, and processes asynchronously
     */
    public function midtransCallback(Request $request)
    {
        try {
            $notification = $request->all();
            $callbackIp = $request->ip();

            // SECURITY: Validate required fields
            if (!isset($notification['order_id']) || !isset($notification['status_code']) || 
                !isset($notification['gross_amount']) || !isset($notification['signature_key'])) {
                Log::warning('Midtrans callback: Missing required fields', [
                    'ip' => $callbackIp,
                    'notification' => $notification
                ]);
                return response()->json(['error' => 'Invalid notification format'], 400);
            }

            $orderId = $notification['order_id'];
            $statusCode = $notification['status_code'];
            $grossAmount = $notification['gross_amount'];
            $serverKey = config('services.midtrans.server_key');
            $signatureKey = $notification['signature_key'];

            // SECURITY: Verify signature (NEVER trust frontend payment status)
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            if ($signatureKey !== $expectedSignature) {
                Log::error('Midtrans callback: Invalid signature', [
                    'ip' => $callbackIp,
                    'order_id' => $orderId,
                    'expected' => substr($expectedSignature, 0, 20) . '...',
                    'received' => substr($signatureKey, 0, 20) . '...'
                ]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            // SECURITY: Validate callback IP (optional but recommended)
            // Note: Midtrans doesn't provide official IP whitelist, but we can log for monitoring
            $isProduction = config('services.midtrans.is_production', false);
            if ($isProduction) {
                // In production, you may want to whitelist Midtrans IPs
                // For now, we rely on signature verification
                Log::info('Midtrans callback received', [
                    'ip' => $callbackIp,
                    'order_id' => $orderId,
                    'status' => $notification['transaction_status'] ?? 'unknown'
                ]);
            }

            // Extract shipment from order_id for validation
            $orderParts = explode('-', $orderId);
            if (count($orderParts) < 3) {
                Log::error('Midtrans callback: Invalid order ID format', [
                    'order_id' => $orderId,
                    'ip' => $callbackIp
                ]);
                return response()->json(['error' => 'Invalid order ID'], 400);
            }

            $resiNumber = $orderParts[1];
            $shipment = Shipment::where('resi_number', $resiNumber)->first();

            if (!$shipment) {
                Log::error('Midtrans callback: Shipment not found', [
                    'resi_number' => $resiNumber,
                    'order_id' => $orderId,
                    'ip' => $callbackIp
                ]);
                return response()->json(['error' => 'Shipment not found'], 404);
            }

            // Validate gross amount matches
            if ((int)$grossAmount !== (int)$shipment->cod_amount) {
                Log::warning('Midtrans callback: Amount mismatch', [
                    'shipment_id' => $shipment->id,
                    'expected' => $shipment->cod_amount,
                    'received' => $grossAmount,
                    'ip' => $callbackIp
                ]);
                // Still process, but log the discrepancy
            }

            // PERFORMANCE: Process callback asynchronously via queue
            // This prevents blocking the callback endpoint and allows retry on failure
            ProcessPaymentCallback::dispatch($notification, $callbackIp)
                ->onQueue('payments'); // Use dedicated queue for payments

            // Return success immediately (async processing)
            return response()->json([
                'success' => true,
                'message' => 'Callback received and queued for processing'
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans callback error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'notification' => $request->all()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get QR Code Image from Midtrans URL (Proxy endpoint)
     * Returns image directly, not blob URL
     */
    public function getQrCodeImage(Request $request, Shipment $shipment)
    {
        $qrCodeUrl = $request->query('url');
        
        if (!$qrCodeUrl) {
            return response()->json(['error' => 'QR Code URL tidak tersedia'], 400);
        }
        
        // Decode URL multiple times if needed (handle double/triple encoding)
        $originalUrl = $qrCodeUrl;
        $previousUrl = '';
        while ($qrCodeUrl !== $previousUrl && strpos($qrCodeUrl, '%') !== false) {
            $previousUrl = $qrCodeUrl;
            $qrCodeUrl = urldecode($qrCodeUrl);
        }
        
        // Log for debugging
        \Log::info('QR Code URL received and decoded', [
            'original' => $originalUrl,
            'decoded' => $qrCodeUrl
        ]);

        try {
            $serverKey = config('services.midtrans.server_key');
            $isProduction = config('services.midtrans.is_production', false);
            
            if (empty($serverKey)) {
                return response()->json(['error' => 'Konfigurasi Midtrans tidak lengkap'], 500);
            }
            
            // URL dari Midtrans sudah lengkap (dari actions array in response)
            // Format untuk sandbox:
            // - https://api.sandbox.midtrans.com/v2/qris/{transaction_id}/qr-code (v2)
            // - https://merchants-app.sbx.midtrans.com/v4/qris/gopay/{qr_id}/qr-code (v4)
            // Format untuk production:
            // - https://api.midtrans.com/v2/qris/{transaction_id}/qr-code (v2)
            // - https://merchants-app.midtrans.com/v4/qris/gopay/{qr_id}/qr-code (v4)
            
            // Validate URL is from Midtrans domain (security check)
            $parsedUrl = parse_url($qrCodeUrl);
            $allowedDomains = $isProduction 
                ? ['api.midtrans.com', 'merchants-app.midtrans.com']
                : ['api.sandbox.midtrans.com', 'merchants-app.sbx.midtrans.com'];
            
            if (!isset($parsedUrl['host']) || !in_array($parsedUrl['host'], $allowedDomains)) {
                \Log::error('Invalid QR code URL domain', ['url' => $qrCodeUrl, 'host' => $parsedUrl['host'] ?? 'none']);
                return response()->json(['error' => 'URL QR Code tidak valid'], 400);
            }
            
            // Fetch QR code image from Midtrans dengan autentikasi
            // According to Midtrans docs, use Basic Auth with Server Key
            $response = Http::withHeaders([
                'Accept' => 'image/png,image/jpeg,image/*',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])
            ->timeout(30)
            ->get($qrCodeUrl);
            
            if ($response->successful()) {
                // Get content type from response
                $contentType = $response->header('Content-Type') ?: 'image/png';
                
                // Return image directly with proper headers
                return response($response->body(), 200)
                    ->header('Content-Type', $contentType)
                    ->header('Cache-Control', 'public, max-age=3600')
                    ->header('Access-Control-Allow-Origin', '*');
            } else {
                \Log::error('Midtrans QR code fetch failed', [
                    'url' => $qrCodeUrl,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 200)
                ]);
                return response()->json([
                    'error' => 'Gagal mengambil QR Code',
                    'status' => $response->status()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching QR code image: ' . $e->getMessage(), [
                'url' => $qrCodeUrl,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil QR Code: ' . $e->getMessage()], 500);
        }
    }
}
