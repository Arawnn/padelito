<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Repositories;

interface EloHistoryRepositoryInterface
{
    public function record(string $playerId, string $matchId, int $eloBefore, int $eloAfter, int $eloChange): void;
}
