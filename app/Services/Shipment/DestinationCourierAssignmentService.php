<?php

namespace App\Services\Shipment;

use App\Models\Shipment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DestinationCourierAssignmentService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Assign shipments to destination courier for delivery and COD collection
     */
    public function assignToDestinationCourier(array $shipmentIds, User $courier, User $assigner): void
    {
        if (!$courier->isKurir()) {
            throw new \Exception('User yang dipilih bukan kurir.');
        }

        DB::transaction(function () use ($shipmentIds, $courier, $assigner) {
            // Disable BranchScope to allow access from destination branch
            $shipments = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->whereIn('id', $shipmentIds)
                ->where('status', 'sampai_di_cabang_tujuan')
                ->whereNull('destination_courier_id')
                ->get();

            if ($shipments->isEmpty()) {
                throw new \Exception('Tidak ada paket yang bisa di-assign ke kurir tujuan.');
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
                $updateData = [
                    'destination_courier_id' => $courier->id,
                    'destination_courier_assigned_at' => now(),
                    // Status tetap 'sampai_di_cabang_tujuan', tidak diubah ke 'dalam_pengiriman'
                    // destination_courier_out_for_delivery_at akan di-set ketika kurir mulai mengantar
                ];

                // For COD shipments, also set cod_collected_by
                if ($shipment->type === 'cod' && $shipment->cod_status === 'belum_lunas') {
                    $updateData['cod_collected_by'] = $courier->id;
                    $updateData['cod_collected_at'] = now();
                }

                $shipment->update($updateData);

                // Create status history - status tetap sampai_di_cabang_tujuan
                $notes = 'Paket ditugaskan ke kurir tujuan: ' . $courier->name . ' untuk pengantaran';
                if ($shipment->type === 'cod') {
                    $notes .= ' dan penagihan COD';
                }

                $shipment->statusHistories()->create([
                    'status' => $shipment->status, // Tetap sampai_di_cabang_tujuan
                    'updated_by' => $assigner->id,
                    'notes' => $notes,
                ]);

                // Send notification to courier
                try {
                    $this->notificationService->send('paket_dikirim', $shipment, ['whatsapp', 'email']);
                } catch (\Exception $e) {
                    Log::error("Failed to send notification for destination courier assignment {$shipment->resi_number}: {$e->getMessage()}");
                }
            }
        });
    }
}
