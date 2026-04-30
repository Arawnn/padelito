<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Queries\GetMyMatches;

final readonly class GetMyMatchesQuery
{
    public function __construct(
        public string $playerId,
        public ?string $filter = null,
    ) {}
}
