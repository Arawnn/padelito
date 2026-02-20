<?php


namespace App\Features\Auth\Infrastructure\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Foundation\Exceptions\Handler;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;

final class AuthExceptionHandler
{
    public function register(Handler $handler): void
    {
        $handler->renderable(function (UserAlreadyExistException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code'    => 'USER_ALREADY_EXISTS',
                        'message' => 'An account with this email address already exists.',
                    ],
                ], 409);
            }
        });

        $handler->renderable(function (UserNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code'    => 'USER_NOT_FOUND',
                        'message' => 'The requested user does not exist.',
                    ],
                ], 404);
            }
        });

        $handler->renderable(function (InvalidPasswordException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'code'    => 'INVALID_PASSWORD',
                        'message' => 'The password does not meet the requirements.',
                        'details' => $e->violations(),
                    ],
                ], 422);
            }
        });
    }
}