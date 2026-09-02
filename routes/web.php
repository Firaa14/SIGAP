<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PltaController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/plta/{slug}', [PltaController::class, 'show'])->name('plta.show');

Route::get('/upload', [UploadController::class, 'index'])->name('upload.index');
Route::post('/upload', [UploadController::class, 'preview'])->name('upload.preview');
