<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidEloRatingException;

final readonly class EloRating
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
            $violations[] = 'Elo rating cannot be negative';
        }

        if (! empty($violations)) {
            throw InvalidEloRatingException::fromViolations($violations);
        }
    }
}
