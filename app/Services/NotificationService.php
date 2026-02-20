<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Shipment;
use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected TemplateService $templateService;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Send notification for shipment event
     */
    public function send(string $templateCode, Shipment $shipment, array $channels = ['whatsapp', 'email'], array $additionalData = []): void
    {
        $recipient = $shipment->receiver_phone ?? $shipment->receiver_email;

        if (!$recipient) {
            Log::warning("No recipient found for shipment {$shipment->resi_number}");
            return;
        }

        foreach ($channels as $channel) {
            // Determine recipient based on channel
            $channelRecipient = $this->getRecipientForChannel($shipment, $channel);
            
            if (!$channelRecipient) {
                continue;
            }

            // Create notification log
            $log = NotificationLog::create([
                'shipment_id' => $shipment->id,
                'channel' => $channel,
                'template_code' => $templateCode,
                'recipient' => $channelRecipient,
                'status' => 'pending',
            ]);

            // Dispatch job to send notification
            SendNotificationJob::dispatch($log, $additionalData);
        }
    }

    /**
     * Get recipient for specific channel
     */
    protected function getRecipientForChannel(Shipment $shipment, string $channel): ?string
    {
        return match($channel) {
            'whatsapp', 'sms' => $shipment->receiver_phone,
            'email' => $shipment->receiver_email ?? $shipment->sender_email,
            default => null,
        };
    }

    /**
     * Retry failed notifications
     */
    public function retryFailed(int $maxRetries = 3): void
    {
        $failedLogs = NotificationLog::where('status', 'failed')
            ->where('retry_count', '<', $maxRetries)
            ->where('created_at', '>=', now()->subDays(7)) // Only retry recent failures
            ->get();

        foreach ($failedLogs as $log) {
            SendNotificationJob::dispatch($log);
        }
    }
}





