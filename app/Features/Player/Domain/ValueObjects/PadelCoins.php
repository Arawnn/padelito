<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

final readonly class PadelCoins
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

    public static function initialize(): self
    {
        return new self(0);
    }
}
