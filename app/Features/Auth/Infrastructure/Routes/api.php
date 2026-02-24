<?php

use Illuminate\Support\Facades\Route;
use App\Features\Auth\Infrastructure\Http\v1\Controllers\LoginController;
use App\Features\Auth\Infrastructure\Http\v1\Controllers\RegisterController;

Route::post('register', RegisterController::class)->name('register');
Route::post('login', LoginController::class)->name('login');

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('user', UserController::class)->name('user');
    // Route::post('logout', LogoutController::class)->name('logout');
});