<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

final readonly class PlayerPreferences
{
    private function __construct(
        private readonly ?DominantHand $dominantHand,
        private readonly ?PreferredPosition $preferredPosition,
        private readonly ?Location $location
    ) {}

    public static function of(?DominantHand $dominantHand, ?PreferredPosition $preferredPosition, ?Location $location): self
    {
        return new self(
            dominantHand: $dominantHand,
            preferredPosition: $preferredPosition,
            location: $location
        );
    }

    public function dominantHand(): ?DominantHand
    {
        return $this->dominantHand;
    }

    public function preferredPosition(): ?PreferredPosition
    {
        return $this->preferredPosition;
    }

    public function location(): ?Location
    {
        return $this->location;
    }
}
