<?php

namespace App\Services\Shipment;

use App\Models\Shipment;
use App\Models\User;
use App\Models\Branch;
use App\Services\ZoneAssignmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShipmentService
{
    protected ZoneAssignmentService $zoneService;

    public function __construct(ZoneAssignmentService $zoneService)
    {
        $this->zoneService = $zoneService;
    }

    /**
     * Create new shipment
     */
    public function create(array $data, User $user): Shipment
    {
        // Admin can only create packages from their branch
        if ($user->isAdmin() && $user->branch_id) {
            if ($data['origin_branch_id'] != $user->branch_id) {
                throw new \Exception('Anda hanya dapat membuat paket dari cabang Anda sendiri.');
            }
            $branchId = $user->branch_id;
        } else {
            $branchId = $data['origin_branch_id'];
        }

        $shipmentData = [
            'resi_number' => Shipment::generateResiNumber(),
            'branch_id' => $branchId,
            'origin_branch_id' => $data['origin_branch_id'],
            'destination_branch_id' => $data['destination_branch_id'],
            'package_type' => $data['package_type'],
            'weight' => $data['weight'],
            'type' => $data['type'],
            'cod_amount' => $data['type'] === 'cod' ? $data['cod_amount'] : 0,
            'shipping_cost' => $data['type'] === 'non_cod' ? ($data['shipping_cost'] ?? null) : null,
            'payment_method' => null,
            'sender_name' => $data['sender_name'],
            'sender_phone' => $data['sender_phone'],
            'sender_address' => $data['sender_address'],
            'receiver_name' => $data['receiver_name'],
            'receiver_phone' => $data['receiver_phone'],
            'receiver_address' => $data['receiver_address'],
            'status' => 'pickup',
        ];

        // Only set cod_status for COD shipments
        if ($data['type'] === 'cod') {
            $shipmentData['cod_status'] = 'belum_lunas';
        }

        return DB::transaction(function () use ($shipmentData) {
            $shipment = Shipment::create($shipmentData);

            // Auto-assign zone based on receiver address
            try {
                $this->zoneService->assignZone($shipment);
            } catch (\Exception $e) {
                Log::warning("Failed to assign zone for shipment {$shipment->resi_number}: {$e->getMessage()}");
                // Continue even if zone assignment fails
            }

            // Create status history (will be handled by observer, but we do it here for explicit control)
            $shipment->statusHistories()->create([
                'status' => 'pickup',
                'updated_by' => auth()->id(),
                'notes' => 'Paket dibuat',
            ]);

            return $shipment;
        });
    }

    /**
     * Update shipment
     */
    public function update(Shipment $shipment, array $data, User $user): Shipment
    {
        // Only allow editing if status is 'pickup'
        if ($shipment->status !== 'pickup') {
            throw new \Exception('Paket yang sudah di-assign tidak dapat diubah.');
        }

        $updateData = [
            'origin_branch_id' => $data['origin_branch_id'],
            'destination_branch_id' => $data['destination_branch_id'],
            'package_type' => $data['package_type'],
            'weight' => $data['weight'],
            'type' => $data['type'],
            'cod_amount' => $data['type'] === 'cod' ? $data['cod_amount'] : 0,
            'shipping_cost' => $data['type'] === 'non_cod' ? ($data['shipping_cost'] ?? null) : null,
            'payment_method' => $shipment->payment_method, // Keep existing value
            'sender_name' => $data['sender_name'],
            'sender_phone' => $data['sender_phone'],
            'sender_address' => $data['sender_address'],
            'receiver_name' => $data['receiver_name'],
            'receiver_phone' => $data['receiver_phone'],
            'receiver_address' => $data['receiver_address'],
        ];

        // Handle cod_status based on type
        if ($data['type'] === 'cod') {
            $updateData['cod_status'] = $shipment->cod_status ?? 'belum_lunas';
        } else {
            $updateData['cod_status'] = null;
        }

        $shipment->update($updateData);

        return $shipment->fresh();
    }

    /**
     * Delete shipment
     */
    public function delete(Shipment $shipment, User $user): void
    {
        // Only allow deletion if status is 'pickup' and not assigned
        if ($shipment->status !== 'pickup' || $shipment->courier_id !== null) {
            throw new \Exception('Paket yang sudah di-assign tidak dapat dihapus.');
        }

        DB::transaction(function () use ($shipment) {
            // Delete related records
            $shipment->statusHistories()->delete();
            $shipment->manifestShipments()->delete();
            $shipment->delete();
        });
    }

    /**
     * Get branches for create form
     */
    public function getBranchesForCreate(User $user): array
    {
        $allBranches = Cache::remember('active_branches', 3600, function () {
            return Branch::where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();
        });

        // Origin branches: Admin can only see their branch, Owner can see all
        if ($user->isAdmin() && $user->branch_id) {
            $originBranches = $allBranches->where('id', $user->branch_id);
        } else {
            $originBranches = $allBranches;
        }

        // Destination branches: Admin can see all branches except their own, Owner can see all
        if ($user->isAdmin() && $user->branch_id) {
            $destinationBranches = $allBranches->where('id', '!=', $user->branch_id);
        } else {
            $destinationBranches = $allBranches;
        }

        return compact('originBranches', 'destinationBranches');
    }
}

