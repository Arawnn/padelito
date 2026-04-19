<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Queries\GetMatch;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchId;

final readonly class GetMatchQueryHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
    ) {}

    public function __invoke(GetMatchQuery $query): PadelMatch
    {
        $match = $this->matchRepository->findById(MatchId::fromString($query->matchId));
        if ($match === null) {
            throw MatchNotFoundException::create();
        }

        return $match;
    }
}
