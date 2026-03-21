<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\WebController;
use  App\Http\Controllers\Admin\DashboardAdminController;
use  App\Http\Controllers\Admin\ProvinceController;
use  App\Http\Controllers\Admin\DistrictController;
use  App\Http\Controllers\Admin\CommuneController;
use  App\Http\Controllers\Admin\VillageController;
use  App\Http\Controllers\Admin\ClinicController;
use  App\Http\Controllers\Admin\ClinicUserController;


    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');

    Route::post('/login', [AdminLoginController::class, 'admin_login'])->name('admin.login.submit');

    Route::post('/logout', [AdminLoginController::class, 'admin_logout'])
        ->middleware('auth:superadmin')
        ->name('admin.logout');

    // Protected Routes
    Route::middleware(['auth:superadmin','superadminguard'])->group(function () {

        Route::get('backend/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard');

        Route::get('backend/province', [ProvinceController::class, 'index'])->name('province.list');
        Route::post('backend/province', [ProvinceController::class, 'import'])->name('provinces.import');
        Route::get('backend/province/add', [ProvinceController::class, 'add']);
        Route::post('backend/province/add', [ProvinceController::class, 'insert']);
        Route::get('backend/province/edit/{id}', [ProvinceController::class, 'edit']);
        Route::post('backend/province/edit/{id}', [ProvinceController::class, 'update']);
        Route::delete('backend/province/delete/{id}', [ProvinceController::class, 'delete'])->name('delete.province');

        Route::get('backend/district', [DistrictController::class, 'index'])->name('district.list');
        Route::post('backend/district', [DistrictController::class, 'import'])->name('districts.import');
        Route::get('backend/district/add', [DistrictController::class, 'add']);
        Route::post('backend/district/add', [DistrictController::class, 'insert']);
        Route::get('backend/district/edit/{id}', [DistrictController::class, 'edit']);
        Route::post('backend/district/edit/{id}', [DistrictController::class, 'update']);

        Route::get('backend/commune', [CommuneController::class, 'index'])->name('commune.list');
        Route::post('backend/commune', [CommuneController::class, 'import'])->name('commune.import');
        Route::get('backend/village', [VillageController::class, 'index'])->name('village.list');
        Route::post('backend/village', [VillageController::class, 'import'])->name('village.import');

        Route::get('backend/clinic', [ClinicController::class,'index']);
        Route::get('backend/clinic/add', [ClinicController::class,'add']);
        Route::post('backend/clinic/add', [ClinicController::class,'store']);
        Route::get('backend/clinic/edit/{id}', [ClinicController::class,'edit']);
        Route::post('backend/clinic/edit/{id}', [ClinicController::class,'update']);
        //Route::delete('backend/clinic/delete/{id}', [ClinicController::class,'delete'])->name('delete.clinic');

        Route::get('/clinic-logo/{id}', function ($id) {
                $path = storage_path('app/private/'.$id.'/'.$id.'_logo.jpg');
                if (!file_exists($path)) {
                    abort(404);
                }
                return response()->file($path);
            });

        Route::get('backend/user/clinic', [ClinicUserController::class,'index']);
        
    });