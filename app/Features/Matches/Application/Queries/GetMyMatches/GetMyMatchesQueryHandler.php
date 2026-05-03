<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Queries\GetMyMatches;

use App\Features\Matches\Application\QueryResults\MatchCard;
use App\Features\Matches\Application\QueryResults\MatchQueryResultFactory;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\PlayerId;

final readonly class GetMyMatchesQueryHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private MatchQueryResultFactory $matchQueryResultFactory,
    ) {}

    /** @return list<MatchCard> */
    public function __invoke(GetMyMatchesQuery $query): array
    {
        $matches = $this->matchRepository->findByPlayerId(
            PlayerId::fromString($query->playerId),
            $query->filter,
        );

        return $this->matchQueryResultFactory->cardsFromMatches($matches, $query->playerId);
    }
}
