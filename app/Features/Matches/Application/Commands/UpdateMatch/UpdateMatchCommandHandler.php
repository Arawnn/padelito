<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\UpdateMatch;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Enums\MatchFormatEnum;
use App\Features\Matches\Domain\Enums\MatchTypeEnum;
use App\Features\Matches\Domain\Events\MatchConfirmationsReset;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyCancelledException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyValidatedException;
use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\CourtName;
use App\Features\Matches\Domain\ValueObjects\MatchFormat;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchType;
use App\Features\Matches\Domain\ValueObjects\Notes;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use App\Features\Matches\Domain\ValueObjects\SetsToWin;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use DateTimeImmutable;

final readonly class UpdateMatchCommandHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(UpdateMatchCommand $command): PadelMatch
    {
        $matchId = MatchId::fromString($command->matchId);
        $requesterId = PlayerId::fromString($command->requesterId);

        $match = $this->matchRepository->findById($matchId);
        if ($match === null) {
            throw MatchNotFoundException::create();
        }

        if (! $match->isCreator($requesterId)) {
            throw UnauthorizedMatchOperationException::create();
        }

        if ($match->status()->isValidated()) {
            throw MatchAlreadyValidatedException::create();
        }

        if ($match->status()->isCancelled()) {
            throw MatchAlreadyCancelledException::create();
        }

        $this->applyUpdates($match, $command);

        $domainEvents = $match->pullDomainEvents();

        $confirmationsReset = false;
        foreach ($domainEvents as $event) {
            if ($event instanceof MatchConfirmationsReset) {
                $confirmationsReset = true;
            }
        }

        if ($confirmationsReset) {
            $this->matchRepository->deleteConfirmations($matchId);
        }

        $this->matchRepository->save($match);

        $this->eventDispatcher->dispatchEvents($domainEvents);

        return $match;
    }

    private function applyUpdates(PadelMatch $match, UpdateMatchCommand $command): void
    {
        if ($command->courtName !== null) {
            $match->updateCourtName(CourtName::fromString($command->courtName));
        }

        if ($command->matchDate !== null) {
            $match->updateMatchDate(new DateTimeImmutable($command->matchDate));
        }

        if ($command->notes !== null) {
            $match->updateNotes(Notes::fromString($command->notes));
        }

        if ($command->matchFormat !== null) {
            $match->updateFormat(MatchFormat::fromEnum(MatchFormatEnum::from($command->matchFormat)));
        }

        if ($command->matchType !== null) {
            $match->updateType(MatchType::fromEnum(MatchTypeEnum::from($command->matchType)));
        }

        if ($command->setsDetail !== null) {
            $match->updateSetsDetail(SetsDetail::fromArray($command->setsDetail));
        }

        if ($command->setsToWin !== null) {
            $match->updateSetsToWin(SetsToWin::fromInt($command->setsToWin));
        }
    }
}
