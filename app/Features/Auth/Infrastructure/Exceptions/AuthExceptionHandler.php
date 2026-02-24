<?php


namespace App\Features\Auth\Infrastructure\Exceptions;

use Illuminate\Http\Request;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;
use Illuminate\Foundation\Configuration\Exceptions;
final class AuthExceptionHandler
{
    public function register(Exceptions $exceptions): void
    {
        $exceptions->renderable(function (UserAlreadyExistException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'code'    => 'USER_ALREADY_EXISTS',
                        'message' => 'An account with this email address already exists.',
                    ],
                ], 409);
            }
        });

        $exceptions->renderable(function (UserNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'code'    => 'USER_NOT_FOUND',
                        'message' => 'The requested user does not exist.',
                    ],
                ], 404);
            }
        });

        $exceptions->renderable(function (InvalidPasswordException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
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