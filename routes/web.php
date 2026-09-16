<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\PltaController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentStatusController;

// ================================
// LOGIN
// ================================

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/equipment-status', [EquipmentStatusController::class, 'index'])
    ->name('equipment.status');
// ================================
// HALAMAN SETELAH LOGIN
// ================================

Route::middleware('auth')->group(function () {

    // DASHBOARD
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/map-data', [DashboardController::class, 'mapData'])
        ->name('dashboard.map-data');

    // PLTA
    Route::get('/plta/{slug}', [PltaController::class, 'show'])
        ->name('plta.show');

    // UPLOAD
    Route::get('/upload', [UploadController::class, 'index'])
        ->name('upload.index');

    Route::post('/upload', [UploadController::class, 'preview'])
        ->name('upload.preview');

    Route::post('/upload/confirm', [UploadController::class, 'commit'])
        ->name('upload.commit');

    Route::get('/upload/result/{history}', [UploadController::class, 'result'])
        ->name('upload.result');

    // EQUIPMENT
    Route::get('/equipment/create', [EquipmentController::class, 'create'])
        ->name('equipment.create');

    Route::post('/equipment', [EquipmentController::class, 'store'])
        ->name('equipment.store');
});
