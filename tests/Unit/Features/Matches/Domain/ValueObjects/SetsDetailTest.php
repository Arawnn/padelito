<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Exceptions\InvalidSetsDetailException;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SetsDetailTest extends TestCase
{
    public function test_it_counts_sets_won_by_team_a(): void
    {
        $sets = SetsDetail::fromArray([
            ['a' => 6, 'b' => 3],
            ['a' => 2, 'b' => 6],
            ['a' => 7, 'b' => 5],
        ]);

        $this->assertEquals(2, $sets->teamASetsWon());
        $this->assertEquals(1, $sets->teamBSetsWon());
    }

    public function test_it_counts_sets_won_by_team_b(): void
    {
        $sets = SetsDetail::fromArray([
            ['a' => 3, 'b' => 6],
            ['a' => 4, 'b' => 6],
        ]);

        $this->assertEquals(0, $sets->teamASetsWon());
        $this->assertEquals(2, $sets->teamBSetsWon());
    }

    public function test_it_handles_single_set(): void
    {
        $sets = SetsDetail::fromArray([['a' => 6, 'b' => 0]]);

        $this->assertEquals(1, $sets->teamASetsWon());
        $this->assertEquals(0, $sets->teamBSetsWon());
    }

    public function test_it_rejects_empty_sets(): void
    {
        $this->expectException(InvalidSetsDetailException::class);

        SetsDetail::fromArray([]);
    }

    public function test_it_rejects_more_than_five_sets(): void
    {
        $this->expectException(InvalidSetsDetailException::class);

        SetsDetail::fromArray([
            ['a' => 6, 'b' => 3],
            ['a' => 6, 'b' => 3],
            ['a' => 6, 'b' => 3],
            ['a' => 6, 'b' => 3],
            ['a' => 6, 'b' => 3],
            ['a' => 6, 'b' => 3],
        ]);
    }

    public function test_it_rejects_negative_scores(): void
    {
        $this->expectException(InvalidSetsDetailException::class);

        SetsDetail::fromArray([['a' => -1, 'b' => 6]]);
    }

    public function test_it_rejects_missing_keys(): void
    {
        $this->expectException(InvalidSetsDetailException::class);

        SetsDetail::fromArray([['team_a' => 6, 'b' => 3]]);
    }

    public function test_set_count_returns_correct_value(): void
    {
        $sets = SetsDetail::fromArray([
            ['a' => 6, 'b' => 3],
            ['a' => 6, 'b' => 4],
        ]);

        $this->assertEquals(2, $sets->setCount());
    }
}
