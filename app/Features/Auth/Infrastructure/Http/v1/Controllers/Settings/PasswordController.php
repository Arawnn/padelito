<?php

namespace App\Features\Auth\Infrastructure\Http\Controllers\Settings;

use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;
use App\Features\Auth\Infrastructure\Http\v1\Exceptions\AuthExceptionMapper;
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
        $result = $this->commandBus->dispatch(new UpdateUserPasswordCommand(
            userId: $request->user()->id,
            password: $request->password,
        ));

        if (! $result->isOk()) {
            return AuthExceptionMapper::toResponse($result->error());
        }

        return response()->json([
            'message' => 'Password updated successfully',
        ], 200);
    }
}
