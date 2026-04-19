<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

final readonly class PlayerId
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
