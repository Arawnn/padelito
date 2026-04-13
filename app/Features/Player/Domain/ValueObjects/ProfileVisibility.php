<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

final readonly class ProfileVisibility
{
    private function __construct(private bool $isPublic) {}

    public static function public(): self
    {
        return new self(true);
    }

    public static function private(): self
    {
        return new self(false);
    }

    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function isPrivate(): bool
    {
        return ! $this->isPublic;
    }
}
