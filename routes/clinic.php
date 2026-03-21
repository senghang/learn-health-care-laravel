<?php

use App\Http\Controllers\Clinics\AuthController;
use App\Http\Controllers\Clinics\DashboardController;
use App\Http\Controllers\Clinics\VisitController;
use App\Http\Controllers\Clinics\Workflows\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authLogin']);

Route::domain('dtc.localhost')->middleware(['userauth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/patients/search', [VisitController::class, 'searchPatients'])->name('patients.search');
    Route::resource('visits', VisitController::class)->only(['index', 'show']);

    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::get('/create', [WorkflowController::class, 'create'])->name('create');
        Route::post('/', [WorkflowController::class, 'store'])->name('store');
        Route::get('/{code}', [WorkflowController::class, 'show'])->name('show');
        Route::get('/{code}/{step}', [WorkflowController::class, 'step'])->name('step');
        Route::patch('/{code}/{step}/save', [WorkflowController::class, 'saveStep'])->name('step.save');
        Route::get('/{code}/{step}/skip', [WorkflowController::class, 'skipStep'])->name('skip');
    });

//    Route::resource('labs', LabController::class)->only(['index']);
//    Route::resource('pharmacy', PharmacyController::class)->only(['index']);
//    Route::resource('billing', BillingController::class)->only(['index']);
//
//    Route::resource('users', UserController::class)->only(['index']);
//    Route::resource('reports', ReportController::class)->only(['index']);
//    Route::resource('settings', SettingController::class)->only(['index']);
});
