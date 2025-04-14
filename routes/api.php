<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanModulesList;
use App\Http\Controllers\UserManagementController;

Route::get('/LoanModules', [LoanModulesList::class, 'index']);

// User validation routes
Route::post('/check-username', [UserManagementController::class, 'checkUsername'])->name('api.check-username');
Route::post('/check-email', [UserManagementController::class, 'checkEmail'])->name('api.check-email');
Route::post('/check-password', [UserManagementController::class, 'checkPassword'])->name('api.check-password');
Route::post('/check-ic-number', [UserManagementController::class, 'checkICNumber'])->name('api.check-ic-number');

