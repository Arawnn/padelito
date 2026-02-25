<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Controllers;

use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Features\Auth\Infrastructure\Http\v1\Exceptions\AuthExceptionMapper;
use App\Features\Auth\Infrastructure\Http\v1\Requests\RegisterRequest;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private TokenCreatorInterface $tokenCreator,
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $result = $this->commandBus->dispatch(new RegisterUserCommand(
            name: $request->name,
            email: $request->email,
            password: $request->password,
        ));

        if (!$result->isOk()) {
            return AuthExceptionMapper::toResponse($result->error());
        }

        $user = $result->value();
        $token = $this->tokenCreator->createFor($user);

        //TODO create a resource object
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