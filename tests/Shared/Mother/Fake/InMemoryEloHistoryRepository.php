<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Matches\Domain\Repositories\EloHistoryRepositoryInterface;

final class InMemoryEloHistoryRepository implements EloHistoryRepositoryInterface
{
    /** @var list<array{playerId: string, matchId: string, eloBefore: int, eloAfter: int, eloChange: int}> */
    private array $records = [];

    public function record(string $playerId, string $matchId, int $eloBefore, int $eloAfter, int $eloChange): void
    {
        $this->records[] = compact('playerId', 'matchId', 'eloBefore', 'eloAfter', 'eloChange');
    }

    /** @return list<array{playerId: string, matchId: string, eloBefore: int, eloAfter: int, eloChange: int}> */
    public function all(): array
    {
        return $this->records;
    }

    public function countForMatch(string $matchId): int
    {
        return \count(array_filter($this->records, fn (array $r) => $r['matchId'] === $matchId));
    }
}
