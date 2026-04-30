<?php

use App\Features\Matches\Infrastructure\Http\v1\Controllers\CancelMatchController;
use App\Features\Matches\Infrastructure\Http\v1\Controllers\ConfirmMatchController;
use App\Features\Matches\Infrastructure\Http\v1\Controllers\CreateMatchController;
use App\Features\Matches\Infrastructure\Http\v1\Controllers\GetMatchController;
use App\Features\Matches\Infrastructure\Http\v1\Controllers\GetMyMatchesController;
use App\Features\Matches\Infrastructure\Http\v1\Controllers\GetMyMatchInvitationsController;
use App\Features\Matches\Infrastructure\Http\v1\Controllers\InvitePlayerToMatchController;
use App\Features\Matches\Infrastructure\Http\v1\Controllers\RespondToMatchInvitationController;
use App\Features\Matches\Infrastructure\Http\v1\Controllers\UpdateMatchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1')->group(function () {
    Route::post('matches', CreateMatchController::class)->name('create-match');
    Route::get('matches/{id}', GetMatchController::class)->name('get-match');
    Route::patch('matches/{id}', UpdateMatchController::class)->name('update-match');
    Route::post('matches/{id}/cancel', CancelMatchController::class)->name('cancel-match');
    Route::post('matches/{id}/invitations', InvitePlayerToMatchController::class)->name('invite-player-to-match');
    Route::patch('matches/{id}/invitations/{invId}', RespondToMatchInvitationController::class)->name('respond-to-match-invitation');
    Route::post('matches/{id}/confirm', ConfirmMatchController::class)->name('confirm-match');
    Route::get('player/me/matches', GetMyMatchesController::class)->name('get-my-matches');
    Route::get('player/me/invitations', GetMyMatchInvitationsController::class)->name('get-my-match-invitations');
});
