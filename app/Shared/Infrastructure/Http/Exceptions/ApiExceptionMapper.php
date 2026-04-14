<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Exceptions;

use App\Shared\Domain\Exceptions\DomainExceptionInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class ApiExceptionMapper
{
    private const SAFE_META_KEYS = ['violations'];

    private const DEFAULT_CLIENT_MESSAGE = 'An error occurred while processing your request.';

    public static function toResponse(DomainExceptionInterface $error, int $status, string $clientMessage = self::DEFAULT_CLIENT_MESSAGE): JsonResponse
    {
        $context = [
            'code'    => $error->getDomainCode(),
            'message' => $error->getMessage(),
            'meta'    => $error->getMeta(),
        ];

        if ($status >= 500) {
            Log::error('Domain invariant violation', $context + ['exception' => $error]);
        } else {
            Log::warning('Domain error', $context);
        }

        return response()->json([
            'error' => [
                'code' => $error->getDomainCode(),
                'message' => $clientMessage,
                'details' => self::filterMeta($error->getMeta()),
            ],
        ], $status);
    }

    private static function filterMeta(array $meta): array
    {
        return array_intersect_key($meta, array_flip(self::SAFE_META_KEYS));
    }
}
