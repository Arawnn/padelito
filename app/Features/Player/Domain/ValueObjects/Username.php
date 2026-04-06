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

        if (strlen($value) > 30) {
            $violations[] = 'Username must be at most 30 characters long';
        }

        if (! preg_match('/^[a-z0-9_]+$/', $value)) {
            $violations[] = 'Username may only contain lowercase letters, digits and underscores';
        }

        if (! empty($violations)) {
            throw InvalidUsernameException::fromViolations($violations);
        }
    }
}
