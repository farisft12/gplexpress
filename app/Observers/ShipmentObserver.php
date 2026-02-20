<?php

namespace App\Observers;

use App\Models\Shipment;
use App\Models\ShipmentStatusHistory;
use App\Services\ZoneAssignmentService;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;

class ShipmentObserver
{

    /**
     * Handle the Shipment "created" event.
     */
    public function created(Shipment $shipment): void
    {
        // Auto-assign zone based on receiver address
        try {
            $zoneService = app(ZoneAssignmentService::class);
            $zoneService->assignZone($shipment);
        } catch (\Exception $e) {
            Log::warning("Failed to assign zone for shipment {$shipment->resi_number}: {$e->getMessage()}");
        }

        // Create initial status history (only if not already created by service)
        if (!$shipment->statusHistories()->where('status', $shipment->status)->exists()) {
            ShipmentStatusHistory::create([
                'shipment_id' => $shipment->id,
                'status' => $shipment->status,
                'updated_by' => auth()->id(),
                'notes' => 'Paket dibuat',
            ]);
        }
    }

    /**
     * Handle the Shipment "updated" event.
     */
    public function updated(Shipment $shipment): void
    {
        // Handle status changes
        if ($shipment->wasChanged('status')) {
            // Note: Status history creation is handled by ShipmentStatusService or other services
            // to allow custom notes. Observer only handles notifications here.
            
            // Send notification for "sampai_di_cabang_tujuan" status
            // Only send if status changed TO "sampai_di_cabang_tujuan" (not from it)
            if ($shipment->status === 'sampai_di_cabang_tujuan' && $shipment->getOriginal('status') !== 'sampai_di_cabang_tujuan') {
                $this->sendArrivalNotification($shipment);
            }
        }

        // Handle COD status changes
        if ($shipment->wasChanged('cod_status') && $shipment->cod_status === 'lunas') {
            // This will be handled by PaymentTransactionObserver or PaymentService
        }
    }

    /**
     * Send arrival notification to receiver and destination branch admin
     */
    protected function sendArrivalNotification(Shipment $shipment): void
    {
        try {
            // Reload shipment with relationships
            $shipment = $shipment->fresh(['destinationBranch.manager']);
            
            $fonnteService = app(FonnteService::class);
            
            if (!$fonnteService->isConfigured()) {
                Log::warning("Fonnte is not configured, skipping arrival notification", [
                    'shipment_id' => $shipment->id,
                ]);
                return;
            }

            // Send to receiver
            if ($shipment->receiver_phone) {
                $receiverMessage = $this->buildArrivalMessage($shipment, 'receiver');
                $result = $fonnteService->sendMessage($shipment->receiver_phone, $receiverMessage);
                
                if ($result['success']) {
                    Log::info("Arrival notification sent to receiver", [
                        'shipment_id' => $shipment->id,
                        'resi' => $shipment->resi_number,
                        'receiver_phone' => $shipment->receiver_phone,
                    ]);
                } else {
                    Log::error("Failed to send arrival notification to receiver", [
                        'shipment_id' => $shipment->id,
                        'error' => $result['message'] ?? 'Unknown error',
                    ]);
                }
            }

            // Send to destination branch admin/manager
            if ($shipment->destinationBranch) {
                $destinationBranch = $shipment->destinationBranch;
                
                // Get branch manager
                if ($destinationBranch->manager && $destinationBranch->manager->phone) {
                    $adminMessage = $this->buildArrivalMessage($shipment, 'admin');
                    $result = $fonnteService->sendMessage($destinationBranch->manager->phone, $adminMessage);
                    
                    if ($result['success']) {
                        Log::info("Arrival notification sent to branch manager", [
                            'shipment_id' => $shipment->id,
                            'branch_id' => $destinationBranch->id,
                            'manager_phone' => $destinationBranch->manager->phone,
                        ]);
                    }
                }

                // Get branch admins (if any)
                $branchAdmins = \App\Models\User::where('branch_id', $destinationBranch->id)
                    ->whereIn('role', ['admin', 'admin_cabang'])
                    ->where('status', 'active')
                    ->whereNotNull('phone')
                    ->get();

                foreach ($branchAdmins as $admin) {
                    if ($admin->phone && $admin->id !== $destinationBranch->manager_id) {
                        $adminMessage = $this->buildArrivalMessage($shipment, 'admin');
                        $result = $fonnteService->sendMessage($admin->phone, $adminMessage);
                        
                        if ($result['success']) {
                            Log::info("Arrival notification sent to branch admin", [
                                'shipment_id' => $shipment->id,
                                'admin_id' => $admin->id,
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send arrival notification", [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Build arrival notification message
     */
    protected function buildArrivalMessage(Shipment $shipment, string $recipientType = 'receiver'): string
    {
        $destinationBranch = $shipment->destinationBranch;
        $branchName = $destinationBranch ? $destinationBranch->name : 'Cabang Tujuan';
        
        if ($recipientType === 'receiver') {
            $message = "📦 *Paket Sampai di Cabang Tujuan*\n\n";
            $message .= "Halo *{$shipment->receiver_name}*,\n\n";
            $message .= "Paket Anda dengan nomor resi *{$shipment->resi_number}* telah sampai di *{$branchName}*.\n\n";
            $message .= "📋 *Detail Paket:*\n";
            $message .= "• Resi: {$shipment->resi_number}\n";
            $message .= "• Pengirim: {$shipment->sender_name}\n";
            $message .= "• Cabang Tujuan: {$branchName}\n";
            
            if ($shipment->type === 'cod') {
                $codAmount = 'Rp ' . number_format($shipment->total_cod_collectible, 0, ',', '.');
                $message .= "• Tipe: COD\n";
                $message .= "• Nilai COD: {$codAmount}\n";
                $message .= "\n💳 *Pembayaran COD dapat dilakukan di cabang tujuan.*\n";
            } else {
                $message .= "• Tipe: Non-COD\n";
            }
            
            $message .= "\nPaket siap untuk diambil atau akan segera dikirim ke alamat Anda.\n\n";
            $message .= "Terima kasih telah menggunakan layanan GPL Express! 🚀";
        } else {
            // Message for admin/manager
            $message = "📦 *Notifikasi Paket Masuk*\n\n";
            $message .= "Paket dengan nomor resi *{$shipment->resi_number}* telah sampai di *{$branchName}*.\n\n";
            $message .= "📋 *Detail Paket:*\n";
            $message .= "• Resi: {$shipment->resi_number}\n";
            $message .= "• Pengirim: {$shipment->sender_name}\n";
            $message .= "• Penerima: {$shipment->receiver_name}\n";
            $message .= "• Telepon Penerima: {$shipment->receiver_phone}\n";
            
            if ($shipment->type === 'cod') {
                $codAmount = 'Rp ' . number_format($shipment->total_cod_collectible, 0, ',', '.');
                $codStatus = $shipment->cod_status === 'lunas' ? 'Lunas' : 'Belum Lunas';
                $message .= "• Tipe: COD\n";
                $message .= "• Nilai COD: {$codAmount}\n";
                $message .= "• Status COD: {$codStatus}\n";
                $message .= "\n⚠️ *Perhatian:* Paket COD memerlukan pembayaran sebelum dapat diambil.\n";
            } else {
                $message .= "• Tipe: Non-COD\n";
            }
            
            $message .= "\nSilakan proses paket sesuai prosedur.\n\n";
            $message .= "GPL Express";
        }

        return $message;
    }

    /**
     * Handle the Shipment "deleted" event.
     */
    public function deleted(Shipment $shipment): void
    {
        // Cleanup related records
        $shipment->statusHistories()->delete();
        $shipment->manifestShipments()->delete();
    }
}
