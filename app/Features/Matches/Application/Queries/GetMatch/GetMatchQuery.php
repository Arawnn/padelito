<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Queries\GetMatch;

final readonly class GetMatchQuery
{
    public function __construct(
        public string $matchId,
    ) {}
}
