<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Shared\Domain\Exceptions\InvalidUuidException;
use Ramsey\Uuid\Uuid;

final readonly class MatchId
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        if (! Uuid::isValid($value)) {
            throw InvalidUuidException::fromViolations(['Invalid UUID: '.$value]);
        }

        return new self($value);
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function value(): string
    {
        return $this->value;
    }
}
