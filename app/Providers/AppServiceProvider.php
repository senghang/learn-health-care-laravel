<?php

namespace App\Providers;

use App\Http\Controllers\Clinics\Workflows\WorkflowStepRegistry;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the step registry as a singleton so it's only instantiated once.
        // WorkflowController receives it via constructor injection automatically.
        $this->app->singleton(WorkflowStepRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Paginator::useBootstrap(); //Configuration about pagination icon UI
    }
}
