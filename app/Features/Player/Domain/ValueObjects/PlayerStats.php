<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

final readonly class PlayerStats
{
    private const INITIAL_ELO_RATING = 1500;

    private const INITIAL_CURRENT_STREAK = 0;

    private const INITIAL_BEST_STREAK = 0;

    private const INITIAL_TOTAL_WINS = 0;

    private const INITIAL_TOTAL_LOSSES = 0;

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
            totalWins: TotalWins::fromInt(self::INITIAL_TOTAL_WINS),
            totalLosses: TotalLosses::fromInt(self::INITIAL_TOTAL_LOSSES),
            eloRating: EloRating::fromInt(self::INITIAL_ELO_RATING),
            currentStreak: CurrentStreak::fromInt(self::INITIAL_CURRENT_STREAK),
            bestStreak: BestStreak::fromInt(self::INITIAL_BEST_STREAK)
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
