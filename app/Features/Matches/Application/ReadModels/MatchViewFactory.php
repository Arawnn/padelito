<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\ReadModels;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Player\Application\Services\MatchEloSummaryProvider;

final readonly class MatchViewFactory
{
    public function __construct(
        private MatchEloSummaryProvider $eloSummaryProvider,
    ) {}

    public function fromMatch(PadelMatch $match, ?string $currentUserId): MatchView
    {
        return new MatchView(
            match: $match,
            elo: $this->eloSummaryProvider->forMatch($match, $currentUserId),
        );
    }

    /**
     * @param  list<PadelMatch>  $matches
     * @return list<MatchView>
     */
    public function fromMatches(array $matches, ?string $currentUserId): array
    {
        return array_map(
            fn (PadelMatch $match): MatchView => $this->fromMatch($match, $currentUserId),
            $matches,
        );
    }
}
