<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

final readonly class EloSnapshot
{
    private function __construct(
        private EloRating $teamABefore,
        private EloRating $teamBBefore,
        private EloChange $change,
    ) {}

    public static function from(EloRating $teamABefore, EloRating $teamBBefore, EloChange $change): self
    {
        return new self($teamABefore, $teamBBefore, $change);
    }

    public function teamABefore(): EloRating
    {
        return $this->teamABefore;
    }

    public function teamBBefore(): EloRating
    {
        return $this->teamBBefore;
    }

    public function change(): EloChange
    {
        return $this->change;
    }
}
