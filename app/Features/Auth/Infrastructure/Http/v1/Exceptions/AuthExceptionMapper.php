<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Exceptions;

use App\Features\Auth\Domain\Exceptions\InvalidEmailException;
use App\Features\Auth\Domain\Exceptions\InvalidNameException;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\InvalidResetTokenException;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;
use App\Shared\Infrastructure\Http\Exceptions\ApiExceptionMapper;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class AuthExceptionMapper
{
    private const HTTP_STATUS_MAP = [
        UserNotFoundException::class => Response::HTTP_NOT_FOUND,
        InvalidPasswordException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidEmailException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidNameException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidResetTokenException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        UserAlreadyExistException::class => Response::HTTP_CONFLICT,
    ];

    private const CLIENT_MESSAGES = [
        'USER_NOT_FOUND' => 'The requested user was not found.',
        'USER_ALREADY_EXISTS' => 'An account with these credentials already exists.',
        'INVALID_PASSWORD' => 'The provided password is invalid.',
        'INVALID_EMAIL' => 'The provided email is invalid.',
        'INVALID_NAME' => 'The provided name is invalid.',
        'INVALID_RESET_TOKEN' => 'The reset token is invalid or has expired.',
    ];

    public static function toResponse(DomainExceptionInterface $error): JsonResponse
    {
        $status = self::HTTP_STATUS_MAP[$error::class] ?? Response::HTTP_INTERNAL_SERVER_ERROR;
        $clientMessage = self::CLIENT_MESSAGES[$error->getDomainCode()] ?? 'An error occurred.';

        return ApiExceptionMapper::toResponse($error, $status, $clientMessage);
    }
}
