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
        
        // Clean up: remove lines that are completely empty or only contain ":" (after variable replacement)
        $lines = explode("\n", $content);
        $cleanedLines = [];
        $prevEmpty = false;
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Skip completely empty lines or lines that only contain ":" (from empty variable replacement)
            // Also skip lines that are just whitespace or only ":"
            if ($trimmed === '' || $trimmed === ':' || preg_match('/^:\s*$/', $trimmed)) {
                if (!$prevEmpty && count($cleanedLines) > 0) {
                    // Allow one empty line between sections
                    $cleanedLines[] = '';
                    $prevEmpty = true;
                }
            } else {
                $cleanedLines[] = $line;
                $prevEmpty = false;
            }
        }
        
        $content = implode("\n", $cleanedLines);
        $content = trim($content); // Remove leading/trailing whitespace

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
        $destinationBranchName = $shipment->destinationBranch?->name ?? 'GPL Expres';
        
        // External resi handling
        $externalResi = $shipment->external_resi_number ?? '';
        $externalResiLabel = '';
        $externalResiLine = '';
        if ($externalResi && $shipment->expedition) {
            $expeditionName = $shipment->expedition->code ?? $shipment->expedition->name ?? 'Ekspedisi';
            $externalResiLabel = "Resi {$expeditionName}";
            $externalResiLine = "* {$externalResiLabel}: {$externalResi}\n";
        } else {
            // If no external resi, set both to empty so the line will be removed
            $externalResi = '';
            $externalResiLabel = '';
        }
        
        // COD breakdown handling
        $codBreakdown = '';
        $typeLabel = $shipment->type === 'cod' ? 'COD' : 'Non-COD';
        
        if ($shipment->isCOD()) {
            $codNominal = 'Rp ' . number_format($shipment->cod_amount, 0, ',', '.');
            $codOngkir = 'Rp ' . number_format($shipment->cod_shipping_cost ?? 0, 0, ',', '.');
            $codAdmin = 'Rp ' . number_format($shipment->cod_admin_fee ?? 0, 0, ',', '.');
            $codTotal = 'Rp ' . number_format($shipment->total_cod_collectible, 0, ',', '.');
            
            $codBreakdown = "* Nominal COD: {$codNominal}\n* Ongkir: {$codOngkir}\n* Admin COD: {$codAdmin}\n\n*total : {$codTotal}*";
        }
        
        return array_merge([
            'resi' => $shipment->resi_number,
            'status' => $this->getStatusLabel($shipment->status),
            'eta' => $eta,
            'amount' => $shipment->isCOD() ? 'Rp ' . number_format($shipment->total_cod_collectible, 0, ',', '.') : 'N/A',
            'receiver_name' => $shipment->receiver_name,
            'receiver_phone' => $this->maskPhone($shipment->receiver_phone),
            'sender_name' => $shipment->sender_name,
            'branch_name' => $branchName,
            'destination_branch_name' => $destinationBranchName,
            'sla_status' => $slaStatus,
            'courier_name' => $shipment->courier?->name ?? 'Belum diassign',
            'type' => $typeLabel,
            'external_resi' => $externalResi,
            'external_resi_label' => $externalResiLabel,
            'external_resi_line' => $externalResiLine,
            'cod_breakdown' => $codBreakdown,
            'cod_nominal' => $shipment->isCOD() ? 'Rp ' . number_format($shipment->cod_amount, 0, ',', '.') : '',
            'cod_ongkir' => $shipment->isCOD() ? 'Rp ' . number_format($shipment->cod_shipping_cost ?? 0, 0, ',', '.') : '',
            'cod_admin' => $shipment->isCOD() ? 'Rp ' . number_format($shipment->cod_admin_fee ?? 0, 0, ',', '.') : '',
            'cod_total' => $shipment->isCOD() ? 'Rp ' . number_format($shipment->total_cod_collectible, 0, ',', '.') : '',
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





