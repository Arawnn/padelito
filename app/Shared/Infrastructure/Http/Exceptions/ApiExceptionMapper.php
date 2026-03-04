<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Exceptions;

use App\Shared\Domain\Exceptions\DomainExceptionInterface;
use Illuminate\Http\JsonResponse;

final class ApiExceptionMapper
{
    public static function toResponse(DomainExceptionInterface $error, int $status): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $error->getDomainCode(),
                'message' => $error->getMessage(),
                'details' => $error->getMeta(),
            ],
        ], $status);
    }
}
