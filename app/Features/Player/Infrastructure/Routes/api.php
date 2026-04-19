<?php

use App\Features\Player\Infrastructure\Http\v1\Controllers\ChangeProfileVisibilityController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\ChangeUsernameController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\CreatePlayerProfileController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\GetMyPlayerProfileController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\GetPublicPlayerProfileController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\UpdatePlayerIdentityController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\UpdatePlayerPreferencesController;
use App\Features\Player\Infrastructure\Http\v1\Controllers\UploadPlayerAvatarController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1')->group(function () {
    Route::get('player/me', GetMyPlayerProfileController::class)->name('get-my-player-profile');
    Route::get('players/{username}', GetPublicPlayerProfileController::class)->name('get-public-player-profile');
    Route::post('player', CreatePlayerProfileController::class)->name('create-player-profile');
    Route::patch('player/me/visibility', ChangeProfileVisibilityController::class)->name('change-profile-visibility');
    Route::patch('player/me/username', ChangeUsernameController::class)->name('change-username');
    Route::patch('player/me/identity', UpdatePlayerIdentityController::class)->name('update-player-identity');
    Route::post('player/me/avatar', UploadPlayerAvatarController::class)->name('upload-player-avatar');
    Route::patch('player/me/preferences', UpdatePlayerPreferencesController::class)->name('update-player-preferences');
});
