<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsManager
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user || (!$user->isManager() && !$user->isOwner())) {
            abort(403, 'Akses ditolak. Hanya manager yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
