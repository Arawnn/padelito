<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Enums\PreferredPositionEnum;

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
