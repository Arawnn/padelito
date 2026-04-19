<?php

declare(strict_types=1);

namespace App\Features\Onboarding\Infrastructure\Http\v1\Controllers;

use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Features\Auth\Infrastructure\Http\v1\Requests\RegisterRequest;
use App\Features\Onboarding\Application\RegisterPlayer\RegisterPlayerCommand;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class RegisterPlayerController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private TokenCreatorInterface $tokenCreator,
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $result = $this->commandBus->dispatch(new RegisterPlayerCommand(
            name: $request->name,
            email: $request->email,
            password: $request->password,
        ));

        $token = $this->tokenCreator->createForId($result->userId);

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $result->userId,
                    'name' => $result->name,
                    'email' => $result->email,
                ],
            ],
            'token' => $token,
            'message' => 'User registered successfully',
        ], 201);
    }
}
