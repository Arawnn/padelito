<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\ValueObjects;

use App\Features\Auth\Domain\Exceptions\InvalidNameException;

final readonly class Name
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

        if (strlen($value) === 0 || trim($value) === '') {
            $violations[] = 'Name cannot be empty';
        } elseif (strlen($value) < 3) {
            $violations[] = 'Name must be at least 3 characters long';
        } elseif (strlen($value) > 255) {
            $violations[] = 'Name must be less than 256 characters long';
        }

        if (!empty($violations)) {
            throw InvalidNameException::fromViolations($violations);
        }
    }
}
