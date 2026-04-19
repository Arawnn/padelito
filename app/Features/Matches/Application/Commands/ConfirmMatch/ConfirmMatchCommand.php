<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\ConfirmMatch;

final readonly class ConfirmMatchCommand
{
    public function __construct(
        public string $matchId,
        public string $playerId,
    ) {}
}
