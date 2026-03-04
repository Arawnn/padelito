<?php

use App\Features\Auth\Infrastructure\Http\v1\Controllers\ConfirmPasswordResetController;
use App\Features\Auth\Infrastructure\Http\v1\Controllers\LoginController;
use App\Features\Auth\Infrastructure\Http\v1\Controllers\LogoutController;
use App\Features\Auth\Infrastructure\Http\v1\Controllers\RegisterController;
use App\Features\Auth\Infrastructure\Http\v1\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->prefix('api/v1')->group(function () {
    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
    Route::post('reset-password', ResetPasswordController::class)->name('reset-password');
    Route::post('reset-password/confirm', ConfirmPasswordResetController::class)->name('reset-password.confirm');
});

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1')->group(function () {
    // Route::get('user', UserController::class)->name('user');
    Route::post('logout', LogoutController::class)->name('logout');
});
