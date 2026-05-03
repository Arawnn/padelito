<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Services;

use App\Features\Matches\Application\Contracts\MatchEloImpactReader;
use App\Features\Matches\Application\Dto\MatchEloImpactInput;
use App\Features\Matches\Application\QueryResults\EloImpact;
use App\Features\Player\Application\Contracts\MatchEloSummaryReader as PlayerMatchEloSummaryReader;
use App\Features\Player\Application\Dto\MatchEloInput as PlayerMatchEloInput;
use App\Features\Player\Application\Dto\MatchEloSummary as PlayerMatchEloSummary;

final readonly class PlayerEloImpactReaderAdapter implements MatchEloImpactReader
{
    public function __construct(
        private PlayerMatchEloSummaryReader $reader,
    ) {}

    public function forMatch(MatchEloImpactInput $input, ?string $currentUserId): ?EloImpact
    {
        $summary = $this->reader->forMatch($this->toPlayerInput($input), $currentUserId);

        return $summary !== null ? $this->toMatchSummary($summary) : null;
    }

    public function forMatches(array $inputs, ?string $currentUserId): array
    {
        $summaries = $this->reader->summariesForMatches(
            array_map(fn (MatchEloImpactInput $input): PlayerMatchEloInput => $this->toPlayerInput($input), $inputs),
            $currentUserId,
        );

        $impacts = [];
        foreach ($summaries as $matchId => $summary) {
            $impacts[$matchId] = $this->toMatchSummary($summary);
        }

        return $impacts;
    }

    private function toPlayerInput(MatchEloImpactInput $input): PlayerMatchEloInput
    {
        return new PlayerMatchEloInput(
            matchId: $input->matchId,
            isRanked: $input->isRanked,
            isValidated: $input->isValidated,
            teamAPlayerIds: $input->teamAPlayerIds,
            teamBPlayerIds: $input->teamBPlayerIds,
            teamAScore: $input->teamAScore,
            teamBScore: $input->teamBScore,
        );
    }

    private function toMatchSummary(PlayerMatchEloSummary $summary): EloImpact
    {
        return EloImpact::from(
            teamABefore: $summary->teamABefore,
            teamBBefore: $summary->teamBBefore,
            teamAChange: $summary->teamAChange,
            teamBChange: $summary->teamBChange,
            currentUserChange: $summary->currentUserChange,
            source: $summary->source,
        );
    }
}
