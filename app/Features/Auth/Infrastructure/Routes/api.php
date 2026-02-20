<?php

use App\Features\Auth\Infrastructure\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('register', RegisterController::class)->name('register');
// Route::post('login', LoginController::class)->name('login');

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('user', UserController::class)->name('user');
    // Route::post('logout', LogoutController::class)->name('logout');
});