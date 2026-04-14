<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Controllers;

use App\Features\Auth\Application\Commands\LoginUser\LoginUserCommand;
use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Features\Auth\Infrastructure\Http\v1\Requests\LoginRequest;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private TokenCreatorInterface $tokenCreator,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = $this->commandBus->dispatch(new LoginUserCommand(
            email: $request->email,
            password: $request->password,
        ));

        $token = $this->tokenCreator->createFor($user);

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id()->value(),
                    'name' => $user->name()->value(),
                    'email' => $user->email()->value(),
                ],
            ],
            'token' => $token,
            'message' => 'User logged in successfully',
        ], 200);
    }
}
