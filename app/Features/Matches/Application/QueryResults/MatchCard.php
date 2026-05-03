<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\QueryResults;

use App\Features\Matches\Domain\Entities\PadelMatch;
use DateTimeImmutable;

readonly class MatchCard
{
    /**
     * @param  array{player1_id: string, player2_id: string|null}  $teamA
     * @param  array{player1_id: string|null, player2_id: string|null}  $teamB
     * @param  array{team_a: int|null, team_b: int|null, sets_detail: list<array{a: int, b: int}>|null}  $score
     * @param  list<string>  $confirmedPlayerIds
     */
    public function __construct(
        public string $id,
        public string $matchType,
        public string $matchFormat,
        public string $status,
        public ?string $courtName,
        public ?DateTimeImmutable $matchDate,
        public ?string $notes,
        public string $createdBy,
        public array $teamA,
        public array $teamB,
        public int $setsToWin,
        public array $score,
        public ?EloImpact $eloImpact,
        public array $confirmedPlayerIds,
    ) {}

    public static function fromMatch(PadelMatch $match, ?EloImpact $eloImpact): static
    {
        return new static(
            id: $match->id()->value(),
            matchType: $match->type()->value()->value,
            matchFormat: $match->format()->value()->value,
            status: $match->status()->value()->value,
            courtName: $match->courtName()?->value(),
            matchDate: $match->matchDate(),
            notes: $match->notes()?->value(),
            createdBy: $match->createdBy()->value(),
            teamA: [
                'player1_id' => $match->teamAPlayer1Id()->value(),
                'player2_id' => $match->teamAPlayer2Id()?->value(),
            ],
            teamB: [
                'player1_id' => $match->teamBPlayer1Id()?->value(),
                'player2_id' => $match->teamBPlayer2Id()?->value(),
            ],
            setsToWin: $match->setsToWin()->value(),
            score: [
                'team_a' => $match->teamAScore()?->value(),
                'team_b' => $match->teamBScore()?->value(),
                'sets_detail' => $match->setsDetail()?->sets(),
            ],
            eloImpact: $eloImpact,
            confirmedPlayerIds: array_map(fn ($id): string => $id->value(), $match->confirmedPlayerIds()),
        );
    }
}
