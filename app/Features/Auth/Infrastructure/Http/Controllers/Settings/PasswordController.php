<?php

namespace App\Features\Auth\Infrastructure\Http\Controllers\Settings;

use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Features\Auth\Infrastructure\Http\Requests\Settings\PasswordUpdateRequest;
use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;

class PasswordController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}
    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Password');
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        
        $this->commandBus->dispatch(new UpdateUserPasswordCommand(
            userId: $request->user()->id,
            password: $request->password,
        ));

        return back();
    }
}
