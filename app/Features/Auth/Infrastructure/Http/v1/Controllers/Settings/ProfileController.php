<?php
//TODO to migrate to API format without Inertia
namespace App\Features\Auth\Infrastructure\Http\Controllers\Settings;

use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): void
    {
        
    }

    /**
     * Update the user's profile information.
     */
    public function update($request): void
    {
        
    }

    /**
     * Delete the user's profile.
     */
    public function destroy($request): void
    {
        // $user = $request->user();

        // Auth::logout();

        // $user->delete();

        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        // return redirect('/');
    }
}
