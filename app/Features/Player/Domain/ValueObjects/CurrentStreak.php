<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidCurrentStreakException;

final readonly class CurrentStreak
{
    private function __construct(private int $value) {}

    public static function fromInt(int $value): self
    {
        self::validate($value);

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    private static function validate(int $value): void
    {
        $violations = [];

        if ($value < 0) {
            $violations[] = 'Current streak cannot be negative';
        }

        if (! empty($violations)) {
            throw InvalidCurrentStreakException::fromViolations($violations);
        }
    }
}
