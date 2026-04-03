<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

// Pour l'instant aucune validation VO simple sera implémenté par la suite
// Déterminer la ville de préférence pour jouer
// Sera simplement affiché comme info de profil affiché
// Sera utilisé pour le matching dans le futur
// A encapsulé dans VO PlayerPref probablement
final readonly class Localization
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
