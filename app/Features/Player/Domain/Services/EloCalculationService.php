<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Services;

final class EloCalculationService
{
    private const K_NEW = 40;

    private const K_INTERMEDIATE = 30;

    private const K_ESTABLISHED = 20;

    private const MIN_ELO = 100;

    private const MAX_ELO = 3000;

    /**
     * @param  list<int>  $teamAElos  ELO ratings of team A players
     * @param  list<int>  $teamBElos  ELO ratings of team B players
     * @param  list<int>  $teamAMatchCounts  Total matches played by each team A player
     * @param  list<int>  $teamBMatchCounts  Total matches played by each team B player
     */
    public function calculate(
        array $teamAElos,
        array $teamBElos,
        array $teamAMatchCounts,
        array $teamBMatchCounts,
        int $teamAScore,
        int $teamBScore,
    ): EloCalculationResult {
        $teamAAvg = array_sum($teamAElos) / count($teamAElos);
        $teamBAvg = array_sum($teamBElos) / count($teamBElos);

        $expectedA = $this->expectedScore($teamAAvg, $teamBAvg);
        $expectedB = 1 - $expectedA;

        $actualA = match (true) {
            $teamAScore > $teamBScore => 1.0,
            $teamAScore < $teamBScore => 0.0,
            default => 0.5,
        };

        $actualB = 1.0 - $actualA;

        $multiplier = $this->performanceMultiplier(abs($teamAScore - $teamBScore));

        $teamAK = $this->teamKFactor($teamAMatchCounts);
        $teamBK = $this->teamKFactor($teamBMatchCounts);

        $changeA = (int) round($teamAK * ($actualA - $expectedA) * $multiplier);
        $changeB = (int) round($teamBK * ($actualB - $expectedB) * $multiplier);

        return new EloCalculationResult(
            teamAChange: $changeA,
            teamBChange: $changeB,
            teamAExpected: $expectedA,
            teamBExpected: $expectedB,
        );
    }

    public function clampElo(int $elo): int
    {
        return max(self::MIN_ELO, min(self::MAX_ELO, $elo));
    }

    private function expectedScore(float $ratingA, float $ratingB): float
    {
        return 1 / (1 + 10 ** (($ratingB - $ratingA) / 400));
    }

    private function kFactor(int $matchesPlayed): int
    {
        if ($matchesPlayed < 30) {
            return self::K_NEW;
        }

        if ($matchesPlayed < 100) {
            return self::K_INTERMEDIATE;
        }

        return self::K_ESTABLISHED;
    }

    /** @param list<int> $matchCounts */
    private function teamKFactor(array $matchCounts): float
    {
        $kValues = array_map(fn (int $m) => $this->kFactor($m), $matchCounts);

        return array_sum($kValues) / count($kValues);
    }

    private function performanceMultiplier(int $scoreDiff): float
    {
        return match (true) {
            $scoreDiff <= 1 => 1.0,
            $scoreDiff === 2 => 1.15,
            default => 1.3,
        };
    }
}
