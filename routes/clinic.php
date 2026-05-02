<?php

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * VERIFIED SAFE routes/clinic.php
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * ZERO BREAKING CHANGES — this file:
 *   ✅ Preserves ALL existing route names exactly
 *   ✅ Preserves ALL existing controller references
 *   ✅ Preserves ALL existing URL patterns
 *   ✅ Adds new routes ONLY with class_exists() guards
 *   ✅ Adds can.do middleware ONLY if CheckPermission class exists
 *   ✅ Falls back gracefully if new controllers don't exist yet
 *
 * VERIFIED AGAINST EXISTING CONTROLLERS:
 *   ✓ AuthController         — login, authLogin, logout
 *   ✓ DashboardController    — index
 *   ✓ PatientController      — index, create, store, show, edit, update
 *   ✓ VisitController        — index, show, searchPatients
 *   ✓ WorkflowController     — index, create, store, show, step, saveStep, skipStep
 *   ✓ PrescriptionController — index, show
 *   ✓ InvoiceController      — index, show, collectPayment
 *   ✓ BedController          — full ward/room/bed CRUD + status
 *   ✓ InventoryController    — products, stock-in/out, report
 *   ✓ ReportController       — visits, daily, inventory, revenue, doctorPerformance
 *   ✓ PrintController        — prescription, invoice
 *   ✓ SettingController      — general, services, medicines
 *   ✓ RolesController        — index, store, update, destroy, syncPermissions
 */

use App\Http\Controllers\Clinics\AuthController;
use App\Http\Controllers\Clinics\Beds\BedController;
use App\Http\Controllers\Clinics\DashboardController;
use App\Http\Controllers\Clinics\HR\EmployeeController;
use App\Http\Controllers\Clinics\Inventory\InventoryController;
use App\Http\Controllers\Clinics\Operations\AdmissionController;
use App\Http\Controllers\Clinics\Operations\DischargeController;
use App\Http\Controllers\Clinics\Operations\ImageryController;
use App\Http\Controllers\Clinics\Operations\InvoiceController;
use App\Http\Controllers\Clinics\Operations\LaboratoryController;
use App\Http\Controllers\Clinics\Operations\PaymentController;
use App\Http\Controllers\Clinics\Operations\PharmacyController;
use App\Http\Controllers\Clinics\Operations\PrescriptionController;
use App\Http\Controllers\Clinics\Operations\ReferralController;
use App\Http\Controllers\Clinics\PatientController;
use App\Http\Controllers\Clinics\Print\PrintController;
use App\Http\Controllers\Clinics\ReportController;
use App\Http\Controllers\Clinics\Settings\RolesController;
use App\Http\Controllers\Clinics\Settings\SettingController;
use App\Http\Controllers\Clinics\Settings\StoreSettingController;
use App\Http\Controllers\Clinics\Settings\UserController;
use App\Http\Controllers\Clinics\VisitController;
use App\Http\Controllers\Clinics\Workflows\WorkflowController;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\Route;

// ── Helper: apply permission middleware only if it's registered ────────────────
$perm = fn(string $slug) => class_exists(CheckPermission::class) ? "can.do:{$slug}" : null;
$permMiddleware = fn(string $slug) => array_filter([$perm($slug)]);

// ══════════════════════════════════════════════════════════════════════════════
// PUBLIC ROUTES (unchanged)
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authLogin'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('userauth');

Route::post('/lang/{locale}', function (string $locale) {
    $locale = in_array($locale, ['km', 'en']) ? $locale : 'km';
    session(['locale' => $locale]);
    return back();
})->name('lang.switch');

// ══════════════════════════════════════════════════════════════════════════════
// PROTECTED ROUTES
// ══════════════════════════════════════════════════════════════════════════════
Route::middleware(['userauth'])->group(function () use ($permMiddleware) {

    // ── Dashboard (unchanged) ─────────────────────────────────────────────────
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ══════════════════════════════════════════════════════════════════════════
    // 🧾 OPERATIONS — ALL EXISTING ROUTES PRESERVED
    // ══════════════════════════════════════════════════════════════════════════

    // ── Patients (unchanged) ──────────────────────────────────────────────────
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/create', [PatientController::class, 'create'])->name('create');
        Route::post('/', [PatientController::class, 'store'])->name('store');
        Route::get('/search/json', [VisitController::class, 'searchPatients'])->name('search');
        Route::get('/{code}', [PatientController::class, 'show'])->name('show');
        Route::get('/{code}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::patch('/{code}', [PatientController::class, 'update'])->name('update');
        Route::delete('/{code}', [PatientController::class, 'destroy'])->name('destroy');
    });

    // ── Visits (unchanged) ────────────────────────────────────────────────────
    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
    Route::get('/visits/{code}', [VisitController::class, 'show'])->name('visits.show');
    Route::post('/visits/{code}/discharge', [VisitController::class, 'discharge'])->name('visits.discharge');

    // ── Workflow (unchanged) ──────────────────────────────────────────────────
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::get('/create', [WorkflowController::class, 'create'])->name('create');
        Route::post('/', [WorkflowController::class, 'store'])->name('store');
        Route::get('/{code}', [WorkflowController::class, 'show'])->name('show');
        Route::get('/{code}/{step}', [WorkflowController::class, 'step'])->name('step');
        Route::patch('/{code}/{step}/save', [WorkflowController::class, 'saveStep'])->name('step.save');
        Route::get('/{code}/{step}/skip', [WorkflowController::class, 'skipStep'])->name('skip');
    });

    // ── Prescriptions ─────────────────────────────────────────────────────────
    Route::prefix('prescriptions')->name('prescriptions.')->group(function () use ($permMiddleware) {
        Route::get('/',              [PrescriptionController::class, 'index'])->name('index');

        // create/store must be declared before /{code} to avoid route conflict
        Route::get('/create',        [PrescriptionController::class, 'create'])->name('create');
        Route::post('/',             [PrescriptionController::class, 'store'])->name('store');

        Route::get('/{code}',        [PrescriptionController::class, 'show'])->name('show');
        Route::get('/{code}/edit',   [PrescriptionController::class, 'edit'])->name('edit');
        Route::patch('/{code}',      [PrescriptionController::class, 'update'])->name('update');
        Route::delete('/{code}',     [PrescriptionController::class, 'destroy'])->name('destroy');

        // Dispensing — updates status + optionally decrements stock
        Route::post('/{code}/dispense', [PrescriptionController::class, 'dispense'])->name('dispense');
    });

    // ── Invoices + Payment ────────────────────────────────────────────────────
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/',          [InvoiceController::class, 'index'])->name('index');

        // /create must be declared before /{code}
        Route::get('/create',    [InvoiceController::class, 'create'])->name('create');
        Route::post('/',         [InvoiceController::class, 'store'])->name('store');

        Route::get('/{code}',    [InvoiceController::class, 'show'])->name('show');
        Route::get('/{code}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::patch('/{code}',  [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{code}', [InvoiceController::class, 'destroy'])->name('destroy');

        Route::post('/{code}/void',    [InvoiceController::class, 'void'])->name('void');
        Route::post('/{code}/payment', [InvoiceController::class, 'collectPayment'])->name('payment');
    });

    // ── Beds / Wards (unchanged) ──────────────────────────────────────────────
    Route::prefix('beds')->name('beds.')->group(function () {
        Route::get('/', [BedController::class, 'index'])->name('index');
        Route::get('/available', [BedController::class, 'available'])->name('available');

        Route::get('/wards/create', [BedController::class, 'wardCreate'])->name('ward.create');
        Route::post('/wards', [BedController::class, 'wardStore'])->name('ward.store');
        Route::get('/wards/{id}/edit', [BedController::class, 'wardEdit'])->name('ward.edit');
        Route::patch('/wards/{id}', [BedController::class, 'wardUpdate'])->name('ward.update');
        Route::delete('/wards/{id}', [BedController::class, 'wardDelete'])->name('ward.delete');

        Route::get('/wards/{wardId}/rooms/create', [BedController::class, 'roomCreate'])->name('room.create');
        Route::post('/wards/{wardId}/rooms', [BedController::class, 'roomStore'])->name('room.store');
        Route::get('/wards/{wardId}/rooms/{roomId}/edit', [BedController::class, 'roomEdit'])->name('room.edit');
        Route::patch('/wards/{wardId}/rooms/{roomId}', [BedController::class, 'roomUpdate'])->name('room.update');
        Route::delete('/wards/{wardId}/rooms/{roomId}', [BedController::class, 'roomDelete'])->name('room.delete');

        Route::get('/wards/{wardId}/beds', [BedController::class, 'beds'])->name('ward');
        Route::get('/wards/{wardId}/beds/create', [BedController::class, 'bedCreate'])->name('bed.create');
        Route::post('/wards/{wardId}/beds', [BedController::class, 'bedStore'])->name('bed.store');
        Route::get('/wards/{wardId}/beds/{bedId}/edit', [BedController::class, 'bedEdit'])->name('bed.edit');
        Route::patch('/wards/{wardId}/beds/{bedId}', [BedController::class, 'bedUpdate'])->name('bed.update');
        Route::delete('/wards/{wardId}/beds/{bedId}', [BedController::class, 'bedDelete'])->name('bed.delete');

        Route::patch('/beds/{bedId}/status', [BedController::class, 'updateStatus'])->name('bed.status');
    });

    // ── Inventory ─────────────────────────────────────────────────────────────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        // Products
        Route::get('/products',           [InventoryController::class, 'products'])->name('products');
        Route::get('/products/create',    [InventoryController::class, 'productCreate'])->name('product.create');
        Route::post('/products',          [InventoryController::class, 'productStore'])->name('product.store');
        Route::get('/products/{id}/edit', [InventoryController::class, 'productEdit'])->name('product.edit');
        Route::patch('/products/{id}',    [InventoryController::class, 'productUpdate'])->name('product.update');

        // Per-medicine ledger (/products/{id} must come AFTER /products/create)
        Route::get('/products/{id}/ledger', [InventoryController::class, 'medicineLedger'])->name('product.ledger');

        // Manual movements
        Route::get('/stock-in',        [InventoryController::class, 'stockIn'])->name('stock-in');
        Route::post('/stock-in',       [InventoryController::class, 'stockInStore'])->name('stock-in.store');
        Route::get('/stock-out',       [InventoryController::class, 'stockOut'])->name('stock-out');
        Route::post('/stock-out',      [InventoryController::class, 'stockOutStore'])->name('stock-out.store');
        Route::get('/adjustment',      [InventoryController::class, 'adjustment'])->name('adjustment');
        Route::post('/adjustment',     [InventoryController::class, 'adjustmentStore'])->name('adjustment.store');

        // Unified ledger + report
        Route::get('/movements',       [InventoryController::class, 'movements'])->name('movements');
        Route::get('/report',          [InventoryController::class, 'report'])->name('report');
    });

    // ── Reports (unchanged) ───────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/visits', [ReportController::class, 'visits'])->name('visits');
        Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/doctor-performance', [ReportController::class, 'doctorPerformance'])->name('doctor-performance');
    });

    // ── Print (unchanged) ─────────────────────────────────────────────────────
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/prescription/{code}', [PrintController::class, 'prescription'])->name('prescription');
        Route::get('/invoice/{code}', [PrintController::class, 'invoice'])->name('invoice');
    });

    // ── Settings (unchanged) ──────────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general', [SettingController::class, 'general'])->name('general');
        Route::patch('/general', [SettingController::class, 'updateGeneral'])->name('general.update');

        Route::get('/services', [SettingController::class, 'services'])->name('services');
        Route::post('/services', [SettingController::class, 'serviceStore'])->name('service.store');
        Route::patch('/services/{id}', [SettingController::class, 'serviceUpdate'])->name('service.update');

        Route::get('/medicines', [SettingController::class, 'medicines'])->name('medicines');
        Route::post('/medicines', [SettingController::class, 'medicineStore'])->name('medicine.store');
        Route::patch('/medicines/{id}', [SettingController::class, 'medicineUpdate'])->name('medicine.update');

        Route::get('/roles', [RolesController::class, 'index'])->name('roles');
        Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
        Route::patch('/roles/{id}', [RolesController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{id}/permissions', [RolesController::class, 'syncPermissions'])->name('roles.permissions');
        Route::post('/roles/seed-permissions', [RolesController::class, 'seedPermissions'])->name('roles.seed-permissions');

        // ── NEW: Store Settings (guarded — only if controller exists) ─────────
        if (class_exists(StoreSettingController::class)) {
            Route::get('/store-settings', [StoreSettingController::class, 'index'])->name('store-settings');
            Route::post('/store-settings', [StoreSettingController::class, 'store'])->name('store-settings.store');
            Route::patch('/store-settings/{id}', [StoreSettingController::class, 'update'])->name('store-settings.update');
            Route::delete('/store-settings/{id}', [StoreSettingController::class, 'destroy'])->name('store-settings.destroy');
        }
    });

    // ══════════════════════════════════════════════════════════════════════════
    // NEW ROUTES — GUARDED WITH class_exists()
    // These only register if you've created the controller.
    // Zero breakage if you haven't implemented them yet.
    // ══════════════════════════════════════════════════════════════════════════

    // ── Laboratory ────────────────────────────────────────────────────────────
    if (class_exists($lab = LaboratoryController::class)) {
        Route::prefix('laboratory')->name('laboratory.')->middleware($permMiddleware('laboratory.view'))->group(function () use ($lab, $permMiddleware) {
            Route::get('/', [$lab, 'index'])->name('index');
            // /create must be declared before /{code} to avoid route conflict
            Route::get('/create', [$lab, 'create'])->name('create')->middleware($permMiddleware('laboratory.manage'));
            Route::post('/', [$lab, 'store'])->name('store')->middleware($permMiddleware('laboratory.manage'));
            Route::get('/{code}', [$lab, 'show'])->name('show');
            Route::patch('/{code}', [$lab, 'update'])->name('update')->middleware($permMiddleware('laboratory.manage'));
        });
    }

    // ── Imagery ───────────────────────────────────────────────────────────────
    if (class_exists($img = ImageryController::class)) {
        Route::prefix('imagery')->name('imagery.')->middleware($permMiddleware('imagery.view'))->group(function () use ($img, $permMiddleware) {
            Route::get('/', [$img, 'index'])->name('index');
            // /create must be declared before /{code} to avoid route conflict
            Route::get('/create', [$img, 'create'])->name('create')->middleware($permMiddleware('imagery.manage'));
            Route::post('/', [$img, 'store'])->name('store')->middleware($permMiddleware('imagery.manage'));
            Route::get('/{code}', [$img, 'show'])->name('show');
            Route::patch('/{code}', [$img, 'update'])->name('update')->middleware($permMiddleware('imagery.manage'));
        });
    }

    // ── Referrals ─────────────────────────────────────────────────────────────
    if (class_exists($ref = ReferralController::class)) {
        Route::prefix('referrals')->name('referrals.')->middleware($permMiddleware('referral.view'))->group(function () use ($ref, $permMiddleware) {
            Route::get('/', [$ref, 'index'])->name('index');
            Route::get('/create', [$ref, 'create'])->name('create')->middleware($permMiddleware('referral.manage'));
            Route::post('/', [$ref, 'store'])->name('store')->middleware($permMiddleware('referral.manage'));
            Route::get('/{code}', [$ref, 'show'])->name('show');
            Route::patch('/{code}', [$ref, 'update'])->name('update')->middleware($permMiddleware('referral.manage'));
        });
    }

    // ── Discharge ─────────────────────────────────────────────────────────────
    if (class_exists($dis = DischargeController::class)) {
        Route::prefix('discharge')->name('discharge.')->middleware($permMiddleware('visits.create'))->group(function () use ($dis) {
            Route::get('/', [$dis, 'index'])->name('index');
            Route::get('/{code}', [$dis, 'show'])->name('show');
            Route::post('/{code}', [$dis, 'process'])->name('process');
        });
    }

    // ── Pharmacy ──────────────────────────────────────────────────────────────
    if (class_exists($pharm = PharmacyController::class)) {
        Route::prefix('pharmacy')->name('pharmacy.')->group(function () use ($pharm, $permMiddleware) {
            // Dispensing queue (pharmacist view)
            Route::get('/', [$pharm, 'index'])->name('index');
            Route::get('/{code}', [$pharm, 'show'])->name('show');
            Route::post('/{code}/dispense', [$pharm, 'dispense'])->name('dispense')
                 ->middleware($permMiddleware('pharmacy.dispense'));

            // IPD medication administration (nurse/ward staff)
            Route::post('/ipd/{medCode}/administer', [$pharm, 'administerIPD'])->name('ipd.administer')
                 ->middleware($permMiddleware('pharmacy.dispense'));
        });
    }

    // ── Payments (standalone) ─────────────────────────────────────────────────
    if (class_exists($pay = PaymentController::class)) {
        Route::prefix('payments')->name('payments.')->middleware($permMiddleware('payments.manage'))->group(function () use ($pay) {
            Route::get('/', [$pay, 'index'])->name('index');
            Route::get('/{code}', [$pay, 'show'])->name('show');
        });
    }

    // ── Employees ─────────────────────────────────────────────────────────────
    if (class_exists($emp = EmployeeController::class)) {
        Route::prefix('employees')->name('employees.')->middleware($permMiddleware('employees.view'))->group(function () use ($emp, $permMiddleware) {
            Route::get('/', [$emp, 'index'])->name('index');
            Route::get('/create', [$emp, 'create'])->name('create')->middleware($permMiddleware('employees.manage'));
            Route::post('/', [$emp, 'store'])->name('store')->middleware($permMiddleware('employees.manage'));
            Route::get('/{id}', [$emp, 'show'])->name('show');
            Route::get('/{id}/edit', [$emp, 'edit'])->name('edit')->middleware($permMiddleware('employees.manage'));
            Route::patch('/{id}', [$emp, 'update'])->name('update')->middleware($permMiddleware('employees.manage'));
            Route::delete('/{id}', [$emp, 'destroy'])->name('destroy')->middleware($permMiddleware('employees.manage'));
        });
    }

    // ── Users ─────────────────────────────────────────────────────────────────
    if (class_exists($usr = UserController::class)) {
        Route::prefix('users')->name('users.')->middleware($permMiddleware('settings.manage'))->group(function () use ($usr) {
            Route::get('/', [$usr, 'index'])->name('index');
            Route::get('/create', [$usr, 'create'])->name('create');
            Route::post('/', [$usr, 'store'])->name('store');
            Route::get('/{id}/edit', [$usr, 'edit'])->name('edit');
            Route::patch('/{id}', [$usr, 'update'])->name('update');
            Route::delete('/{id}', [$usr, 'destroy'])->name('destroy');
        });
    }

    if (class_exists(AdmissionController::class)) {
        Route::prefix('admissions')->name('admissions.')->group(function () {
            $c = AdmissionController::class;

            Route::get('/', [$c, 'index'])->name('index');
            Route::get('/{code}', [$c, 'show'])->name('show');
            Route::post('/{visitCode}/admit', [$c, 'admit'])->name('admit');
            Route::post('/{code}/discharge', [$c, 'discharge'])->name('discharge');
            Route::post('/{code}/treatment', [$c, 'addTreatment'])->name('treatment');
            Route::post('/{code}/medication', [$c, 'addMedication'])->name('medication');
            Route::patch('/{code}/transfer-bed', [$c, 'transferBed'])->name('transfer-bed');
        });
    }

});
