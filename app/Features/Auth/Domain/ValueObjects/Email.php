<?php

namespace App\Features\Auth\Domain\ValueObjects;

use App\Features\Auth\Domain\Exceptions\InvalidEmailException;

final readonly class Email
{
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $value): self
    {
        self::validate($value);

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    private static function validate(string $value): void
    {
        $violations = [];

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $violations[] = 'Invalid email format';
        }

        if (!empty($violations)) {
            throw InvalidEmailException::fromViolations($violations);
        }
    }
}
