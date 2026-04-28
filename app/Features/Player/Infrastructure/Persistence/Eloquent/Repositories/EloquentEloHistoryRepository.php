<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Persistence\Eloquent\Repositories;

use App\Features\Player\Domain\Repositories\EloHistoryRepositoryInterface;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\EloHistory;
use Ramsey\Uuid\Uuid;

final class EloquentEloHistoryRepository implements EloHistoryRepositoryInterface
{
    public function record(string $playerId, string $matchId, int $eloBefore, int $eloAfter, int $eloChange): void
    {
        EloHistory::create([
            'id' => Uuid::uuid4()->toString(),
            'player_id' => $playerId,
            'match_id' => $matchId,
            'elo_before' => $eloBefore,
            'elo_after' => $eloAfter,
            'elo_change' => $eloChange,
            'recorded_at' => now(),
        ]);
    }
}
