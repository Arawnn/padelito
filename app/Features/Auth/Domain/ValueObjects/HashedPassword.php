<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\ValueObjects;

final readonly class HashedPassword
{
    private function __construct(private string $value) {}

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function value(): string
    {
        return $this->value;
    }
}
