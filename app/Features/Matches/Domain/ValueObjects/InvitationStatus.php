<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Enums\InvitationStatusEnum;

final readonly class InvitationStatus
{
    private function __construct(private InvitationStatusEnum $value) {}

    public static function fromEnum(InvitationStatusEnum $value): self
    {
        return new self($value);
    }

    public static function pending(): self
    {
        return new self(InvitationStatusEnum::PENDING);
    }

    public static function accepted(): self
    {
        return new self(InvitationStatusEnum::ACCEPTED);
    }

    public static function declined(): self
    {
        return new self(InvitationStatusEnum::DECLINED);
    }

    public static function cancelled(): self
    {
        return new self(InvitationStatusEnum::CANCELLED);
    }

    public function value(): InvitationStatusEnum
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === InvitationStatusEnum::PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->value === InvitationStatusEnum::ACCEPTED;
    }

    public function isDeclined(): bool
    {
        return $this->value === InvitationStatusEnum::DECLINED;
    }

    public function isCancelled(): bool
    {
        return $this->value === InvitationStatusEnum::CANCELLED;
    }
}
