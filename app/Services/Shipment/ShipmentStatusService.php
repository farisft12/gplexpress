<?php

namespace App\Services\Shipment;

use App\Models\Shipment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShipmentStatusService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Allowed status transitions
     */
    protected function getAllowedTransitions(): array
    {
        return [
            'pickup' => ['diproses'],
            'diproses' => ['dalam_pengiriman', 'gagal'],
            'dalam_pengiriman' => ['sampai_di_cabang_tujuan', 'gagal'],
            'sampai_di_cabang_tujuan' => ['diterima', 'gagal'],
            'diterima' => [],
            'gagal' => [],
        ];
    }

    /**
     * Validate status transition
     */
    public function validateTransition(Shipment $shipment, string $newStatus): bool
    {
        $allowedTransitions = $this->getAllowedTransitions();
        $currentStatus = $shipment->status;

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    /**
     * Update shipment status
     */
    public function updateStatus(Shipment $shipment, array $data, ?int $userId = null): Shipment
    {
        $userId = $userId ?? auth()->id();

        if (!$this->validateTransition($shipment, $data['status'])) {
            throw new \Exception('Perubahan status tidak valid.');
        }

        // Validate payment_method is required for COD when status becomes diterima
        if ($data['status'] === 'diterima' && $shipment->type === 'cod' && empty($data['payment_method'])) {
            throw new \Exception('Metode pembayaran wajib diisi untuk paket COD yang diterima.');
        }

        return DB::transaction(function () use ($shipment, $data, $userId) {
            $oldStatus = $shipment->status;
            $updateData = [
                'status' => $data['status'],
            ];

            // Set timestamps based on status
            if ($data['status'] === 'dalam_pengiriman' && !$shipment->out_for_delivery_at) {
                $updateData['out_for_delivery_at'] = now();
            }

            if ($data['status'] === 'diterima') {
                $updateData['delivered_at'] = now();
                if ($shipment->type === 'cod' && !empty($data['payment_method'])) {
                    $updateData['payment_method'] = $data['payment_method'];
                }
            }

            if ($data['status'] === 'gagal') {
                $updateData['failed_at'] = now();
            }

            $shipment->update($updateData);

            // Create status history (will be handled by observer, but we can also do it here for explicit control)
            $shipment->statusHistories()->create([
                'status' => $data['status'],
                'updated_by' => $userId,
                'notes' => $data['notes'] ?? 'Status diubah',
            ]);

            // Send notifications
            $this->sendStatusNotifications($shipment, $data);

            return $shipment->fresh();
        });
    }

    /**
     * Send notifications based on status change
     */
    protected function sendStatusNotifications(Shipment $shipment, array $data): void
    {
        try {
            $templateCode = match($data['status']) {
                'dalam_pengiriman' => 'kurir_otw',
                'sampai_di_cabang_tujuan' => 'paket_sampai_cabang',
                'diterima' => 'paket_terkirim',
                'gagal' => 'gagal_antar',
                default => null,
            };

            if ($templateCode) {
                $this->notificationService->send($templateCode, $shipment->fresh(), ['whatsapp', 'email']);
            }

            // Send COD payment notification if COD is paid
            if ($data['status'] === 'diterima' && $shipment->type === 'cod' && !empty($data['payment_method'])) {
                $this->notificationService->send('cod_lunas', $shipment->fresh(), ['whatsapp', 'email']);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification for shipment {$shipment->resi_number}: {$e->getMessage()}");
        }
    }

}

