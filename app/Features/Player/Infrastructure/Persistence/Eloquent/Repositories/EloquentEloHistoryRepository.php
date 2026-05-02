<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Persistence\Eloquent\Repositories;

use App\Features\Player\Domain\Repositories\EloHistoryRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\EloHistoryEntry;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\EloHistory;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class EloquentEloHistoryRepository implements EloHistoryRepositoryInterface
{
    /** @param list<EloHistoryEntry> $entries */
    public function recordMany(array $entries): void
    {
        if ($entries === []) {
            return;
        }

        $recordedAt = now();

        DB::table((new EloHistory)->getTable())->insertOrIgnore(array_map(
            fn (EloHistoryEntry $entry): array => [
                'id' => Uuid::uuid4()->toString(),
                'player_id' => $entry->playerId,
                'match_id' => $entry->matchId,
                'team' => $entry->team,
                'won' => $entry->won,
                'elo_before' => $entry->eloBefore,
                'elo_after' => $entry->eloAfter,
                'elo_change' => $entry->eloChange,
                'recorded_at' => $recordedAt,
            ],
            $entries,
        ));
    }

    public function findByMatchId(string $matchId): array
    {
        return $this->findByMatchIds([$matchId]);
    }

    public function findByMatchIds(array $matchIds): array
    {
        if ($matchIds === []) {
            return [];
        }

        return EloHistory::query()
            ->whereIn('match_id', array_values(array_unique($matchIds)))
            ->get()
            ->map(fn (EloHistory $entry): EloHistoryEntry => EloHistoryEntry::from(
                playerId: $entry->player_id,
                matchId: $entry->match_id,
                team: $entry->team,
                won: $entry->won,
                eloBefore: $entry->elo_before,
                eloAfter: $entry->elo_after,
                eloChange: $entry->elo_change,
            ))
            ->all();
    }
}
