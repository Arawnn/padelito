<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Enums\InvitationTypeEnum;

final readonly class InvitationType
{
    private function __construct(private InvitationTypeEnum $value) {}

    public static function partner(): self
    {
        return new self(InvitationTypeEnum::PARTNER);
    }

    public static function opponent(): self
    {
        return new self(InvitationTypeEnum::OPPONENT);
    }

    public static function fromString(string $value): self
    {
        return new self(InvitationTypeEnum::from($value));
    }

    public static function fromEnum(InvitationTypeEnum $enum): self
    {
        return new self($enum);
    }

    public function value(): InvitationTypeEnum
    {
        return $this->value;
    }

    public function isPartner(): bool
    {
        return $this->value === InvitationTypeEnum::PARTNER;
    }

    public function isOpponent(): bool
    {
        return $this->value === InvitationTypeEnum::OPPONENT;
    }
}
