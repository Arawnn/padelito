<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Services;

final readonly class EloCalculationResult
{
    public function __construct(
        public readonly int $teamAChange,
        public readonly int $teamBChange,
        public readonly float $teamAExpected,
        public readonly float $teamBExpected,
    ) {}
}
