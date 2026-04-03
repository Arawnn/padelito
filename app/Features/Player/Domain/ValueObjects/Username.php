<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidUsernameException;

final readonly class Username
{
    private function __construct(private string $value) {}

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
            $violations[] = 'Username cannot be empty';
        }

        if (strlen($value) < 3) {
            $violations[] = 'Username must be at least 3 characters long';
        }

        if (strlen($value) > 255) {
            $violations[] = 'Username must be less than 256 characters long';
        }

        if (! empty($violations)) {
            throw InvalidUsernameException::fromViolations($violations);
        }
    }
}
