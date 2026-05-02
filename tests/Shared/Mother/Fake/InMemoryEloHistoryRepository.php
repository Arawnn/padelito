<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Player\Domain\Repositories\EloHistoryRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\EloHistoryEntry;

final class InMemoryEloHistoryRepository implements EloHistoryRepositoryInterface
{
    /** @var list<array{playerId: string, matchId: string, team: string, won: bool, eloBefore: int, eloAfter: int, eloChange: int}> */
    private array $records = [];

    /** @param list<EloHistoryEntry> $entries */
    public function recordMany(array $entries): void
    {
        foreach ($entries as $entry) {
            foreach ($this->records as $record) {
                if ($record['playerId'] === $entry->playerId && $record['matchId'] === $entry->matchId) {
                    continue 2;
                }
            }

            $this->records[] = [
                'playerId' => $entry->playerId,
                'matchId' => $entry->matchId,
                'team' => $entry->team,
                'won' => $entry->won,
                'eloBefore' => $entry->eloBefore,
                'eloAfter' => $entry->eloAfter,
                'eloChange' => $entry->eloChange,
            ];
        }
    }

    /** @return list<array{playerId: string, matchId: string, team: string, won: bool, eloBefore: int, eloAfter: int, eloChange: int}> */
    public function all(): array
    {
        return $this->records;
    }

    public function countForMatch(string $matchId): int
    {
        return \count(array_filter($this->records, fn (array $r) => $r['matchId'] === $matchId));
    }

    public function findByMatchId(string $matchId): array
    {
        return $this->findByMatchIds([$matchId]);
    }

    public function findByMatchIds(array $matchIds): array
    {
        return array_values(array_map(
            fn (array $record): EloHistoryEntry => EloHistoryEntry::from(
                playerId: $record['playerId'],
                matchId: $record['matchId'],
                team: $record['team'],
                won: $record['won'],
                eloBefore: $record['eloBefore'],
                eloAfter: $record['eloAfter'],
                eloChange: $record['eloChange'],
            ),
            array_filter($this->records, fn (array $record): bool => in_array($record['matchId'], $matchIds, true)),
        ));
    }
}
