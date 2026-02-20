<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;

class PublicApiController extends Controller
{
    /**
     * Track shipment by resi number
     * 
     * GET /api/v1/track/{resi}
     */
    public function track(string $resi): JsonResponse
    {
        // Tracking is public, so we need to disable BranchScope to allow tracking from any branch
        $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('resi_number', $resi)
            ->with([
                'courier:id,name',
                'shipmentSla.slaDefinition',
                'zone:id,name',
                'branch:id,name',
                'originBranch:id,name',
                'destinationBranch:id,name',
            ])
            ->first();

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found',
            ], 404);
        }

        // Prepare response with privacy protection
        $data = [
            'resi_number' => $shipment->resi_number,
            'status' => $shipment->status,
            'status_label' => $this->getStatusLabel($shipment->status),
            'created_at' => $shipment->created_at->toIso8601String(),
            'eta' => $shipment->eta_at?->toIso8601String(),
            'sla' => $this->getSlaData($shipment),
            'cod_status' => $this->getCodStatus($shipment),
            'timeline' => $this->getTimeline($shipment),
            'branch' => [
                'origin' => $shipment->originBranch?->name,
                'destination' => $shipment->destinationBranch?->name,
            ],
            'zone' => $shipment->zone?->name,
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get shipment status timeline
     */
    protected function getTimeline(Shipment $shipment): array
    {
        $timeline = [];

        if ($shipment->created_at) {
            $timeline[] = [
                'event' => 'created',
                'label' => 'Paket dibuat',
                'timestamp' => $shipment->created_at->toIso8601String(),
            ];
        }

        if ($shipment->assigned_at) {
            $timeline[] = [
                'event' => 'assigned',
                'label' => 'Diterima kurir',
                'timestamp' => $shipment->assigned_at->toIso8601String(),
                'courier' => $shipment->courier?->name,
            ];
        }

        if ($shipment->out_for_delivery_at) {
            $timeline[] = [
                'event' => 'out_for_delivery',
                'label' => 'Sedang dikirim',
                'timestamp' => $shipment->out_for_delivery_at->toIso8601String(),
            ];
        }

        if ($shipment->delivered_at) {
            $timeline[] = [
                'event' => 'delivered',
                'label' => 'Paket diterima',
                'timestamp' => $shipment->delivered_at->toIso8601String(),
            ];
        }

        if ($shipment->failed_at) {
            $timeline[] = [
                'event' => 'failed',
                'label' => 'Gagal antar',
                'timestamp' => $shipment->failed_at->toIso8601String(),
            ];
        }

        return $timeline;
    }

    /**
     * Get SLA data
     */
    protected function getSlaData(Shipment $shipment): ?array
    {
        if (!$shipment->shipmentSla) {
            return null;
        }

        return [
            'deadline' => $shipment->shipmentSla->deadline_at->toIso8601String(),
            'status' => $shipment->shipmentSla->status,
            'status_label' => match($shipment->shipmentSla->status) {
                'on_time' => 'Tepat Waktu',
                'late' => 'Terlambat',
                'failed' => 'Gagal',
                default => 'Pending',
            },
            'sla_type' => $shipment->shipmentSla->slaDefinition?->name,
        ];
    }

    /**
     * Get COD status (read-only)
     */
    protected function getCodStatus(Shipment $shipment): ?array
    {
        if ($shipment->type !== 'cod') {
            return null;
        }

        return [
            'amount' => $shipment->total_cod_collectible,
            'status' => $shipment->cod_status,
            'status_label' => match($shipment->cod_status) {
                'lunas' => 'Lunas',
                'belum_lunas' => 'Belum Lunas',
                default => 'Pending',
            },
            'payment_method' => $shipment->payment_method,
        ];
    }

    /**
     * Get status label
     */
    protected function getStatusLabel(string $status): string
    {
        return match($status) {
            'pickup' => 'Pickup',
            'diproses' => 'Diproses',
            'dalam_pengiriman' => 'Dalam Pengiriman',
            'sampai_di_cabang_tujuan' => 'Sampai di Cabang Tujuan',
            'diterima' => 'Diterima',
            'gagal' => 'Gagal',
            default => ucfirst($status),
        };
    }
}
