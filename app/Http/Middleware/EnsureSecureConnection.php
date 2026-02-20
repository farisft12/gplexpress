<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class EnsureSecureConnection
{
    /**
     * Force HTTPS in production and secure cookies
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force HTTPS in production
        if (app()->environment('production') && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        $response = $next($request);

        // Set secure cookie flags in production
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        return $response;
    }
}
