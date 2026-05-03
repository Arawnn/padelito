<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Exceptions;

use App\Features\Matches\Domain\Exceptions\CannotSwitchToSinglesWithMultiplePlayersException;
use App\Features\Matches\Domain\Exceptions\DuplicatePlayerInMatchException;
use App\Features\Matches\Domain\Exceptions\InvalidCourtNameException;
use App\Features\Matches\Domain\Exceptions\InvalidSetsDetailException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyCancelledException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyValidatedException;
use App\Features\Matches\Domain\Exceptions\MatchInvitationAlreadyRespondedException;
use App\Features\Matches\Domain\Exceptions\MatchInvitationNotFoundException;
use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Exceptions\MatchNotReadyForConfirmationException;
use App\Features\Matches\Domain\Exceptions\MatchTeamFullException;
use App\Features\Matches\Domain\Exceptions\PlayerAlreadyConfirmedException;
use App\Features\Matches\Domain\Exceptions\PlayerNotParticipantException;
use App\Features\Matches\Domain\Exceptions\PlayerNotRegisteredInAppException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;
use App\Shared\Domain\Exceptions\InvalidUuidException;
use App\Shared\Infrastructure\Http\Exceptions\ApiExceptionMapper;
use App\Shared\Infrastructure\Http\Exceptions\DomainExceptionRendererInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class MatchExceptionMapper implements DomainExceptionRendererInterface
{
    private const HTTP_STATUS_MAP = [
        MatchNotFoundException::class => Response::HTTP_NOT_FOUND,
        MatchInvitationNotFoundException::class => Response::HTTP_NOT_FOUND,
        PlayerNotRegisteredInAppException::class => Response::HTTP_NOT_FOUND,
        MatchAlreadyValidatedException::class => Response::HTTP_CONFLICT,
        MatchAlreadyCancelledException::class => Response::HTTP_CONFLICT,
        MatchInvitationAlreadyRespondedException::class => Response::HTTP_CONFLICT,
        MatchTeamFullException::class => Response::HTTP_CONFLICT,
        PlayerAlreadyConfirmedException::class => Response::HTTP_CONFLICT,
        DuplicatePlayerInMatchException::class => Response::HTTP_CONFLICT,
        UnauthorizedMatchOperationException::class => Response::HTTP_FORBIDDEN,
        PlayerNotParticipantException::class => Response::HTTP_FORBIDDEN,
        MatchNotReadyForConfirmationException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        CannotSwitchToSinglesWithMultiplePlayersException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidSetsDetailException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidCourtNameException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidUuidException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
    ];

    private const CLIENT_MESSAGES = [
        'MATCH_NOT_FOUND' => 'Match not found.',
        'MATCH_INVITATION_NOT_FOUND' => 'Match invitation not found.',
        'PLAYER_NOT_REGISTERED_IN_APP' => 'Player not found.',
        'MATCH_ALREADY_VALIDATED' => 'This match has already been validated.',
        'MATCH_ALREADY_CANCELLED' => 'This match has already been cancelled.',
        'MATCH_INVITATION_ALREADY_RESPONDED' => 'This invitation has already been responded to.',
        'MATCH_TEAM_FULL' => 'This team is already full.',
        'PLAYER_ALREADY_CONFIRMED' => 'You have already confirmed this match.',
        'DUPLICATE_PLAYER_IN_MATCH' => 'This player is already part of the match.',
        'UNAUTHORIZED_MATCH_OPERATION' => 'You are not authorized to perform this action.',
        'PLAYER_NOT_PARTICIPANT' => 'You are not a participant in this match.',
        'MATCH_NOT_READY_FOR_CONFIRMATION' => 'The match is not ready for confirmation yet.',
        'CANNOT_SWITCH_TO_SINGLES_WITH_MULTIPLE_PLAYERS' => 'Cannot switch to singles format with multiple players already assigned.',
        'INVALID_SETS_DETAIL' => 'The sets detail is invalid.',
        'INVALID_COURT_NAME' => 'The court name is invalid.',
        'INVALID_UUID' => 'The provided identifier is invalid.',
    ];

    public function handles(DomainExceptionInterface $e): bool
    {
        return isset(self::HTTP_STATUS_MAP[$e::class]);
    }

    public function render(DomainExceptionInterface $e): JsonResponse
    {
        $status = self::HTTP_STATUS_MAP[$e::class] ?? Response::HTTP_INTERNAL_SERVER_ERROR;
        $clientMessage = self::CLIENT_MESSAGES[$e->getDomainCode()] ?? 'An unexpected error occurred.';

        return ApiExceptionMapper::toResponse($e, $status, $clientMessage);
    }
}
