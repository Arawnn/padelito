<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Enums\TeamEnum;

final readonly class Team
{
    private function __construct(private TeamEnum $value) {}

    public static function fromEnum(TeamEnum $value): self
    {
        return new self($value);
    }

    public static function fromString(string $value): self
    {
        return new self(TeamEnum::from($value));
    }

    public static function A(): self
    {
        return new self(TeamEnum::A);
    }

    public static function B(): self
    {
        return new self(TeamEnum::B);
    }

    public function value(): TeamEnum
    {
        return $this->value;
    }

    public function isA(): bool
    {
        return $this->value === TeamEnum::A;
    }
}
