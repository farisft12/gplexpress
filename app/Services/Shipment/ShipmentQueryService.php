<?php

namespace App\Services\Shipment;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ShipmentQueryService
{
    /**
     * Build base query with eager loading and branch scope
     */
    public function buildBaseQuery(User $user, ?string $direction = null): Builder
    {
        $query = Shipment::with([
            'courier:id,name,email',
            'originBranch:id,name,code',
            'destinationBranch:id,name,code',
            'branch:id,name,code'
        ])->latest();

        // Branch scope for admin and manager
        if (($user->isAdmin() || $user->isManager()) && $user->branch_id) {
            // Filter by direction: outgoing (paket keluar) or incoming (paket masuk)
            if ($direction === 'outgoing') {
                // Paket keluar: origin_branch_id = current branch
                $query->where('origin_branch_id', $user->branch_id);
            } elseif ($direction === 'incoming') {
                // Paket masuk: destination_branch_id = current branch
                $query->where('destination_branch_id', $user->branch_id);
            } else {
                // Default: show both (paket keluar dan masuk)
                $query->where(function($q) use ($user) {
                    $q->where('origin_branch_id', $user->branch_id)
                      ->orWhere('destination_branch_id', $user->branch_id);
                });
            }
        }
        // For super_admin/owner: no branch filtering (can see all)

        return $query;
    }

    /**
     * Apply filters to query
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['resi'])) {
            $resi = $filters['resi'];
            // Use index-friendly search: prefix match is faster than LIKE with %
            if (strlen($resi) >= 3) {
                $query->where('resi_number', 'like', $resi . '%');
            } else {
                $query->where('resi_number', 'like', '%' . $resi . '%');
            }
        }

        if (!empty($filters['courier_id'])) {
            $query->where('courier_id', $filters['courier_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Get shipments with pagination
     */
    public function getPaginated(User $user, array $filters = [], int $perPage = 20)
    {
        $direction = $filters['direction'] ?? null;
        $query = $this->buildBaseQuery($user, $direction);
        $query = $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Get unassigned shipments for assignment
     */
    public function getUnassignedShipments(User $user, int $limit = 500)
    {
        $query = Shipment::where('status', 'pickup')
            ->whereNull('courier_id')
            ->with([
                'originBranch:id,name,code',
                'destinationBranch:id,name,code'
            ])
            ->select('id', 'resi_number', 'origin_branch_id', 'destination_branch_id', 'status', 'created_at')
            ->latest()
            ->limit($limit);

        // Branch scope
        if ($user->isAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isManager() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query->get()->groupBy(function($shipment) {
            return $shipment->origin_branch_id ?? 'no_branch';
        });
    }
}

