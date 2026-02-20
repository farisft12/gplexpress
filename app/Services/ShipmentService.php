<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ShipmentService
{
    public function getShipmentsQuery($user, $filters = [])
    {
        $query = Shipment::with([
            'courier:id,name,email',
            'originBranch:id,name,code',
            'destinationBranch:id,name,code',
            'branch:id,name,code'
        ])->latest();

        // Branch scope for admin and manager
        if ($user->isAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isManager() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // Filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['resi'])) {
            $resi = $filters['resi'];
            if (strlen($resi) >= 3) {
                $query->where('resi_number', 'like', $resi . '%');
            } else {
                $query->where('resi_number', 'like', '%' . $resi . '%');
            }
        }

        return $query;
    }

    public function getBranchesForCreate($user)
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

    public function createShipment($validatedData, $user)
    {
        // Admin can only create packages from their branch
        if ($user->isAdmin() && $user->branch_id) {
            if ($validatedData['origin_branch_id'] != $user->branch_id) {
                throw new \Exception('Anda hanya dapat membuat paket dari cabang Anda sendiri.');
            }
            $branchId = $user->branch_id;
        } else {
            $branchId = $validatedData['origin_branch_id'];
        }

        $shipmentData = [
            'resi_number' => Shipment::generateResiNumber(),
            'branch_id' => $branchId,
            'origin_branch_id' => $validatedData['origin_branch_id'],
            'destination_branch_id' => $validatedData['destination_branch_id'],
            'package_type' => $validatedData['package_type'],
            'weight' => $validatedData['weight'],
            'type' => $validatedData['type'],
            'cod_amount' => $validatedData['type'] === 'cod' ? $validatedData['cod_amount'] : 0,
            'shipping_cost' => $validatedData['type'] === 'non_cod' ? ($validatedData['shipping_cost'] ?? null) : null,
            'payment_method' => null,
            'sender_name' => $validatedData['sender_name'],
            'sender_phone' => $validatedData['sender_phone'],
            'sender_address' => $validatedData['sender_address'],
            'receiver_name' => $validatedData['receiver_name'],
            'receiver_phone' => $validatedData['receiver_phone'],
            'receiver_address' => $validatedData['receiver_address'],
            'status' => 'pickup',
        ];

        if ($validatedData['type'] === 'cod') {
            $shipmentData['cod_status'] = 'belum_lunas';
        }

        $shipment = DB::transaction(function () use ($shipmentData) {
            $shipment = Shipment::create($shipmentData);

            // Auto-assign zone
            try {
                $zoneService = new \App\Services\ZoneAssignmentService();
                $zoneService->assignZone($shipment);
            } catch (\Exception $e) {
                \Log::warning("Failed to assign zone for shipment {$shipment->resi_number}: {$e->getMessage()}");
            }

            // Create status history
            $shipment->statusHistories()->create([
                'status' => 'pickup',
                'updated_by' => auth()->id(),
                'notes' => 'Paket dibuat',
            ]);

            return $shipment;
        });

        return $shipment;
    }

    public function getAssignData($user)
    {
        // Get couriers
        $kurirQuery = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'branch_id');

        if ($user->role !== 'super_admin' && $user->branch_id) {
            $kurirQuery->where('branch_id', $user->branch_id);
        }

        $kurirs = $kurirQuery->orderBy('name')->get();

        // Get unassigned shipments
        $unassignedShipmentsQuery = Shipment::where('status', 'pickup')
            ->whereNull('courier_id');

        if ($user->isAdmin() && $user->branch_id) {
            $unassignedShipmentsQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isManager() && $user->branch_id) {
            $unassignedShipmentsQuery->where('branch_id', $user->branch_id);
        }

        $unassignedShipments = $unassignedShipmentsQuery
            ->with([
                'originBranch:id,name,code',
                'destinationBranch:id,name,code'
            ])
            ->select('id', 'resi_number', 'origin_branch_id', 'destination_branch_id', 'status', 'created_at')
            ->latest()
            ->limit(500)
            ->get()
            ->groupBy(function($shipment) {
                return $shipment->origin_branch_id ?? 'no_branch';
            });

        return compact('kurirs', 'unassignedShipments');
    }

    public function assignShipments($validatedData, $user)
    {
        $courier = User::findOrFail($validatedData['courier_id']);

        if (!$courier->isKurir()) {
            throw new \Exception('User yang dipilih bukan kurir.');
        }

        DB::transaction(function () use ($validatedData, $courier, $user) {
            $shipments = Shipment::whereIn('id', $validatedData['shipment_ids'])
                ->where('status', 'pickup')
                ->whereNull('courier_id')
                ->get();

            if ($shipments->isEmpty()) {
                throw new \Exception('Tidak ada paket yang bisa di-assign.');
            }

            foreach ($shipments as $shipment) {
                if (!$user->can('assign', $shipment)) {
                    throw new \Exception('Anda tidak memiliki izin untuk assign paket: ' . $shipment->resi_number);
                }
            }

            $manifest = \App\Models\CourierManifest::firstOrCreate(
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
                $shipment->update([
                    'courier_id' => $courier->id,
                    'status' => 'diproses',
                    'assigned_at' => now(),
                ]);

                // Create manifest shipment
                \App\Models\CourierManifestShipment::create([
                    'courier_manifest_id' => $manifest->id,
                    'shipment_id' => $shipment->id,
                ]);

                // Create status history
                $shipment->statusHistories()->create([
                    'status' => 'diproses',
                    'updated_by' => $user->id,
                    'notes' => 'Paket di-assign ke kurir: ' . $courier->name,
                ]);

                // Send notification
                try {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $notificationService->send('paket_dikirim', $shipment, ['whatsapp', 'email']);
                } catch (\Exception $e) {
                    \Log::error("Failed to send notification for shipment {$shipment->resi_number}: {$e->getMessage()}");
                }
            }

            // Update manifest total
            $manifest->update([
                'total_packages' => $manifest->shipments()->count(),
            ]);
        });
    }

    public function updateShipmentStatus($shipment, $validatedData)
    {
        // Check if status transition is valid
        $allowedTransitions = [
            'pickup' => ['diproses'],
            'diproses' => ['dalam_pengiriman', 'gagal'],
            'dalam_pengiriman' => ['sampai_di_cabang_tujuan', 'gagal'],
            'sampai_di_cabang_tujuan' => ['diterima', 'gagal'],
            'diterima' => [],
            'gagal' => [],
        ];

        $currentStatus = $shipment->status;
        if (!in_array($validatedData['status'], $allowedTransitions[$currentStatus] ?? [])) {
            throw new \Exception('Perubahan status tidak valid.');
        }

        // Validate COD must be paid before status can be changed to diterima
        if ($validatedData['status'] === 'diterima' && $shipment->type === 'cod') {
            if ($shipment->cod_status !== 'lunas') {
                throw new \Exception('Paket COD harus lunas terlebih dahulu sebelum status dapat diubah menjadi diterima.');
            }
            if (empty($validatedData['payment_method'])) {
                throw new \Exception('Metode pembayaran wajib diisi untuk paket COD yang diterima.');
            }
        }

        DB::transaction(function () use ($shipment, $validatedData) {
            $oldStatus = $shipment->status;
            $updateData = [
                'status' => $validatedData['status'],
            ];

            if ($validatedData['status'] === 'dalam_pengiriman' && !$shipment->out_for_delivery_at) {
                $updateData['out_for_delivery_at'] = now();
            }

            if ($validatedData['status'] === 'diterima') {
                $updateData['delivered_at'] = now();
                if ($shipment->type === 'cod' && !empty($validatedData['payment_method'])) {
                    $updateData['payment_method'] = $validatedData['payment_method'];
                }
            }

            if ($validatedData['status'] === 'gagal') {
                $updateData['failed_at'] = now();
            }

            $shipment->update($updateData);

            // Create status history
            $shipment->statusHistories()->create([
                'status' => $validatedData['status'],
                'updated_by' => auth()->id(),
                'notes' => $validatedData['notes'] ?? 'Status diubah oleh admin',
            ]);

            // Send notifications
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $templateCode = match($validatedData['status']) {
                    'dalam_pengiriman' => 'kurir_otw',
                    'diterima' => 'paket_terkirim',
                    'gagal' => 'gagal_antar',
                    default => null,
                };

                if ($templateCode) {
                    $notificationService->send($templateCode, $shipment->fresh(), ['whatsapp', 'email']);
                }

                if ($validatedData['status'] === 'diterima' && $shipment->type === 'cod' && !empty($validatedData['payment_method'])) {
                    $notificationService->send('cod_lunas', $shipment->fresh(), ['whatsapp', 'email']);
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send notification for shipment {$shipment->resi_number}: {$e->getMessage()}");
            }
        });
    }
}