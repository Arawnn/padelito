<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

final readonly class EloRating
{
    private function __construct(private int $value) {}

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
