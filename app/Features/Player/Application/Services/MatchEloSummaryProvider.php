<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Services;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Player\Application\ReadModels\MatchEloSummary;
use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Repositories\EloHistoryRepositoryInterface;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\Services\EloCalculationService;
use App\Features\Player\Domain\ValueObjects\EloHistoryEntry;
use App\Features\Player\Domain\ValueObjects\Id;

final readonly class MatchEloSummaryProvider
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private EloHistoryRepositoryInterface $eloHistoryRepository,
        private EloCalculationService $eloCalculationService,
    ) {}

    public function forMatch(PadelMatch $match, ?string $currentUserId): ?MatchEloSummary
    {
        if (! $match->type()->isRanked()) {
            return null;
        }

        if ($match->status()->isValidated()) {
            return $this->confirmedSummary($match, $currentUserId);
        }

        return $this->projectedSummary($match, $currentUserId);
    }

    private function confirmedSummary(PadelMatch $match, ?string $currentUserId): ?MatchEloSummary
    {
        $entries = $this->eloHistoryRepository->findByMatchId($match->id()->value());
        if ($entries === []) {
            return null;
        }

        $teamAEntries = $this->entriesForTeam($entries, 'A');
        $teamBEntries = $this->entriesForTeam($entries, 'B');
        if ($teamAEntries === [] || $teamBEntries === []) {
            return null;
        }

        $teamAChange = $teamAEntries[0]->eloChange;
        $teamBChange = $teamBEntries[0]->eloChange;

        return MatchEloSummary::from(
            teamABefore: $this->averageBefore($teamAEntries),
            teamBBefore: $this->averageBefore($teamBEntries),
            teamAChange: $teamAChange,
            teamBChange: $teamBChange,
            currentUserChange: $this->currentUserChange($match, $currentUserId, $teamAChange, $teamBChange),
            source: 'confirmed',
        );
    }

    private function projectedSummary(PadelMatch $match, ?string $currentUserId): ?MatchEloSummary
    {
        if ($match->setsDetail() === null) {
            return null;
        }

        [$teamAScore, $teamBScore] = $match->derivedScores();

        $teamAPlayerIds = $this->teamAPlayerIds($match);
        $teamBPlayerIds = $this->teamBPlayerIds($match);
        if ($teamAPlayerIds === [] || $teamBPlayerIds === []) {
            return null;
        }

        $players = $this->loadPlayers([...$teamAPlayerIds, ...$teamBPlayerIds]);
        if (count($players) !== count([...$teamAPlayerIds, ...$teamBPlayerIds])) {
            return null;
        }

        $teamAElos = array_map(fn (string $id): int => $players[$id]->stats()->eloRating()->value(), $teamAPlayerIds);
        $teamBElos = array_map(fn (string $id): int => $players[$id]->stats()->eloRating()->value(), $teamBPlayerIds);

        $result = $this->eloCalculationService->calculate(
            teamAElos: $teamAElos,
            teamBElos: $teamBElos,
            teamAMatchCounts: array_map(fn (string $id): int => $players[$id]->stats()->totalMatches(), $teamAPlayerIds),
            teamBMatchCounts: array_map(fn (string $id): int => $players[$id]->stats()->totalMatches(), $teamBPlayerIds),
            teamAScore: $teamAScore,
            teamBScore: $teamBScore,
        );

        return MatchEloSummary::from(
            teamABefore: $this->average($teamAElos),
            teamBBefore: $this->average($teamBElos),
            teamAChange: $result->teamAChange,
            teamBChange: $result->teamBChange,
            currentUserChange: $this->currentUserChange($match, $currentUserId, $result->teamAChange, $result->teamBChange),
            source: 'projected',
        );
    }

    /**
     * @param  list<string>  $playerIds
     * @return array<string, Player>
     */
    private function loadPlayers(array $playerIds): array
    {
        $players = [];

        foreach ($playerIds as $playerId) {
            $player = $this->playerRepository->findById(Id::fromString($playerId));
            if ($player !== null) {
                $players[$playerId] = $player;
            }
        }

        return $players;
    }

    /** @param list<EloHistoryEntry> $entries */
    private function entriesForTeam(array $entries, string $team): array
    {
        return array_values(array_filter($entries, fn (EloHistoryEntry $entry): bool => $entry->team === $team));
    }

    /** @param list<EloHistoryEntry> $entries */
    private function averageBefore(array $entries): int
    {
        return $this->average(array_map(fn (EloHistoryEntry $entry): int => $entry->eloBefore, $entries));
    }

    /** @param list<int> $values */
    private function average(array $values): int
    {
        return (int) round(array_sum($values) / count($values));
    }

    private function currentUserChange(PadelMatch $match, ?string $currentUserId, int $teamAChange, int $teamBChange): ?int
    {
        if ($currentUserId === null) {
            return null;
        }

        if (in_array($currentUserId, $this->teamAPlayerIds($match), true)) {
            return $teamAChange;
        }

        if (in_array($currentUserId, $this->teamBPlayerIds($match), true)) {
            return $teamBChange;
        }

        return null;
    }

    /** @return list<string> */
    private function teamAPlayerIds(PadelMatch $match): array
    {
        return array_values(array_filter([
            $match->teamAPlayer1Id()->value(),
            $match->teamAPlayer2Id()?->value(),
        ]));
    }

    /** @return list<string> */
    private function teamBPlayerIds(PadelMatch $match): array
    {
        return array_values(array_filter([
            $match->teamBPlayer1Id()?->value(),
            $match->teamBPlayer2Id()?->value(),
        ]));
    }
}
