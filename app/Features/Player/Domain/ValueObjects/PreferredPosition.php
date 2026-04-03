<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

enum PreferredPositionEnum: string
{
    case LEFT = 'left';
    case RIGHT = 'right';
    case ANY = 'any';
}

final readonly class PreferredPosition
{
    private function __construct(private PreferredPositionEnum $value) {}

    public static function fromPreferredPositionEnum(PreferredPositionEnum $value): self
    {
        return new self($value);
    }

    public function value(): PreferredPositionEnum
    {
        return $this->value;
    }
}
