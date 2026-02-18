<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\ValueObjects;


final readonly class Id {
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}