<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Exceptions\InvalidCourtNameException;

final readonly class CourtName
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

        if (mb_strlen($value) > 100) {
            $violations[] = 'Court name must be at most 100 characters long';
        }

        if (! empty($violations)) {
            throw InvalidCourtNameException::fromViolations($violations);
        }
    }
}
