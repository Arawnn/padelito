<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Queries\GetMatch;

use App\Features\Matches\Application\ReadModels\MatchDetails;
use App\Features\Matches\Application\ReadModels\MatchReadModelFactory;
use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchId;

final readonly class GetMatchQueryHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private MatchReadModelFactory $matchReadModelFactory,
    ) {}

    public function __invoke(GetMatchQuery $query): MatchDetails
    {
        $match = $this->matchRepository->findById(MatchId::fromString($query->matchId));
        if ($match === null) {
            throw MatchNotFoundException::create();
        }

        return $this->matchReadModelFactory->detailsFromMatch($match, $query->currentUserId);
    }
}
