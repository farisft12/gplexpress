<?php

namespace App\Services\Dashboard;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Get admin dashboard metrics
     */
    public function getMetrics(User $user): array
    {
        $today = today();
        
        // Build base query with branch scope
        $baseQuery = Shipment::query();
        
        // Branch scope untuk admin
        if ($user->isAdmin() && $user->branch_id) {
            $baseQuery->where('branch_id', $user->branch_id);
        }
        
        // Optimize: Use single query with conditional aggregation
        $stats = $baseQuery
            ->selectRaw('
                COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as total_paket_hari_ini,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? AND type = \'cod\' THEN (cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)) END), 0) as total_cod_hari_ini,
                COUNT(CASE WHEN status IN (\'diproses\', \'dalam_pengiriman\') THEN 1 END) as paket_dalam_pengantaran,
                COUNT(CASE WHEN status = \'gagal\' AND DATE(created_at) = ? THEN 1 END) as paket_gagal
            ', [$today->format('Y-m-d'), $today->format('Y-m-d'), $today->format('Y-m-d')])
            ->first();
        
        return [
            'total_paket_hari_ini' => $stats->total_paket_hari_ini ?? 0,
            'total_cod_hari_ini' => $stats->total_cod_hari_ini ?? 0,
            'paket_dalam_pengantaran' => $stats->paket_dalam_pengantaran ?? 0,
            'paket_gagal' => $stats->paket_gagal ?? 0,
        ];
    }
}

