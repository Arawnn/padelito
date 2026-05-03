<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\QueryResults;

use App\Features\Matches\Application\Contracts\MatchEloImpactReader;
use App\Features\Matches\Application\Dto\MatchEloImpactInput;
use App\Features\Matches\Domain\Entities\PadelMatch;

final readonly class MatchQueryResultFactory
{
    public function __construct(
        private MatchEloImpactReader $matchEloImpactReader,
    ) {}

    public function detailsFromMatch(PadelMatch $match, ?string $currentUserId): MatchDetails
    {
        return MatchDetails::fromMatch(
            $match,
            $this->matchEloImpactReader->forMatch($this->toEloImpactInput($match), $currentUserId),
        );
    }

    /**
     * @param  list<PadelMatch>  $matches
     * @return list<MatchCard>
     */
    public function cardsFromMatches(array $matches, ?string $currentUserId): array
    {
        $eloImpactByMatchId = $this->matchEloImpactReader->forMatches(
            array_map(fn (PadelMatch $match): MatchEloImpactInput => $this->toEloImpactInput($match), $matches),
            $currentUserId,
        );

        return array_map(
            fn (PadelMatch $match): MatchCard => MatchCard::fromMatch($match, $eloImpactByMatchId[$match->id()->value()] ?? null),
            $matches,
        );
    }

    private function toEloImpactInput(PadelMatch $match): MatchEloImpactInput
    {
        $scores = $match->setsDetail() !== null ? $match->derivedScores() : null;

        return new MatchEloImpactInput(
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
