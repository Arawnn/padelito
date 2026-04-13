<?php

use App\Features\Player\Infrastructure\Http\v1\Controllers\ChangeProfileVisibilityController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\CreatePlayerProfileController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\GetMyPlayerProfileController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\GetPublicPlayerProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1')->group(function () {
    Route::get('player/me', GetMyPlayerProfileController::class)->name('get-my-player-profile');
    Route::get('players/{username}', GetPublicPlayerProfileController::class)->name('get-public-player-profile');
    Route::post('player', CreatePlayerProfileController::class)->name('create-player-profile');
    Route::patch('player/me/visibility', ChangeProfileVisibilityController::class)->name('change-profile-visibility');
});
