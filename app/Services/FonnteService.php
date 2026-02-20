<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $token;
    protected string $noToken;
    protected string $phone;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.fonnte.token', '');
        $this->noToken = config('services.fonnte.no_token', '');
        $this->phone = config('services.fonnte.phone', '');
        $this->baseUrl = config('services.fonnte.url', 'https://api.fonnte.com');
    }

    /**
     * Check if Fonnte is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->noToken) && !empty($this->phone);
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage(string $to, string $message): array
    {
        if (!$this->isConfigured()) {
            Log::error('Fonnte is not configured');
            return [
                'success' => false,
                'message' => 'Fonnte is not configured',
            ];
        }

        try {
            // Format phone number (remove + and spaces, ensure starts with country code)
            $phoneNumber = $this->formatPhoneNumber($to);

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post("{$this->baseUrl}/send", [
                'target' => $phoneNumber,
                'message' => $message,
                'countryCode' => '62', // Indonesia
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Fonnte message sent successfully', [
                    'to' => $phoneNumber,
                    'response' => $data,
                ]);

                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $data,
                ];
            }

            Log::error('Fonnte API error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Fonnte exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send WhatsApp message with media
     */
    public function sendMedia(string $to, string $message, string $mediaUrl): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Fonnte is not configured',
            ];
        }

        try {
            $phoneNumber = $this->formatPhoneNumber($to);

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post("{$this->baseUrl}/send", [
                'target' => $phoneNumber,
                'message' => $message,
                'url' => $mediaUrl,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Media message sent successfully',
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send media message',
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Fonnte media exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify device connection
     */
    public function verifyDevice(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Fonnte is not configured',
            ];
        }

        try {
            // Try to check device status using Fonnte API
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->timeout(10)->get("{$this->baseUrl}/device");

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Fonnte device verified', ['data' => $data]);
                
                return [
                    'success' => true,
                    'data' => $data,
                    'message' => 'Device terhubung dan siap digunakan',
                ];
            }

            // If device endpoint doesn't work, credentials are at least configured
            Log::warning('Fonnte device verification endpoint not available', [
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => 'Credentials dikonfigurasi, tetapi verifikasi device tidak tersedia. Pastikan device terhubung.',
            ];
        } catch (\Exception $e) {
            Log::error('Fonnte device verification exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Credentials dikonfigurasi, tetapi tidak dapat memverifikasi device: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62 (Indonesia country code)
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with country code, add 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}

