<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Services;

use App\Features\Player\Application\Dto\MatchEloInput;
use App\Features\Player\Application\Dto\MatchEloSummary;
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

    public function forMatch(MatchEloInput $input, ?string $currentUserId): ?MatchEloSummary
    {
        if (! $input->isRanked) {
            return null;
        }

        if ($input->isValidated) {
            return $this->confirmedSummary($input, $currentUserId);
        }

        return $this->projectedSummary($input, $currentUserId);
    }

    private function confirmedSummary(MatchEloInput $input, ?string $currentUserId): ?MatchEloSummary
    {
        $entries = $this->eloHistoryRepository->findByMatchId($input->matchId);
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
            currentUserChange: $this->currentUserChange($input, $currentUserId, $teamAChange, $teamBChange),
            source: 'confirmed',
        );
    }

    private function projectedSummary(MatchEloInput $input, ?string $currentUserId): ?MatchEloSummary
    {
        if ($input->teamAScore === null || $input->teamBScore === null) {
            return null;
        }

        if ($input->teamAPlayerIds === [] || $input->teamBPlayerIds === []) {
            return null;
        }

        $players = $this->loadPlayers([...$input->teamAPlayerIds, ...$input->teamBPlayerIds]);
        if (count($players) !== count([...$input->teamAPlayerIds, ...$input->teamBPlayerIds])) {
            return null;
        }

        $teamAElos = array_map(fn (string $id): int => $players[$id]->stats()->eloRating()->value(), $input->teamAPlayerIds);
        $teamBElos = array_map(fn (string $id): int => $players[$id]->stats()->eloRating()->value(), $input->teamBPlayerIds);

        $result = $this->eloCalculationService->calculate(
            teamAElos: $teamAElos,
            teamBElos: $teamBElos,
            teamAMatchCounts: array_map(fn (string $id): int => $players[$id]->stats()->totalMatches(), $input->teamAPlayerIds),
            teamBMatchCounts: array_map(fn (string $id): int => $players[$id]->stats()->totalMatches(), $input->teamBPlayerIds),
            teamAScore: $input->teamAScore,
            teamBScore: $input->teamBScore,
        );

        return MatchEloSummary::from(
            teamABefore: $this->average($teamAElos),
            teamBBefore: $this->average($teamBElos),
            teamAChange: $result->teamAChange,
            teamBChange: $result->teamBChange,
            currentUserChange: $this->currentUserChange($input, $currentUserId, $result->teamAChange, $result->teamBChange),
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

    private function currentUserChange(MatchEloInput $input, ?string $currentUserId, int $teamAChange, int $teamBChange): ?int
    {
        if ($currentUserId === null) {
            return null;
        }

        if (in_array($currentUserId, $input->teamAPlayerIds, true)) {
            return $teamAChange;
        }

        if (in_array($currentUserId, $input->teamBPlayerIds, true)) {
            return $teamBChange;
        }

        return null;
    }
}
