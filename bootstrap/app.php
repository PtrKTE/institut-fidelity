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
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleAuthorization::class,
            'multi_session' => \App\Http\Middleware\CheckMultiSession::class,
            'session_timeout' => \App\Http\Middleware\CheckSessionTimeout::class,
            'anti_hijack' => \App\Http\Middleware\AntiHijack::class,
            'audit' => \App\Http\Middleware\AuditRequest::class,
            'cliente_auth' => \App\Http\Middleware\ClienteAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
