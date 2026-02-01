<?php

namespace App\Services\Shipment;

use App\Models\Shipment;
use App\Models\User;
use App\Models\CourierManifest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShipmentAssignmentService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Assign shipments to courier
     */
    public function assignShipments(array $shipmentIds, User $courier, User $assigner): void
    {
        if (!$courier->isKurir()) {
            throw new \Exception('User yang dipilih bukan kurir.');
        }

        DB::transaction(function () use ($shipmentIds, $courier, $assigner) {
            $shipments = Shipment::whereIn('id', $shipmentIds)
                ->where('status', 'pickup')
                ->whereNull('courier_id')
                ->get();

            if ($shipments->isEmpty()) {
                throw new \Exception('Tidak ada paket yang bisa di-assign.');
            }

            // Verify user can assign each shipment
            foreach ($shipments as $shipment) {
                if (!$assigner->can('assign', $shipment)) {
                    throw new \Exception('Anda tidak memiliki izin untuk assign paket: ' . $shipment->resi_number);
                }
            }

            // Create or get today's manifest
            $manifest = CourierManifest::firstOrCreate(
                [
                    'courier_id' => $courier->id,
                    'manifest_date' => today(),
                ],
                [
                    'status' => 'active',
                    'total_packages' => 0,
                ]
            );

            foreach ($shipments as $shipment) {
                // Update shipment
                $shipment->update([
                    'courier_id' => $courier->id,
                    'status' => 'diproses',
                    'assigned_at' => now(),
                ]);

                // Create status history (will be handled by observer)
                $shipment->statusHistories()->create([
                    'status' => 'diproses',
                    'updated_by' => $assigner->id,
                    'notes' => 'Ditugaskan ke kurir: ' . $courier->name,
                ]);

                // Add to manifest
                $manifest->manifestShipments()->create([
                    'shipment_id' => $shipment->id,
                ]);

                // Send notification
                try {
                    $this->notificationService->send('paket_dikirim', $shipment, ['whatsapp', 'email']);
                } catch (\Exception $e) {
                    Log::error("Failed to send notification for shipment {$shipment->resi_number}: {$e->getMessage()}");
                }
            }

            // Update manifest counts
            $manifest->update([
                'total_packages' => $manifest->manifestShipments()->count(),
            ]);
        });
    }
}

