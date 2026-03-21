<?php

use App\Http\Middleware\AdminAuthMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthUserMiddleware;
use App\Http\Middleware\BindSubdomainParameter;
use App\Http\Middleware\ClinicMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Admin domain
            Route::domain('admin.localhost')
                ->middleware(['web'])
                ->group(base_path('routes/admin.php'));

            // Clinic subdomain
            // Middleware order:
            //   1. clinic          — resolves currentClinic() from subdomain
            //   2. bind-subdomain  — sets URL::defaults(['subdomain'=>...])
            //                        so ALL route() calls work without passing
            //                        subdomain explicitly in views/controllers
            //   3. locale          — sets app locale from clinic setting
            Route::domain('dtc.localhost')
                ->middleware(['web', 'clinic', 'bind-subdomain', 'locale'])
                ->group(base_path('routes/clinic.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'clinic' => ClinicMiddleware::class,
            'bind-subdomain' => BindSubdomainParameter::class,
            'locale' => SetLocale::class,
            'auth' => Authenticate::class,
            'userauth' => AuthUserMiddleware::class,
            'superadminguard' => AdminAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
