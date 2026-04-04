<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\ValueObjects\EloRating;

final readonly class PlayerStats
{
    private function __construct(
        private readonly TotalWins $totalWins,
        private readonly TotalLosses $totalLosses,
        private readonly EloRating $eloRating,
        private readonly CurrentStreak $currentStreak,
        private readonly BestStreak $bestStreak,
    ) {}

    public static function of(TotalWins $totalWins, TotalLosses $totalLosses, EloRating $eloRating, CurrentStreak $currentStreak, BestStreak $bestStreak): self
    {
        return new self(
            totalWins: $totalWins,
            totalLosses: $totalLosses,
            eloRating: $eloRating,
            currentStreak: $currentStreak,
            bestStreak: $bestStreak,
        );
    }
    
    public static function initialize(): self
    {
        return self::of(
            totalWins: TotalWins::fromInt(0),
            totalLosses: TotalLosses::fromInt(0),
            eloRating: EloRating::fromInt(1500),
            currentStreak: CurrentStreak::fromInt(0),
            bestStreak: BestStreak::fromInt(0)
        );
    }

    public function eloRating(): EloRating
    {
        return $this->eloRating;
    }

    public function totalWins(): TotalWins
    {
        return $this->totalWins;
    }

    public function totalLosses(): TotalLosses
    {
        return $this->totalLosses;
    }

    public function currentStreak(): CurrentStreak
    {
        return $this->currentStreak;
    }

    public function bestStreak(): BestStreak
    {
        return $this->bestStreak;
    }

    public function totalMatches(): int
    {
        return $this->totalWins->value() + $this->totalLosses->value();
    }

    public function winRate(): float
    {
        $total = $this->totalMatches();

        return $total === 0 ? 0.0 : $this->totalWins->value() / $total;
    }
}
