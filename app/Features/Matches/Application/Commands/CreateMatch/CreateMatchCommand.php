<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\CreateMatch;

final readonly class CreateMatchCommand
{
    public function __construct(
        public string $creatorId,
        public string $matchType,
        public string $matchFormat,
        public ?string $courtName = null,
        public ?string $matchDate = null,
        public ?string $notes = null,
        public ?int $setsToWin = null,
    ) {}
}
