<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class TrackingController extends Controller
{
    /**
     * Show tracking form
     */
    public function index(Request $request)
    {
        $resiNumber = $request->get('resi_number');
        if ($resiNumber) {
            return $this->track($request);
        }
        return view('tracking.index');
    }

    /**
     * Track shipment with rate limiting and privacy protection
     */
    public function track(Request $request)
    {
        $validated = $request->validate([
            'resi_number' => ['required', 'string', 'max:50'],
        ]);

        $resiNumber = trim($validated['resi_number']);
        $ip = $request->ip();

        // Rate limiting: max 10 attempts per IP per minute
        $key = 'tracking:' . $ip;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->withErrors([
                'resi_number' => 'Terlalu banyak percobaan. Silakan coba lagi dalam beberapa saat.',
            ]);
        }

        RateLimiter::hit($key, 60); // 60 seconds window

        // Anti brute-force: Log failed attempts
        $shipment = Shipment::where('resi_number', $resiNumber)
            ->with([
                'courier',
                'statusHistories.updater',
                'shipmentSla.slaDefinition',
                'zone',
                'branch',
                'originBranch',
                'destinationBranch',
            ])
            ->first();

        if (!$shipment) {
            // Log failed attempt for security monitoring
            \Log::warning("Tracking attempt failed", [
                'resi' => $resiNumber,
                'ip' => $ip,
            ]);

            return back()->withErrors([
                'resi_number' => 'Nomor resi tidak ditemukan. Pastikan nomor resi sudah benar.',
            ]);
        }

        // Prepare data with privacy protection
        $data = $this->prepareTrackingData($shipment);

        return view('tracking.result', $data);
    }

    /**
     * Prepare tracking data with privacy protection
     */
    protected function prepareTrackingData(Shipment $shipment): array
    {
        return [
            'shipment' => $shipment,
            'eta' => $shipment->eta_at,
            'sla' => $shipment->shipmentSla,
            'cod_status' => $this->getCodStatus($shipment),
            'pod' => $this->getPodData($shipment), // Proof of Delivery metadata
            'masked_phone' => $this->maskPhone($shipment->receiver_phone),
            'masked_address' => $this->maskAddress($shipment->receiver_address),
        ];
    }

    /**
     * Get COD payment status (read-only for customer)
     */
    protected function getCodStatus(Shipment $shipment): ?array
    {
        if ($shipment->type !== 'cod') {
            return null;
        }

        return [
            'amount' => $shipment->cod_amount,
            'status' => $shipment->cod_status,
            'status_label' => match($shipment->cod_status) {
                'lunas' => 'Lunas',
                'belum_lunas' => 'Belum Lunas',
                default => 'Pending',
            },
            'payment_method' => $shipment->payment_method,
            'paid_at' => $shipment->paymentTransactions?->first()?->paid_at,
        ];
    }

    /**
     * Get Proof of Delivery data (metadata only)
     */
    protected function getPodData(Shipment $shipment): ?array
    {
        if ($shipment->status !== 'diterima') {
            return null;
        }

        return [
            'delivered_at' => $shipment->delivered_at,
            'delivered_by' => $shipment->courier?->name,
            'signature' => null, // Placeholder for future signature storage
            'photo' => null, // Placeholder for future photo storage
        ];
    }

    /**
     * Mask phone number for privacy
     */
    protected function maskPhone(?string $phone): string
    {
        if (!$phone || strlen($phone) < 8) {
            return '***';
        }

        return substr($phone, 0, 4) . str_repeat('*', strlen($phone) - 8) . substr($phone, -4);
    }

    /**
     * Mask address for privacy (show only city/district)
     */
    protected function maskAddress(?string $address): string
    {
        if (!$address) {
            return '***';
        }

        // Extract city/district if available, otherwise mask
        if (preg_match('/(Kota|Kab\.|Kec\.)\s+([^,]+)/', $address, $matches)) {
            return $matches[0] . ', ***';
        }

        // Mask most of the address
        $parts = explode(',', $address);
        if (count($parts) > 1) {
            return '***, ' . end($parts);
        }

        return '***';
    }
}

