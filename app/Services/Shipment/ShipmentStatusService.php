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

<<<<<<< HEAD
        // Validate COD must be paid before status can be changed to diterima
        if ($data['status'] === 'diterima' && $shipment->type === 'cod') {
            if ($shipment->cod_status !== 'lunas') {
                throw new \Exception('Paket COD harus lunas terlebih dahulu sebelum status dapat diubah menjadi diterima.');
            }
            if (empty($data['payment_method'])) {
                throw new \Exception('Metode pembayaran wajib diisi untuk paket COD yang diterima.');
            }
=======
        // Validate payment_method is required for COD when status becomes diterima
        if ($data['status'] === 'diterima' && $shipment->type === 'cod' && empty($data['payment_method'])) {
            throw new \Exception('Metode pembayaran wajib diisi untuk paket COD yang diterima.');
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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

<<<<<<< HEAD
            // Create status history manually with custom notes (observer will skip if already exists)
            // We create it here to ensure we can set custom notes from user input
            $recentHistory = $shipment->statusHistories()
                ->where('status', $data['status'])
                ->where('created_at', '>=', now()->subSeconds(5))
                ->first();
            
            if (!$recentHistory) {
                $shipment->statusHistories()->create([
                    'status' => $data['status'],
                    'updated_by' => $userId,
                    'notes' => $data['notes'] ?? 'Status diubah',
                ]);
            } else {
                // Update existing history with custom notes if provided
                if (!empty($data['notes']) && $recentHistory->notes === 'Status diubah') {
                    $recentHistory->update(['notes' => $data['notes']]);
                }
            }
=======
            // Create status history (will be handled by observer, but we can also do it here for explicit control)
            $shipment->statusHistories()->create([
                'status' => $data['status'],
                'updated_by' => $userId,
                'notes' => $data['notes'] ?? 'Status diubah',
            ]);
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573

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
<<<<<<< HEAD
                // Load necessary relationships for template rendering
                $shipment->load(['destinationBranch', 'expedition']);
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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

