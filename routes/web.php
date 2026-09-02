<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PltaController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/plta/{slug}', [PltaController::class, 'show'])
    ->name('plta.show');

Route::get('/upload', [UploadController::class, 'index'])
    ->name('upload.index');

Route::post('/upload', [UploadController::class, 'preview'])
    ->name('upload.preview');