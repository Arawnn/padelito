<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use App\Features\Auth\Infrastructure\Http\Requests\RegisterRequest;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Queries\GetUserByEmail\GetUserByEmailQuery;

class RegisterController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private TokenCreatorInterface $tokenCreator,
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new RegisterUserCommand(
            name: $request->name,
            email: $request->email,
            password: $request->password,
        ));

        $user = $this->queryBus->ask(new GetUserByEmailQuery($request->email));
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

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
            'message' => 'User registered successfully',
        ], 201);
    }
}