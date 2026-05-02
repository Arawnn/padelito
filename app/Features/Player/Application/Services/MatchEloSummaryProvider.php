<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Services;

use App\Features\Player\Application\Contracts\MatchEloSummaryReader;
use App\Features\Player\Application\Dto\MatchEloInput;
use App\Features\Player\Application\Dto\MatchEloSummary;
use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Repositories\EloHistoryRepositoryInterface;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\Services\EloCalculationService;
use App\Features\Player\Domain\ValueObjects\EloHistoryEntry;
use App\Features\Player\Domain\ValueObjects\Id;

final readonly class MatchEloSummaryProvider implements MatchEloSummaryReader
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private EloHistoryRepositoryInterface $eloHistoryRepository,
        private EloCalculationService $eloCalculationService,
    ) {}

    public function forMatch(MatchEloInput $input, ?string $currentUserId): ?MatchEloSummary
    {
        return $this->summariesForMatches([$input], $currentUserId)[$input->matchId] ?? null;
    }

    public function summariesForMatches(array $inputs, ?string $currentUserId): array
    {
        $rankedInputs = array_values(array_filter($inputs, fn (MatchEloInput $input): bool => $input->isRanked));
        if ($rankedInputs === []) {
            return [];
        }

        $entriesByMatchId = $this->entriesByMatchId($rankedInputs);
        $players = $this->loadPlayers($this->projectedPlayerIds($rankedInputs));
        $summaries = [];

        foreach ($rankedInputs as $input) {
            $summary = $input->isValidated
                ? $this->confirmedSummary($input, $currentUserId, $entriesByMatchId[$input->matchId] ?? [])
                : $this->projectedSummary($input, $currentUserId, $players);

            if ($summary !== null) {
                $summaries[$input->matchId] = $summary;
            }
        }

        return $summaries;
    }

    /** @param list<EloHistoryEntry> $entries */
    private function confirmedSummary(MatchEloInput $input, ?string $currentUserId, array $entries): ?MatchEloSummary
    {
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

    /** @param array<string, Player> $players */
    private function projectedSummary(MatchEloInput $input, ?string $currentUserId, array $players): ?MatchEloSummary
    {
        if ($input->teamAScore === null || $input->teamBScore === null) {
            return null;
        }

        if ($input->teamAPlayerIds === [] || $input->teamBPlayerIds === []) {
            return null;
        }

        foreach ([...$input->teamAPlayerIds, ...$input->teamBPlayerIds] as $playerId) {
            if (! isset($players[$playerId])) {
                return null;
            }
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
        $playerIds = array_values(array_unique($playerIds));
        if ($playerIds === []) {
            return [];
        }

        return $this->playerRepository->findByIds(array_map(fn (string $playerId): Id => Id::fromString($playerId), $playerIds));
    }

    /**
     * @param  list<MatchEloInput>  $inputs
     * @return array<string, list<EloHistoryEntry>>
     */
    private function entriesByMatchId(array $inputs): array
    {
        $matchIds = array_values(array_unique(array_map(
            fn (MatchEloInput $input): string => $input->matchId,
            array_filter($inputs, fn (MatchEloInput $input): bool => $input->isValidated),
        )));

        $entriesByMatchId = [];
        foreach ($this->eloHistoryRepository->findByMatchIds($matchIds) as $entry) {
            $entriesByMatchId[$entry->matchId][] = $entry;
        }

        return $entriesByMatchId;
    }

    /**
     * @param  list<MatchEloInput>  $inputs
     * @return list<string>
     */
    private function projectedPlayerIds(array $inputs): array
    {
        $playerIds = [];
        foreach ($inputs as $input) {
            if ($input->isValidated) {
                continue;
            }

            $playerIds = [...$playerIds, ...$input->teamAPlayerIds, ...$input->teamBPlayerIds];
        }

        return array_values(array_unique($playerIds));
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
