<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

final readonly class PlayerPreferences
{
    private function __construct(
        private readonly ?DominantHand $dominantHand,
        private readonly ?PreferredPosition $preferredPosition
    ) {}

    public static function of(?DominantHand $hand, ?PreferredPosition $position): self
    {
        return new self(
            dominantHand: $hand,
            preferredPosition: $position
        );
    }
}
