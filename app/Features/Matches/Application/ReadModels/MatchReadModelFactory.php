<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\ReadModels;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Player\Application\Contracts\MatchEloSummaryReader;
use App\Features\Player\Application\Dto\MatchEloInput;

final readonly class MatchReadModelFactory
{
    public function __construct(
        private MatchEloSummaryReader $matchEloSummaryReader,
    ) {}

    public function detailsFromMatch(PadelMatch $match, ?string $currentUserId): MatchDetails
    {
        return MatchDetails::fromMatch(
            $match,
            $this->matchEloSummaryReader->forMatch($this->toEloInput($match), $currentUserId),
        );
    }

    /**
     * @param  list<PadelMatch>  $matches
     * @return list<MatchCard>
     */
    public function cardsFromMatches(array $matches, ?string $currentUserId): array
    {
        $eloByMatchId = $this->matchEloSummaryReader->summariesForMatches(
            array_map(fn (PadelMatch $match): MatchEloInput => $this->toEloInput($match), $matches),
            $currentUserId,
        );

        return array_map(
            fn (PadelMatch $match): MatchCard => MatchCard::fromMatch($match, $eloByMatchId[$match->id()->value()] ?? null),
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
