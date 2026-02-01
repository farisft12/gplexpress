<?php

namespace App\Services;

use App\Models\MessageTemplate;
use App\Models\Shipment;

class TemplateService
{
    /**
     * Render template with shipment data
     */
    public function render(string $templateCode, Shipment $shipment, array $additionalData = []): ?string
    {
        $template = MessageTemplate::getTemplate($templateCode, $shipment->branch_id);

        if (!$template) {
            return null;
        }

        $variables = $this->getVariables($shipment, $additionalData);
        
        $content = $template->content;
        
        // Replace variables: {{variable}}
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Get all available variables for a shipment
     */
    protected function getVariables(Shipment $shipment, array $additionalData = []): array
    {
        $eta = $shipment->eta_at ? $shipment->eta_at->format('d M Y H:i') : 'Belum tersedia';
        $slaStatus = 'N/A';
        
        if ($shipment->shipmentSla) {
            $slaStatus = match($shipment->shipmentSla->status) {
                'on_time' => 'Tepat Waktu',
                'late' => 'Terlambat',
                'failed' => 'Gagal',
                default => 'Pending',
            };
        }

        $branchName = $shipment->branch?->name ?? 'GPL Expres';
        
        return array_merge([
            'resi' => $shipment->resi_number,
            'status' => $this->getStatusLabel($shipment->status),
            'eta' => $eta,
            'amount' => $shipment->cod_amount ? 'Rp ' . number_format($shipment->cod_amount, 0, ',', '.') : 'N/A',
            'receiver_name' => $shipment->receiver_name,
            'receiver_phone' => $this->maskPhone($shipment->receiver_phone),
            'sender_name' => $shipment->sender_name,
            'branch_name' => $branchName,
            'sla_status' => $slaStatus,
            'courier_name' => $shipment->courier?->name ?? 'Belum diassign',
        ], $additionalData);
    }

    /**
     * Get human-readable status label
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

    /**
     * Mask phone number for privacy
     */
    protected function maskPhone(?string $phone): string
    {
        if (!$phone) {
            return 'N/A';
        }

        // Mask: 081234567890 -> 0812****7890
        if (strlen($phone) > 8) {
            return substr($phone, 0, 4) . str_repeat('*', strlen($phone) - 8) . substr($phone, -4);
        }

        return str_repeat('*', strlen($phone));
    }
}





