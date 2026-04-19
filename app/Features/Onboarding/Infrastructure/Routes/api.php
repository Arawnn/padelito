<?php

use App\Features\Onboarding\Infrastructure\Http\v1\Controllers\RegisterPlayerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->prefix('api/v1')->group(function () {
    Route::post('register', RegisterPlayerController::class)->name('register');
});
