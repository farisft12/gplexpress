<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdminOrOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user || (!$user->isAdmin() && !$user->isOwner())) {
            abort(403, 'Akses ditolak. Hanya admin atau owner yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
