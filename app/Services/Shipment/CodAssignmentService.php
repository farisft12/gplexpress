<?php

namespace App\Services\Shipment;

use App\Models\Shipment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CodAssignmentService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Assign COD shipments to courier for collection
     */
    public function assignCodShipments(array $shipmentIds, User $courier, User $assigner): void
    {
        if (!$courier->isKurir()) {
            throw new \Exception('User yang dipilih bukan kurir.');
        }

        DB::transaction(function () use ($shipmentIds, $courier, $assigner) {
            $shipments = Shipment::whereIn('id', $shipmentIds)
                ->where('status', 'sampai_di_cabang_tujuan')
                ->where('type', 'cod')
                ->where('cod_status', 'belum_lunas')
                ->whereNull('cod_collected_by')
                ->get();

            if ($shipments->isEmpty()) {
                throw new \Exception('Tidak ada paket COD yang bisa di-assign.');
            }

            // Verify courier is from destination branch
            foreach ($shipments as $shipment) {
                if ($shipment->destination_branch_id !== $courier->branch_id) {
                    throw new \Exception('Kurir harus dari cabang tujuan paket: ' . $shipment->resi_number);
                }

                // Verify assigner can assign (admin/manager from destination branch)
                if ($assigner->branch_id !== $shipment->destination_branch_id) {
                    throw new \Exception('Anda tidak memiliki izin untuk assign paket: ' . $shipment->resi_number);
                }
            }

            foreach ($shipments as $shipment) {
                $shipment->update([
                    'cod_collected_by' => $courier->id,
                    'cod_collected_at' => now(),
                ]);

                // Create status history
                $shipment->statusHistories()->create([
                    'status' => $shipment->status,
                    'updated_by' => $assigner->id,
                    'notes' => 'Paket COD ditugaskan ke kurir: ' . $courier->name . ' untuk penagihan COD',
                ]);

                // Send notification to courier
                try {
                    $this->notificationService->send('paket_cod_assigned', $shipment, ['whatsapp', 'email']);
                } catch (\Exception $e) {
                    Log::error("Failed to send notification for COD assignment {$shipment->resi_number}: {$e->getMessage()}");
                }
            }
        });
    }
}
