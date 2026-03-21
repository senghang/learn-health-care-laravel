<?php

use App\Http\Controllers\Clinics\AuthController;
use App\Http\Controllers\Clinics\Beds\BedController;
use App\Http\Controllers\Clinics\DashboardController;
use App\Http\Controllers\Clinics\PatientController;
use App\Http\Controllers\Clinics\Print\PrintController;
use App\Http\Controllers\Clinics\ReportController;
use App\Http\Controllers\Clinics\Settings\SettingController;
use App\Http\Controllers\Clinics\VisitController;
use App\Http\Controllers\Clinics\Workflows\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/login',  [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authLogin']);
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('userauth');

Route::middleware(['userauth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Patients
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/',              [PatientController::class, 'index'])->name('index');
        Route::get('/create',        [PatientController::class, 'create'])->name('create');
        Route::post('/',             [PatientController::class, 'store'])->name('store');
        Route::get('/search/json',   [PatientController::class, 'search'])->name('search');
        Route::get('/{code}',        [PatientController::class, 'show'])->name('show');
        Route::get('/{code}/edit',   [PatientController::class, 'edit'])->name('edit');
        Route::patch('/{code}',      [PatientController::class, 'update'])->name('update');
    });

    // Visits
    Route::resource('visits', VisitController::class)->only(['index', 'show']);

    // Workflow
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/create',               [WorkflowController::class, 'create'])->name('create');
        Route::post('/',                    [WorkflowController::class, 'store'])->name('store');
        Route::get('/{code}',               [WorkflowController::class, 'show'])->name('show');
        Route::get('/{code}/{step}',        [WorkflowController::class, 'step'])->name('step');
        Route::patch('/{code}/{step}/save', [WorkflowController::class, 'saveStep'])->name('step.save');
        Route::get('/{code}/{step}/skip',   [WorkflowController::class, 'skipStep'])->name('skip');
    });

    // Beds
    Route::prefix('beds')->name('beds.')->group(function () {
        Route::get('/',                          [BedController::class, 'index'])->name('index');
        Route::get('/available',                 [BedController::class, 'available'])->name('available');
        Route::get('/wards/create',              [BedController::class, 'wardCreate'])->name('ward.create');
        Route::post('/wards',                    [BedController::class, 'wardStore'])->name('ward.store');
        Route::get('/wards/{id}/edit',           [BedController::class, 'wardEdit'])->name('ward.edit');
        Route::patch('/wards/{id}',              [BedController::class, 'wardUpdate'])->name('ward.update');
        Route::get('/wards/{wardId}/beds',       [BedController::class, 'beds'])->name('ward');
        Route::post('/wards/{wardId}/beds',      [BedController::class, 'bedStore'])->name('bed.store');
        Route::patch('/beds/{bedId}/status',     [BedController::class, 'updateStatus'])->name('bed.status');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/visits', [ReportController::class, 'visits'])->name('visits');
        Route::get('/daily',  [ReportController::class, 'daily'])->name('daily');
    });

    // Print
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/prescription/{code}', [PrintController::class, 'prescription'])->name('prescription');
        Route::get('/invoice/{code}',      [PrintController::class, 'invoice'])->name('invoice');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general',             [SettingController::class, 'general'])->name('general');
        Route::patch('/general',           [SettingController::class, 'updateGeneral'])->name('general.update');
        Route::get('/templates',           [SettingController::class, 'templates'])->name('templates');
        Route::get('/templates/create',    [SettingController::class, 'templateCreate'])->name('template.create');
        Route::post('/templates',          [SettingController::class, 'templateStore'])->name('template.store');
        Route::get('/templates/{id}/edit', [SettingController::class, 'templateEdit'])->name('template.edit');
        Route::patch('/templates/{id}',    [SettingController::class, 'templateUpdate'])->name('template.update');
        Route::get('/services',            [SettingController::class, 'services'])->name('services');
        Route::post('/services',           [SettingController::class, 'serviceStore'])->name('service.store');
        Route::patch('/services/{id}',     [SettingController::class, 'serviceUpdate'])->name('service.update');
        Route::get('/medicines',           [SettingController::class, 'medicines'])->name('medicines');
        Route::post('/medicines',          [SettingController::class, 'medicineStore'])->name('medicine.store');
        Route::patch('/medicines/{id}',    [SettingController::class, 'medicineUpdate'])->name('medicine.update');
    });
});
