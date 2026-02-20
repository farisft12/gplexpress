<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\TemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public NotificationLog $notificationLog,
        public array $additionalData = []
    ) {
        $this->onQueue('notifications');
    }

    public function handle(TemplateService $templateService): void
    {
        $log = $this->notificationLog;
<<<<<<< HEAD
        
        // Refresh log to ensure we have the latest data
        $log->refresh();
        
        // Load shipment relationship if not already loaded
        // Use withoutGlobalScope to avoid BranchScope filtering
        if (!$log->relationLoaded('shipment')) {
            $log->load([
                'shipment' => function ($query) {
                    $query->withoutGlobalScope(\App\Models\Scopes\BranchScope::class);
                }
            ]);
        }
        
        // If shipment is still not loaded, manually fetch it without BranchScope
        $shipment = $log->shipment;
        if (!$shipment) {
            $shipment = \App\Models\Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->find($log->shipment_id);
        }

        if (!$shipment) {
            Log::error('SendNotificationJob: Shipment not found', [
                'log_id' => $log->id,
                'shipment_id' => $log->shipment_id,
            ]);
=======
        $shipment = $log->shipment;

        if (!$shipment) {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
            $log->markAsFailed('Shipment not found');
            return;
        }

<<<<<<< HEAD
        // Load necessary relationships for template rendering
        $shipment->load(['destinationBranch', 'expedition', 'courier']);

=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
        try {
            // Render template
            $message = $templateService->render(
                $log->template_code,
                $shipment,
                $this->additionalData
            );

            if (!$message) {
                $log->markAsFailed('Template not found or inactive');
                return;
            }

            // Send based on channel
            $success = match($log->channel) {
                'email' => $this->sendEmail($log, $message),
                'whatsapp' => $this->sendWhatsApp($log, $message),
                'sms' => $this->sendSMS($log, $message),
                default => false,
            };

            if ($success) {
                $log->update([
                    'message' => $message,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } else {
                $log->markAsFailed('Failed to send notification');
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification: {$e->getMessage()}", [
                'log_id' => $log->id,
                'exception' => $e,
            ]);
            
            $log->markAsFailed($e->getMessage());
            
            throw $e; // Re-throw for queue retry mechanism
        }
    }

    protected function sendEmail(NotificationLog $log, string $message): bool
    {
        try {
            // For now, just log. In production, integrate with email service
            // Example: Mail::to($log->recipient)->send(new ShipmentNotificationMail($message));
            
            Log::info("Email notification sent", [
                'recipient' => $log->recipient,
                'shipment_id' => $log->shipment_id,
            ]);

            // TODO: Implement actual email sending
            // For Phase 10, we're preparing the infrastructure
            return true;
        } catch (\Exception $e) {
            Log::error("Email send failed: {$e->getMessage()}");
            return false;
        }
    }

    protected function sendWhatsApp(NotificationLog $log, string $message): bool
    {
        try {
            $fonnteService = app(\App\Services\FonnteService::class);
            
            if (!$fonnteService->isConfigured()) {
                Log::warning("Fonnte is not configured, skipping WhatsApp notification", [
                    'recipient' => $log->recipient,
                    'shipment_id' => $log->shipment_id,
                ]);
                return false;
            }

            $result = $fonnteService->sendMessage($log->recipient, $message);
            
            if ($result['success']) {
                Log::info("WhatsApp notification sent successfully", [
                    'recipient' => $log->recipient,
                    'shipment_id' => $log->shipment_id,
                ]);
                return true;
            }

            Log::error("WhatsApp send failed", [
                'recipient' => $log->recipient,
                'shipment_id' => $log->shipment_id,
                'error' => $result['message'] ?? 'Unknown error',
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp send exception: {$e->getMessage()}", [
                'recipient' => $log->recipient,
                'shipment_id' => $log->shipment_id,
                'exception' => $e,
            ]);
            return false;
        }
    }

    protected function sendSMS(NotificationLog $log, string $message): bool
    {
        try {
            // For now, just log. In production, integrate with SMS gateway
            // Example: $smsService->send($log->recipient, $message);
            
            Log::info("SMS notification sent", [
                'recipient' => $log->recipient,
                'shipment_id' => $log->shipment_id,
            ]);

            // TODO: Implement actual SMS sending
            // For Phase 10, we're preparing the infrastructure
            return true;
        } catch (\Exception $e) {
            Log::error("SMS send failed: {$e->getMessage()}");
            return false;
        }
    }
}
