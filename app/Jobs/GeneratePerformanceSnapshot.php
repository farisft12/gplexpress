<?php

namespace App\Jobs;

use App\Models\CourierPerformanceSnapshot;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GeneratePerformanceSnapshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $periodType;
    protected Carbon $periodDate;
    protected ?int $courierId;
    protected ?int $branchId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $periodType, Carbon $periodDate, ?int $courierId = null, ?int $branchId = null)
    {
        $this->periodType = $periodType;
        $this->periodDate = $periodDate;
        $this->courierId = $courierId;
        $this->branchId = $branchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $dateRange = $this->getDateRange();

        $query = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active');

        if ($this->courierId) {
            $query->where('id', $this->courierId);
        }

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        $couriers = $query->get();

        foreach ($couriers as $courier) {
            $metrics = $this->calculateMetrics($courier, $dateRange);
            
            CourierPerformanceSnapshot::updateOrCreate(
                [
                    'courier_id' => $courier->id,
                    'period_type' => $this->periodType,
                    'period_date' => $this->periodDate->format('Y-m-d'),
                ],
                [
                    'branch_id' => $courier->branch_id,
                    'metrics' => $metrics,
                    'generated_at' => now(),
                ]
            );
        }
    }

    /**
     * Get date range for the period
     */
    protected function getDateRange(): array
    {
        $start = $this->periodDate->copy();
        $end = $this->periodDate->copy();

        switch ($this->periodType) {
            case 'daily':
                $start->startOfDay();
                $end->endOfDay();
                break;
            case 'weekly':
                $start->startOfWeek();
                $end->endOfWeek();
                break;
            case 'monthly':
                $start->startOfMonth();
                $end->endOfMonth();
                break;
        }

        return [$start, $end];
    }

    /**
     * Calculate performance metrics for courier
     */
    protected function calculateMetrics(User $courier, array $dateRange): array
    {
        [$startDate, $endDate] = $dateRange;

        $shipments = Shipment::where('courier_id', $courier->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalPaket = $shipments->count();
        $delivered = $shipments->where('status', 'diterima');
        $failed = $shipments->where('status', 'gagal');

        // SLA metrics
        $onTimeCount = 0;
        $lateCount = 0;
        $failedCount = $failed->count();

        foreach ($delivered as $shipment) {
            if ($shipment->shipmentSla) {
                if ($shipment->shipmentSla->isOnTime()) {
                    $onTimeCount++;
                } elseif ($shipment->shipmentSla->isLate()) {
                    $lateCount++;
                }
            }
        }

        // Calculate percentages
        $onTimePercentage = $totalPaket > 0 ? ($onTimeCount / $totalPaket) * 100 : 0;
        $latePercentage = $totalPaket > 0 ? ($lateCount / $totalPaket) * 100 : 0;
        $failedPercentage = $totalPaket > 0 ? ($failedCount / $totalPaket) * 100 : 0;

        // Average delivery duration
        $avgDuration = $this->calculateAvgDeliveryDuration($delivered);

        // COD collection accuracy
        $codAccuracy = $this->calculateCodAccuracy($courier, $dateRange);

        return [
            'total_paket' => $totalPaket,
            'on_time_count' => $onTimeCount,
            'late_count' => $lateCount,
            'failed_count' => $failedCount,
            'on_time_percentage' => round($onTimePercentage, 2),
            'late_percentage' => round($latePercentage, 2),
            'failed_percentage' => round($failedPercentage, 2),
            'avg_delivery_duration_hours' => round($avgDuration, 2),
            'cod_collection_accuracy' => round($codAccuracy, 2),
        ];
    }

    /**
     * Calculate average delivery duration in hours
     */
    protected function calculateAvgDeliveryDuration($deliveredShipments): float
    {
        $durations = [];
        
        foreach ($deliveredShipments as $shipment) {
            if ($shipment->assigned_at && $shipment->delivered_at) {
                $duration = Carbon::parse($shipment->delivered_at)
                    ->diffInHours(Carbon::parse($shipment->assigned_at));
                $durations[] = $duration;
            }
        }

        return count($durations) > 0 ? array_sum($durations) / count($durations) : 0;
    }

    /**
     * Calculate COD collection accuracy
     */
    protected function calculateCodAccuracy(User $courier, array $dateRange): float
    {
        [$startDate, $endDate] = $dateRange;

        $codShipments = Shipment::where('courier_id', $courier->id)
            ->where('type', 'cod')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        if ($codShipments->isEmpty()) {
            return 100.0; // No COD shipments = 100% accuracy
        }

        $collected = $codShipments->where('cod_status', 'lunas')->count();
        $total = $codShipments->count();

        return ($collected / $total) * 100;
    }
}
