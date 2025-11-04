<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserCanAccessAdminPanel::class,
            'user' => \App\Http\Middleware\EnsureUserCanAccessUserPanel::class,
            'token.query' => \App\Http\Middleware\TokenFromQueryParameter::class,
        ]);

        // Add TokenFromQueryParameter to API middleware stack to run before Sanctum
        $middleware->prependToGroup('api', \App\Http\Middleware\TokenFromQueryParameter::class);
    })
    ->withSchedule(function ($schedule) {
        // Cleanup temporary image uploads hourly (images older than 24 hours)
        $schedule->command('temp-images:cleanup')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
