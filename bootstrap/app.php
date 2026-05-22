<?php

use App\Http\Middleware\EnsureFinanzasAccess;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Confiar en proxies (nginx, cloudflare, etc.) para que Laravel respete
        // X-Forwarded-Proto y detecte HTTPS correctamente en producción.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'finanzas' => EnsureFinanzasAccess::class,
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();