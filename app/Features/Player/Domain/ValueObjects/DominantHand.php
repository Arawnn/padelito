<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

enum DominantHandEnum: string
{
    case LEFT = 'left';
    case RIGHT = 'right';
    case AMBIDEXTROUS = 'ambidextrous';
}

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
