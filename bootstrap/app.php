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
            'activity.logger' => \App\Http\Middleware\ActivityLogger::class,
            'semester.session' => \App\Http\Middleware\SemesterSession::class,
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);
        
        // Validate CSRF tokens on all requests except debug routes
        $middleware->validateCsrfTokens(except: [
            'debug-session',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
