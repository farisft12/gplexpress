<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ValidatePaymentCallbackIp
{
    /**
     * Validate payment callback IP against allowlist
     * 
     * Note: Midtrans doesn't provide official IP whitelist.
     * This middleware logs IPs for monitoring and can be configured
     * with known Midtrans IP ranges if available.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();
        $allowedIps = config('services.midtrans.allowed_callback_ips', []);

        // Log all callback IPs for security monitoring
        Log::channel('security')->warning('Payment callback received', [
            'ip' => $clientIp,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // If IP allowlist is configured, validate
        if (!empty($allowedIps) && !$this->isIpAllowed($clientIp, $allowedIps)) {
            Log::channel('security')->error('Payment callback from unauthorized IP', [
                'ip' => $clientIp,
                'allowed_ips' => $allowedIps,
            ]);

            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if IP is in allowed list
     */
    private function isIpAllowed(string $ip, array $allowedIps): bool
    {
        foreach ($allowedIps as $allowedIp) {
            // Support CIDR notation
            if (strpos($allowedIp, '/') !== false) {
                if ($this->ipInRange($ip, $allowedIp)) {
                    return true;
                }
            } else {
                if ($ip === $allowedIp) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - (int)$mask);
        $subnetLong &= $maskLong;

        return ($ipLong & $maskLong) === $subnetLong;
    }
}
