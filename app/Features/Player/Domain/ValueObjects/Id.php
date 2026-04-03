<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Shared\Domain\Exceptions\InvalidUuidException;
use Ramsey\Uuid\Uuid;

final readonly class Id
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        if (! Uuid::isValid($value)) {
            throw InvalidUuidException::fromViolations(['Invalid UUID: '.$value]);
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Id $other): bool
    {
        return $this->value() === $other->value();
    }
}
