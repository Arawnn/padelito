<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidDisplayNameException;

final readonly class DisplayName
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
            $violations[] = 'DisplayName cannot be empty';
        }

        if (strlen($value) < 3) {
            $violations[] = 'DisplayName must be at least 3 characters long';
        }

        if (strlen($value) > 255) {
            $violations[] = 'DisplayName must be less than 256 characters long';
        }

        if (! empty($violations)) {
            throw InvalidDisplayNameException::fromViolations($violations);
        }
    }
}
