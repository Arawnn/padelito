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
            $violations[] = 'Display name cannot be empty';
        }

        if (strlen($value) > 30) {
            $violations[] = 'Display name must be at most 30 characters long';
        }

        // Letters (including accented), spaces only
        if (! preg_match('/^[\pL\s]+$/u', $value)) {
            $violations[] = 'Display name may only contain letters and spaces';
        }

        if (! empty($violations)) {
            throw InvalidDisplayNameException::fromViolations($violations);
        }
    }
}
