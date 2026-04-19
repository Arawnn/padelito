<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Domain\Services;

use App\Features\Matches\Domain\Services\EloCalculationService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class EloCalculationServiceTest extends TestCase
{
    private EloCalculationService $service;

    protected function setUp(): void
    {
        $this->service = new EloCalculationService;
    }

    public function test_winner_gains_elo_and_loser_loses_elo(): void
    {
        $result = $this->service->calculate(
            teamAElos: [1500],
            teamBElos: [1500],
            teamAMatchCounts: [10],
            teamBMatchCounts: [10],
            teamAScore: 2,
            teamBScore: 0,
        );

        $this->assertGreaterThan(0, $result->teamAChange);
        $this->assertLessThan(0, $result->teamBChange);
    }

    public function test_loser_loses_elo(): void
    {
        $result = $this->service->calculate(
            teamAElos: [1500],
            teamBElos: [1500],
            teamAMatchCounts: [10],
            teamBMatchCounts: [10],
            teamAScore: 0,
            teamBScore: 2,
        );

        $this->assertLessThan(0, $result->teamAChange);
        $this->assertGreaterThan(0, $result->teamBChange);
    }

    public function test_draw_produces_near_zero_change_for_equal_ratings(): void
    {
        $result = $this->service->calculate(
            teamAElos: [1500],
            teamBElos: [1500],
            teamAMatchCounts: [10],
            teamBMatchCounts: [10],
            teamAScore: 1,
            teamBScore: 1,
        );

        $this->assertEquals(0, $result->teamAChange);
        $this->assertEquals(0, $result->teamBChange);
    }

    public function test_higher_k_factor_for_new_players(): void
    {
        $newPlayer = $this->service->calculate(
            teamAElos: [1500],
            teamBElos: [1500],
            teamAMatchCounts: [5],
            teamBMatchCounts: [5],
            teamAScore: 2,
            teamBScore: 0,
        );

        $veteran = $this->service->calculate(
            teamAElos: [1500],
            teamBElos: [1500],
            teamAMatchCounts: [150],
            teamBMatchCounts: [150],
            teamAScore: 2,
            teamBScore: 0,
        );

        $this->assertGreaterThan(abs($veteran->teamAChange), abs($newPlayer->teamAChange));
    }

    public function test_larger_score_diff_applies_multiplier(): void
    {
        $close = $this->service->calculate(
            teamAElos: [1500],
            teamBElos: [1500],
            teamAMatchCounts: [10],
            teamBMatchCounts: [10],
            teamAScore: 2,
            teamBScore: 1,
        );

        $dominant = $this->service->calculate(
            teamAElos: [1500],
            teamBElos: [1500],
            teamAMatchCounts: [10],
            teamBMatchCounts: [10],
            teamAScore: 3,
            teamBScore: 0,
        );

        $this->assertGreaterThan(abs($close->teamAChange), abs($dominant->teamAChange));
    }

    public function test_clamp_elo_below_minimum(): void
    {
        $this->assertEquals(100, $this->service->clampElo(50));
    }

    public function test_clamp_elo_above_maximum(): void
    {
        $this->assertEquals(3000, $this->service->clampElo(9999));
    }

    public function test_clamp_elo_within_range_unchanged(): void
    {
        $this->assertEquals(1500, $this->service->clampElo(1500));
    }

    public function test_doubles_uses_team_average_elo(): void
    {
        $result = $this->service->calculate(
            teamAElos: [1600, 1400],
            teamBElos: [1500, 1500],
            teamAMatchCounts: [10, 10],
            teamBMatchCounts: [10, 10],
            teamAScore: 2,
            teamBScore: 0,
        );

        $this->assertGreaterThan(0, $result->teamAChange);
    }
}
