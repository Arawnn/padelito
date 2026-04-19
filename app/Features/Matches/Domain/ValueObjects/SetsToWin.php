<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class SetsToWin
{
    private function __construct(private int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1 || $value > 3) {
            throw new InvalidArgumentException("sets_to_win must be between 1 and 3, got {$value}");
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function maxSets(): int
    {
        return 2 * $this->value - 1;
    }
}
