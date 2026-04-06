<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\ValueObjects\BestStreak;
use App\Features\Player\Domain\ValueObjects\CurrentStreak;
use App\Features\Player\Domain\ValueObjects\EloRating;
use App\Features\Player\Domain\ValueObjects\PlayerStats;
use App\Features\Player\Domain\ValueObjects\TotalLosses;
use App\Features\Player\Domain\ValueObjects\TotalWins;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class PlayerStatsTest extends TestCase
{
    public function test_it_initializes_with_correct_defaults(): void
    {
        $stats = PlayerStats::initialize();

        $this->assertEquals(1500, $stats->eloRating()->value());
        $this->assertEquals(0, $stats->totalWins()->value());
        $this->assertEquals(0, $stats->totalLosses()->value());
        $this->assertEquals(0, $stats->currentStreak()->value());
        $this->assertEquals(0, $stats->bestStreak()->value());
    }

    public function test_total_matches_sums_wins_and_losses(): void
    {
        $stats = PlayerStats::of(
            totalWins: TotalWins::fromInt(7),
            totalLosses: TotalLosses::fromInt(3),
            eloRating: EloRating::fromInt(1500),
            currentStreak: CurrentStreak::fromInt(2),
            bestStreak: BestStreak::fromInt(5),
        );

        $this->assertEquals(10, $stats->totalMatches());
    }

    public function test_win_rate_is_correct(): void
    {
        $stats = PlayerStats::of(
            totalWins: TotalWins::fromInt(3),
            totalLosses: TotalLosses::fromInt(1),
            eloRating: EloRating::fromInt(1500),
            currentStreak: CurrentStreak::fromInt(0),
            bestStreak: BestStreak::fromInt(0),
        );

        $this->assertEquals(0.75, $stats->winRate());
    }

    public function test_win_rate_is_zero_when_no_matches_played(): void
    {
        $stats = PlayerStats::initialize();

        $this->assertEquals(0.0, $stats->winRate());
    }

    public function test_win_rate_is_one_hundred_percent_with_all_wins(): void
    {
        $stats = PlayerStats::of(
            totalWins: TotalWins::fromInt(5),
            totalLosses: TotalLosses::fromInt(0),
            eloRating: EloRating::fromInt(1600),
            currentStreak: CurrentStreak::fromInt(5),
            bestStreak: BestStreak::fromInt(5),
        );

        $this->assertEquals(1.0, $stats->winRate());
    }
}
