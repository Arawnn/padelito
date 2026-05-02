<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Queries\GetMyMatches;

use App\Features\Matches\Application\ReadModels\MatchCard;
use App\Features\Matches\Application\ReadModels\MatchReadModelFactory;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\PlayerId;

final readonly class GetMyMatchesQueryHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private MatchReadModelFactory $matchReadModelFactory,
    ) {}

    /** @return list<MatchCard> */
    public function __invoke(GetMyMatchesQuery $query): array
    {
        $matches = $this->matchRepository->findByPlayerId(
            PlayerId::fromString($query->playerId),
            $query->filter,
        );

        return $this->matchReadModelFactory->cardsFromMatches($matches, $query->playerId);
    }
}
