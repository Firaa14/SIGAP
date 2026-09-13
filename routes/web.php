<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PltaController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

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

// ================================
// HALAMAN SETELAH LOGIN
// ================================

Route::middleware('auth')->group(function () {

    // DASHBOARD
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

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
});
