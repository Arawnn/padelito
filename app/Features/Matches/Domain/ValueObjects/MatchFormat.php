<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Enums\MatchFormatEnum;

final readonly class MatchFormat
{
    private function __construct(private MatchFormatEnum $value) {}

    public static function fromEnum(MatchFormatEnum $value): self
    {
        return new self($value);
    }

    public static function doubles(): self
    {
        return new self(MatchFormatEnum::DOUBLES);
    }

    public static function singles(): self
    {
        return new self(MatchFormatEnum::SINGLES);
    }

    public function value(): MatchFormatEnum
    {
        return $this->value;
    }

    public function isDoubles(): bool
    {
        return $this->value === MatchFormatEnum::DOUBLES;
    }

    public function isSingles(): bool
    {
        return $this->value === MatchFormatEnum::SINGLES;
    }

    public function requiredPlayerCount(): int
    {
        return $this->isDoubles() ? 4 : 2;
    }
}
