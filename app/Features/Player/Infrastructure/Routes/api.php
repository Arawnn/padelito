<?php

use App\Features\Player\Infrastructure\Http\v1\Controllers\CreatePlayerProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1')->group(function () {
    // Route::get('player/{id}', GetPlayerProfileController::class)->name('get-player-profile');
    // Route::put('player/{id}', UpdatePlayerProfileController::class)->name('update-player-profile');
    // Route::delete('player/{id}', DeletePlayerProfileController::class)->name('delete-player-profile')
    Route::post('player', CreatePlayerProfileController::class)->name('create-player-profile');
});
