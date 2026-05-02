<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\CreateMatch;

use App\Features\Matches\Application\Contracts\PlayerRegistryInterface;
use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Enums\MatchFormatEnum;
use App\Features\Matches\Domain\Enums\MatchTypeEnum;
use App\Features\Matches\Domain\Exceptions\PlayerNotRegisteredInAppException;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\CourtName;
use App\Features\Matches\Domain\ValueObjects\MatchFormat;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchType;
use App\Features\Matches\Domain\ValueObjects\Notes;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\SetsToWin;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use DateTimeImmutable;

final readonly class CreateMatchCommandHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private PlayerRegistryInterface $playerRegistry,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(CreateMatchCommand $command): PadelMatch
    {
        $creatorId = PlayerId::fromString($command->creatorId);

        if (! $this->playerRegistry->exists($creatorId)) {
            throw PlayerNotRegisteredInAppException::forPlayer($command->creatorId);
        }

        $match = PadelMatch::create(
            id: MatchId::generate(),
            type: MatchType::fromEnum(MatchTypeEnum::from($command->matchType)),
            format: MatchFormat::fromEnum(MatchFormatEnum::from($command->matchFormat)),
            createdBy: $creatorId,
            courtName: $command->courtName ? CourtName::fromString($command->courtName) : null,
            matchDate: $command->matchDate ? new DateTimeImmutable($command->matchDate) : null,
            notes: $command->notes !== null ? Notes::fromString($command->notes) : null,
            setsToWin: $command->setsToWin !== null ? SetsToWin::fromInt($command->setsToWin) : null,
        );

        $this->matchRepository->save($match);

        $domainEvents = $match->pullDomainEvents();
        $this->eventDispatcher->dispatchEvents($domainEvents);

        return $match;
    }
}
