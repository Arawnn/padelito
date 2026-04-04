<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Enums\DominantHandEnum;

final readonly class DominantHand
{
    private function __construct(private DominantHandEnum $value) {}

    public static function fromDominantHandEnum(DominantHandEnum $value): self
    {
        return new self($value);
    }

    public function value(): DominantHandEnum
    {
        return $this->value;
    }
}
