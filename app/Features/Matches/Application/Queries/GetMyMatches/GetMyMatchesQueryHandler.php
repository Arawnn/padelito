<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Queries\GetMyMatches;

use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\PlayerId;

final readonly class GetMyMatchesQueryHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
    ) {}

    /** @return list<\App\Features\Matches\Domain\Entities\PadelMatch> */
    public function __invoke(GetMyMatchesQuery $query): array
    {
        return $this->matchRepository->findByPlayerId(
            PlayerId::fromString($query->playerId),
            $query->filter,
        );
    }
}
