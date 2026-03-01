<?php

//TODO to migrate to API format without Inertia
namespace App\Features\Auth\Infrastructure\Http\Controllers\Settings;

use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [];
    }

    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(): Response
    {
        return Inertia::render('settings/TwoFactor', [
            'twoFactorEnabled' => true,
            'requiresConfirmation' => false,
        ]);
    }
}
