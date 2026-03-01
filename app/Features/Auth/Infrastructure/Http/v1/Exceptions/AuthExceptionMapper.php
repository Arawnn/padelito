<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Exceptions;

use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\InvalidResetTokenException;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;
use App\Shared\Infrastructure\Http\Exceptions\ApiExceptionMapper;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class AuthExceptionMapper {

    private const HTTP_STATUS_MAP = [
        UserNotFoundException::class      => Response::HTTP_NOT_FOUND,
        InvalidPasswordException::class   => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidResetTokenException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        UserAlreadyExistException::class  => Response::HTTP_CONFLICT,
    ];

    public static function toResponse(DomainExceptionInterface $error): JsonResponse
    {
        $status = self::HTTP_STATUS_MAP[$error::class] ?? Response::HTTP_INTERNAL_SERVER_ERROR;
        return ApiExceptionMapper::toResponse($error, $status);
    }
}