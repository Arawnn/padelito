<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Controllers;

use App\Features\Auth\Application\Commands\ConfirmPasswordReset\ConfirmPasswordResetCommand;
use App\Features\Auth\Infrastructure\Http\v1\Requests\ConfirmPasswordResetRequest;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ConfirmPasswordResetController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(ConfirmPasswordResetRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new ConfirmPasswordResetCommand(
            email: $request->email,
            token: $request->token,
            password: $request->password,
        ));

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ], 200);
    }
}
