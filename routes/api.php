<?php

use App\Http\Controllers\Api\Admin\AdminNutmorClinicBranchController;
use App\Http\Controllers\Api\Admin\AdminNutmorClinicController;
use App\Http\Controllers\Api\Admin\AdminNutmorDoctorController;
use App\Http\Controllers\Api\Admin\SuperAdminUserController;
use App\Http\Controllers\Api\Medreco\QueueController;
use App\Http\Controllers\Api\Nutmor\AppointmentController;
use App\Http\Controllers\Api\Nutmor\ClinicController;
use App\Http\Controllers\Api\Nutmor\DoctorProfileController;
use App\Http\Controllers\Api\Nutmor\DoctorSearchController;
use App\Http\Controllers\Api\Nutmor\DoctorSlotController;
use App\Http\Controllers\Api\OneHealth\AuthController;
use App\Http\Controllers\Api\OneHealth\PasswordController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('onehealth')->group(function (): void {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/forgot-password', [PasswordController::class, 'forgot']);
        Route::post('/auth/reset-password', [PasswordController::class, 'reset']);
    });

    Route::get('/nutmor/clinics/{slug}', [ClinicController::class, 'showBySlug']);
    Route::get('/nutmor/clinics', [ClinicController::class, 'index']);
    Route::get('/nutmor/doctors/search', [DoctorSearchController::class, 'index']);
    Route::get('/nutmor/doctors/{doctor}', [DoctorProfileController::class, 'show']);
    Route::get('/nutmor/doctors/{doctor}/slots', [DoctorSlotController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::prefix('onehealth')->group(function (): void {
            Route::post('/auth/logout', [AuthController::class, 'logout']);
            Route::get('/auth/me', [AuthController::class, 'me']);
        });

        Route::prefix('nutmor')->group(function (): void {
            Route::get('/appointments', [AppointmentController::class, 'index']);
            Route::post('/appointments', [AppointmentController::class, 'store']);
            Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update']);
        });

        Route::prefix('medreco')->group(function (): void {
            Route::get('/queues/today', [QueueController::class, 'today']);
        });

        Route::prefix('admin')->middleware('super_admin')->group(function (): void {
            Route::get('/users', [SuperAdminUserController::class, 'index']);

            Route::prefix('nutmor')->group(function (): void {
                Route::get('clinics', [AdminNutmorClinicController::class, 'index']);
                Route::post('clinics', [AdminNutmorClinicController::class, 'store']);
                Route::get('clinics/{clinic}', [AdminNutmorClinicController::class, 'show']);
                Route::patch('clinics/{clinic}', [AdminNutmorClinicController::class, 'update']);
                Route::delete('clinics/{clinic}', [AdminNutmorClinicController::class, 'destroy']);
                Route::post('clinics/{clinic}/branches', [AdminNutmorClinicBranchController::class, 'store']);
                Route::patch('clinic-branches/{branch}', [AdminNutmorClinicBranchController::class, 'update']);
                Route::delete('clinic-branches/{branch}', [AdminNutmorClinicBranchController::class, 'destroy']);

                Route::get('doctors', [AdminNutmorDoctorController::class, 'index']);
                Route::post('doctors', [AdminNutmorDoctorController::class, 'store']);
                Route::get('doctors/{doctor}', [AdminNutmorDoctorController::class, 'show']);
                Route::patch('doctors/{doctor}', [AdminNutmorDoctorController::class, 'update']);
                Route::delete('doctors/{doctor}', [AdminNutmorDoctorController::class, 'destroy']);
            });
        });
    });
});
