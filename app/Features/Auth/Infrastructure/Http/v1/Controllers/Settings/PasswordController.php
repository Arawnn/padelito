<?php

namespace App\Features\Auth\Infrastructure\Http\v1\Controllers\Settings;

use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;
use App\Features\Auth\Infrastructure\Http\v1\Requests\Settings\PasswordUpdateRequest;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class PasswordController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new UpdateUserPasswordCommand(
            userId: $request->user()->id,
            password: $request->password,
        ));

        return response()->json([
            'message' => 'Password updated successfully',
        ], 200);
    }
}
