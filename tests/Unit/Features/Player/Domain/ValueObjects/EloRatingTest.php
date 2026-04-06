<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidEloRatingException;
use App\Features\Player\Domain\ValueObjects\EloRating;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class EloRatingTest extends TestCase
{
    public function test_it_accepts_a_valid_elo_rating(): void
    {
        $this->assertEquals(1500, EloRating::fromInt(1500)->value());
    }

    public function test_it_accepts_zero(): void
    {
        $this->assertEquals(0, EloRating::fromInt(0)->value());
    }

    public function test_it_accepts_a_high_rating(): void
    {
        $this->assertEquals(3000, EloRating::fromInt(3000)->value());
    }

    public function test_it_rejects_a_negative_rating(): void
    {
        $this->expectException(InvalidEloRatingException::class);
        $this->expectExceptionMessage('Elo rating cannot be negative');
        EloRating::fromInt(-1);
    }
}
