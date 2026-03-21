<?php

namespace App\Providers;

use App\Http\Controllers\Clinics\Workflows\WorkflowStepRegistry;
use App\Services\PrintService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Workflow step registry — singleton so steps are only instantiated once
        $this->app->singleton(WorkflowStepRegistry::class);

        // Print service — singleton (stateless, safe to share)
        $this->app->singleton(PrintService::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // Share currentClinic to all views so layout can use {{ currentClinic()->name }}
        view()->composer('*', function ($view) {
            if (app()->has('currentClinic')) {
                $view->with('_clinic', app('currentClinic'));
            }
        });
    }
}
