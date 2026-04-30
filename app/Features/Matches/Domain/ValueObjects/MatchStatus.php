<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Enums\MatchStatusEnum;

final readonly class MatchStatus
{
    private function __construct(private MatchStatusEnum $value) {}

    public static function fromEnum(MatchStatusEnum $value): self
    {
        return new self($value);
    }

    public static function pending(): self
    {
        return new self(MatchStatusEnum::PENDING);
    }

    public static function validated(): self
    {
        return new self(MatchStatusEnum::VALIDATED);
    }

    public static function cancelled(): self
    {
        return new self(MatchStatusEnum::CANCELLED);
    }

    public function value(): MatchStatusEnum
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === MatchStatusEnum::PENDING;
    }

    public function isValidated(): bool
    {
        return $this->value === MatchStatusEnum::VALIDATED;
    }

    public function isCancelled(): bool
    {
        return $this->value === MatchStatusEnum::CANCELLED;
    }
}
