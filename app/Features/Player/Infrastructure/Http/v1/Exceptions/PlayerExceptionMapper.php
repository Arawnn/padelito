<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Exceptions;

use App\Features\Player\Domain\Exceptions\InvalidAvatarUrlException;
use App\Features\Player\Domain\Exceptions\InvalidBestStreakException;
use App\Features\Player\Domain\Exceptions\InvalidBioException;
use App\Features\Player\Domain\Exceptions\InvalidCurrentStreakException;
use App\Features\Player\Domain\Exceptions\InvalidDisplayNameException;
use App\Features\Player\Domain\Exceptions\InvalidEloRatingException;
use App\Features\Player\Domain\Exceptions\InvalidTotalLossesException;
use App\Features\Player\Domain\Exceptions\InvalidTotalWinsException;
use App\Features\Player\Domain\Exceptions\InvalidUsernameException;
use App\Features\Player\Domain\Exceptions\PlayerProfileAlreadyExistException;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;
use App\Shared\Infrastructure\Http\Exceptions\ApiExceptionMapper;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class PlayerExceptionMapper
{
    private const HTTP_STATUS_MAP = [
        PlayerProfileAlreadyExistException::class => Response::HTTP_CONFLICT,
        InvalidUsernameException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidDisplayNameException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidBioException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidAvatarUrlException::class => Response::HTTP_UNPROCESSABLE_ENTITY,

        InvalidEloRatingException::class => Response::HTTP_INTERNAL_SERVER_ERROR,
        InvalidTotalWinsException::class => Response::HTTP_INTERNAL_SERVER_ERROR,
        InvalidTotalLossesException::class => Response::HTTP_INTERNAL_SERVER_ERROR,
        InvalidBestStreakException::class => Response::HTTP_INTERNAL_SERVER_ERROR,
        InvalidCurrentStreakException::class => Response::HTTP_INTERNAL_SERVER_ERROR,
    ];

    private const CLIENT_MESSAGES = [
        'PLAYER_PROFILE_ALREADY_EXISTS' => 'A player profile already exists for this account.',
        'INVALID_USERNAME' => 'The provided username is invalid.',
        'INVALID_DISPLAY_NAME' => 'The provided display name is invalid.',
        'INVALID_BIO' => 'The provided bio is invalid.',
        'INVALID_AVATAR_URL' => 'The provided avatar URL is invalid.',

        'INVALID_ELO_RATING' => 'An internal error occurred while initializing the player profile.',
        'INVALID_TOTAL_WINS' => 'An internal error occurred while initializing the player profile.',
        'INVALID_TOTAL_LOSSES' => 'An internal error occurred while initializing the player profile.',
        'INVALID_BEST_STREAK' => 'An internal error occurred while initializing the player profile.',
        'INVALID_CURRENT_STREAK' => 'An internal error occurred while initializing the player profile.',
    ];

    public static function toResponse(DomainExceptionInterface $error): JsonResponse
    {
        $status = self::HTTP_STATUS_MAP[$error::class] ?? Response::HTTP_INTERNAL_SERVER_ERROR;
        $clientMessage = self::CLIENT_MESSAGES[$error->getDomainCode()] ?? 'An unexpected error occurred.';

        return ApiExceptionMapper::toResponse($error, $status, $clientMessage);
    }
}
