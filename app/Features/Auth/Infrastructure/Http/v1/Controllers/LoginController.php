<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Controllers;

use App\Features\Auth\Application\Commands\LoginUser\LoginUserCommand;
use App\Features\Auth\Application\Queries\GetUserByEmail\GetUserByEmailQuery;
use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Features\Auth\Infrastructure\Http\v1\Exceptions\AuthExceptionMapper;
use App\Features\Auth\Infrastructure\Http\v1\Requests\LoginRequest;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private TokenCreatorInterface $tokenCreator,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $result = $this->commandBus->dispatch(new LoginUserCommand(
            email: $request->email,
            password: $request->password,
        ));

        if (!$result->isOk()) {
            return AuthExceptionMapper::toResponse($result->error());
        }

        $user = $result->value();
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