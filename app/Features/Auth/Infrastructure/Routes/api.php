<?php

use App\Features\Auth\Infrastructure\Http\v1\Controllers\LoginController;
use App\Features\Auth\Infrastructure\Http\v1\Controllers\LogoutController;
use App\Features\Auth\Infrastructure\Http\v1\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;



Route::middleware(['api'])->prefix('api/v1')->group(function() {
    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');    
});


Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1')->group(function () {
    // Route::get('user', UserController::class)->name('user');
    Route::post('logout', LogoutController::class)->name('logout');
});