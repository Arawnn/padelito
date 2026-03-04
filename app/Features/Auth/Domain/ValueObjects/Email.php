<?php

namespace App\Features\Auth\Domain\ValueObjects;

final readonly class Email
{
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    // TODO: Add validation for email
    // TODO: Move this value object to a shared value object
}
