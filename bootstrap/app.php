<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {

            /*
            |--------------------------------------------------------------------------
            | ADMIN DOMAIN
            |--------------------------------------------------------------------------
            */
            Route::domain('admin.localhost')
                ->middleware(['web'])
                ->group(base_path('routes/admin.php'));

            /*
            |--------------------------------------------------------------------------
            | CLINIC SUBDOMAIN
            |--------------------------------------------------------------------------
            */
            Route::domain('{subdomain}.localhost')
                ->middleware(['web', 'clinic'])
                ->group(base_path('routes/clinic.php'));

        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
            'clinic' => \App\Http\Middleware\ClinicMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'userauth' => \App\Http\Middleware\AuthUserMiddleware::class,
            'superadminguard' => \App\Http\Middleware\AdminAuthMiddleware::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
