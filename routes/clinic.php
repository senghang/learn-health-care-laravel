<?php

use App\Http\Controllers\Clinics\AuthController;
use App\Http\Controllers\Clinics\Beds\BedController;
use App\Http\Controllers\Clinics\DashboardController;
use App\Http\Controllers\Clinics\Inventory\InventoryController;
use App\Http\Controllers\Clinics\Operations\InvoiceController;
use App\Http\Controllers\Clinics\Operations\PrescriptionController;
use App\Http\Controllers\Clinics\PatientController;
use App\Http\Controllers\Clinics\Print\PrintController;
use App\Http\Controllers\Clinics\ReportController;
use App\Http\Controllers\Clinics\Settings\RolesController;
use App\Http\Controllers\Clinics\Settings\SettingController;
use App\Http\Controllers\Clinics\VisitController;
use App\Http\Controllers\Clinics\Workflows\WorkflowController;
use Illuminate\Support\Facades\Route;

// ── Auth (public) ─────────────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authLogin'])->name('login.submit');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('userauth');

// ── Language switcher (public — works before login too) ──────────────────────
Route::post('/lang/{locale}', function (string $locale) {
    $locale = in_array($locale, ['km', 'en']) ? $locale : 'km';
    session(['locale' => $locale]);
    return back();
})->name('lang.switch');

// ── Protected routes ──────────────────────────────────────────────────────────
Route::middleware(['userauth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Patients ──────────────────────────────────────────────────────────────
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/',            [PatientController::class, 'index'])->name('index');
        Route::get('/create',      [PatientController::class, 'create'])->name('create');
        Route::post('/',           [PatientController::class, 'store'])->name('store');
        Route::get('/search/json', [VisitController::class,  'searchPatients'])->name('search');
        Route::get('/{code}',      [PatientController::class, 'show'])->name('show');
        Route::get('/{code}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::patch('/{code}',    [PatientController::class, 'update'])->name('update');
    });

    // ── Visits ────────────────────────────────────────────────────────────────
    Route::get('/visits',        [VisitController::class, 'index'])->name('visits.index');
    Route::get('/visits/{code}', [VisitController::class, 'show'])->name('visits.show');

    // ── Workflow ──────────────────────────────────────────────────────────────
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/',                         [WorkflowController::class, 'index'])->name('index');
        Route::get('/create',                   [WorkflowController::class, 'create'])->name('create');
        Route::post('/',                        [WorkflowController::class, 'store'])->name('store');
        Route::get('/{code}',                   [WorkflowController::class, 'show'])->name('show');
        Route::get('/{code}/{step}',            [WorkflowController::class, 'step'])->name('step');
        Route::patch('/{code}/{step}/save',     [WorkflowController::class, 'saveStep'])->name('step.save');
        Route::get('/{code}/{step}/skip',       [WorkflowController::class, 'skipStep'])->name('skip');
    });

    // ── Operations ────────────────────────────────────────────────────────────
    // Prescriptions
    Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
        Route::get('/',       [PrescriptionController::class, 'index'])->name('index');
        Route::get('/{code}', [PrescriptionController::class, 'show'])->name('show');
    });

    // Invoices + Payment
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/',                    [InvoiceController::class, 'index'])->name('index');
        Route::get('/{code}',              [InvoiceController::class, 'show'])->name('show');
        Route::post('/{code}/payment',     [InvoiceController::class, 'collectPayment'])->name('payment');
    });

    // ── Beds / Wards ──────────────────────────────────────────────────────────
    Route::prefix('beds')->name('beds.')->group(function () {
        // Overview & JSON
        Route::get('/',           [BedController::class, 'index'])->name('index');
        Route::get('/available',  [BedController::class, 'available'])->name('available');

        // Ward CRUD
        Route::get('/wards/create',      [BedController::class, 'wardCreate'])->name('ward.create');
        Route::post('/wards',            [BedController::class, 'wardStore'])->name('ward.store');
        Route::get('/wards/{id}/edit',   [BedController::class, 'wardEdit'])->name('ward.edit');
        Route::patch('/wards/{id}',      [BedController::class, 'wardUpdate'])->name('ward.update');
        Route::delete('/wards/{id}',     [BedController::class, 'wardDelete'])->name('ward.delete');

        // Room CRUD (inside a ward)
        Route::get('/wards/{wardId}/rooms/create',   [BedController::class, 'roomCreate'])->name('room.create');
        Route::post('/wards/{wardId}/rooms',         [BedController::class, 'roomStore'])->name('room.store');
        Route::get('/wards/{wardId}/rooms/{roomId}/edit', [BedController::class, 'roomEdit'])->name('room.edit');
        Route::patch('/wards/{wardId}/rooms/{roomId}',    [BedController::class, 'roomUpdate'])->name('room.update');
        Route::delete('/wards/{wardId}/rooms/{roomId}',   [BedController::class, 'roomDelete'])->name('room.delete');

        // Bed list & CRUD (inside a ward)
        Route::get('/wards/{wardId}/beds',               [BedController::class, 'beds'])->name('ward');
        Route::get('/wards/{wardId}/beds/create',        [BedController::class, 'bedCreate'])->name('bed.create');
        Route::post('/wards/{wardId}/beds',              [BedController::class, 'bedStore'])->name('bed.store');
        Route::get('/wards/{wardId}/beds/{bedId}/edit',  [BedController::class, 'bedEdit'])->name('bed.edit');
        Route::patch('/wards/{wardId}/beds/{bedId}',     [BedController::class, 'bedUpdate'])->name('bed.update');
        Route::delete('/wards/{wardId}/beds/{bedId}',    [BedController::class, 'bedDelete'])->name('bed.delete');

        // Status toggle (AJAX)
        Route::patch('/beds/{bedId}/status', [BedController::class, 'updateStatus'])->name('bed.status');
    });

    // ── Inventory ─────────────────────────────────────────────────────────────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/products',           [InventoryController::class, 'products'])->name('products');
        Route::get('/products/create',    [InventoryController::class, 'productCreate'])->name('product.create');
        Route::post('/products',          [InventoryController::class, 'productStore'])->name('product.store');
        Route::get('/products/{id}/edit', [InventoryController::class, 'productEdit'])->name('product.edit');
        Route::patch('/products/{id}',    [InventoryController::class, 'productUpdate'])->name('product.update');
        Route::get('/stock-in',           [InventoryController::class, 'stockIn'])->name('stock-in');
        Route::post('/stock-in',          [InventoryController::class, 'stockInStore'])->name('stock-in.store');
        Route::get('/stock-out',          [InventoryController::class, 'stockOut'])->name('stock-out');
        Route::post('/stock-out',         [InventoryController::class, 'stockOutStore'])->name('stock-out.store');
        Route::get('/report',             [InventoryController::class, 'report'])->name('report');
    });

    // ── Reports ───────────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/visits',             [ReportController::class, 'visits'])->name('visits');
        Route::get('/daily',              [ReportController::class, 'daily'])->name('daily');
        Route::get('/inventory',          [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/revenue',            [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/doctor-performance', [ReportController::class, 'doctorPerformance'])->name('doctor-performance');
    });

    // ── Print ─────────────────────────────────────────────────────────────────
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/prescription/{code}', [PrintController::class, 'prescription'])->name('prescription');
        Route::get('/invoice/{code}',      [PrintController::class, 'invoice'])->name('invoice');
    });

    // ── Settings ──────────────────────────────────────────────────────────────
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

        // Roles & Permissions
        Route::get('/roles',                      [RolesController::class, 'index'])->name('roles');
        Route::post('/roles',                     [RolesController::class, 'store'])->name('roles.store');
        Route::patch('/roles/{id}',               [RolesController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}',              [RolesController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{id}/permissions',    [RolesController::class, 'syncPermissions'])->name('roles.permissions');
    });

});
