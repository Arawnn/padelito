<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Repositories;

use App\Features\Player\Domain\ValueObjects\EloHistoryEntry;

interface EloHistoryRepositoryInterface
{
    /** @param list<EloHistoryEntry> $entries */
    public function recordMany(array $entries): void;

    /** @return list<EloHistoryEntry> */
    public function findByMatchId(string $matchId): array;
}
