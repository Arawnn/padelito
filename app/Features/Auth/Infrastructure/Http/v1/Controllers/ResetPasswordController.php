<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Controllers;

use App\Features\Auth\Application\Commands\SendPasswordResetEmail\SendPasswordResetEmailCommand;
use App\Features\Auth\Infrastructure\Http\v1\Requests\SendPasswordResetEmailRequest;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ResetPasswordController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(SendPasswordResetEmailRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new SendPasswordResetEmailCommand(
            email: $request->email,
        ));

        return response()->json([
            'message' => 'If an account with that email exists, a password reset link has been sent.',
        ], 200);
    }
}
