<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Enums\PlayerLevelEnum;

final readonly class PlayerLevel
{
    private function __construct(private PlayerLevelEnum $value) {}

    public static function fromPlayerLevelEnum(PlayerLevelEnum $value): self
    {
        return new self($value);
    }

    public function value(): PlayerLevelEnum
    {
        return $this->value;
    }
}
