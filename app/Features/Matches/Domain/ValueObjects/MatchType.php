<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Enums\MatchTypeEnum;

final readonly class MatchType
{
    private function __construct(private MatchTypeEnum $value) {}

    public static function fromEnum(MatchTypeEnum $value): self
    {
        return new self($value);
    }

    public static function friendly(): self
    {
        return new self(MatchTypeEnum::FRIENDLY);
    }

    public static function ranked(): self
    {
        return new self(MatchTypeEnum::RANKED);
    }

    public function value(): MatchTypeEnum
    {
        return $this->value;
    }

    public function isRanked(): bool
    {
        return $this->value === MatchTypeEnum::RANKED;
    }
}
