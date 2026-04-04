<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

final readonly class PlayerStats
{
    private function __construct(
        public readonly TotalWins $totalWins,
        public readonly TotalLosses $totalLosses,
        public readonly EloRating $eloRating,
        public readonly CurrentStreak $currentStreak,
        public readonly BestStreak $bestStreak,
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
