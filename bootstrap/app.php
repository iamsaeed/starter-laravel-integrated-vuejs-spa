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

        // Set tenancy middleware to run with highest priority (before SubstituteBindings)
        // This ensures the tenant database connection is initialized before route model binding
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \App\Http\Middleware\InitializeTenancyByPath::class, // Tenancy before binding
            \Illuminate\Routing\Middleware\SubstituteBindings::class, // Route model binding
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        // Expire workspace invitations hourly
        $schedule->command('invitations:expire')->hourly();

        // Cleanup temporary image uploads hourly (images older than 24 hours)
        $schedule->command('temp-images:cleanup')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
