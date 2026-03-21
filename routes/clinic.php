<?php

use App\Http\Controllers\Clinics\AuthController;
use App\Http\Controllers\Clinics\DashboardController;
use App\Http\Controllers\Clinics\ReportController;
use App\Http\Controllers\Clinics\VisitController;
use App\Http\Controllers\Clinics\Workflows\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/login',  [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authLogin'])->name('login.submit');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

Route::middleware(['userauth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/patients/search', [VisitController::class, 'searchPatients'])->name('patients.search');

    Route::resource('visits', VisitController::class)->only(['index', 'show']);

    // ── Reports ──────────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/visits', [ReportController::class, 'visits'])->name('visits');
        Route::get('/daily',  [ReportController::class, 'daily'])->name('daily');
    });

    Route::prefix('workflow')->name('workflow.')->group(function () {

        Route::get('/',       [WorkflowController::class, 'index'])->name('index');
        Route::get('/create', [WorkflowController::class, 'create'])->name('create');
        Route::post('/',      [WorkflowController::class, 'store'])->name('store');

        // More specific routes MUST come before the /{code} catch-all
        Route::get('/{code}/{step}/skip',   [WorkflowController::class, 'skipStep'])->name('skip');
        Route::patch('/{code}/{step}/save', [WorkflowController::class, 'saveStep'])->name('step.save');
        Route::get('/{code}/{step}',        [WorkflowController::class, 'step'])->name('step');

        // /{code} catch-all — must be last
        Route::get('/{code}', [WorkflowController::class, 'show'])->name('show');
    });

});
