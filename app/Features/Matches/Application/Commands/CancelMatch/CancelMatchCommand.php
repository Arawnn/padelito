<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\CancelMatch;

final readonly class CancelMatchCommand
{
    public function __construct(
        public string $matchId,
        public string $requesterId,
    ) {}
}
