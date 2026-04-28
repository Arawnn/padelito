<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\ViewModels;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Player\Application\Dto\MatchEloInput;
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
            elo: $this->eloSummaryProvider->forMatch($this->toEloInput($match), $currentUserId),
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

    private function toEloInput(PadelMatch $match): MatchEloInput
    {
        $scores = $match->setsDetail() !== null ? $match->derivedScores() : null;

        return new MatchEloInput(
            matchId: $match->id()->value(),
            isRanked: $match->type()->isRanked(),
            isValidated: $match->status()->isValidated(),
            teamAPlayerIds: array_values(array_filter([
                $match->teamAPlayer1Id()->value(),
                $match->teamAPlayer2Id()?->value(),
            ])),
            teamBPlayerIds: array_values(array_filter([
                $match->teamBPlayer1Id()?->value(),
                $match->teamBPlayer2Id()?->value(),
            ])),
            teamAScore: $scores !== null ? $scores[0] : null,
            teamBScore: $scores !== null ? $scores[1] : null,
        );
    }
}
