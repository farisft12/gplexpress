<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware
        $middleware->web(append: [
            \App\Http\Middleware\EnsureSecureConnection::class,
        ]);
        
        // Alias middleware
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'owner' => \App\Http\Middleware\EnsureUserIsOwner::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'admin_or_owner' => \App\Http\Middleware\EnsureUserIsAdminOrOwner::class,
            'manager' => \App\Http\Middleware\EnsureUserIsManager::class,
            'kurir' => \App\Http\Middleware\EnsureUserIsKurir::class,
            'secure' => \App\Http\Middleware\EnsureSecureConnection::class,
            'validate.payment.ip' => \App\Http\Middleware\ValidatePaymentCallbackIp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
