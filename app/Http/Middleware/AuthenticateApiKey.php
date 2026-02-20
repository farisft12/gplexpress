<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
            ], 401);
        }

        // Validate API key against database
        $keyModel = ApiKey::where('key', $apiKey)->first();

        if (!$keyModel || !$keyModel->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        // Check IP restriction
        $clientIp = $request->ip();
        if (!$keyModel->isIpAllowed($clientIp)) {
            return response()->json([
                'success' => false,
                'message' => 'IP address not allowed',
            ], 403);
        }
        
        // Rate limiting per API key (use key's rate_limit or default 100)
        $rateLimit = $keyModel->rate_limit ?? 100;
        $key = 'api:' . $apiKey;
        if (RateLimiter::tooManyAttempts($key, $rateLimit)) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        // Update last used timestamp
        $keyModel->markAsUsed();

        // Attach API key to request for use in controllers
        $request->merge(['api_key_model' => $keyModel]);

        return $next($request);
    }
}
