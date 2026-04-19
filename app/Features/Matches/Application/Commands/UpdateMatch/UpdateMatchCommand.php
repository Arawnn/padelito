<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\UpdateMatch;

final readonly class UpdateMatchCommand
{
    public function __construct(
        public string $matchId,
        public string $requesterId,
        public ?string $courtName = null,
        public ?string $matchDate = null,
        public ?string $notes = null,
        public ?string $matchFormat = null,
        public ?string $matchType = null,
        /** @var list<array{a: int, b: int}>|null */
        public ?array $setsDetail = null,
        public ?int $setsToWin = null,
    ) {}
}
