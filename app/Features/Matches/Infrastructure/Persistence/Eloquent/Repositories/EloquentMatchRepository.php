<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Persistence\Eloquent\Repositories;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Mappers\MatchMapper;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Models\MatchConfirmation;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Models\MatchModel as EloquentMatch;
use Ramsey\Uuid\Uuid;

final class EloquentMatchRepository implements MatchRepositoryInterface
{
    public function __construct(
        private readonly MatchMapper $mapper,
    ) {}

    public function findById(MatchId $id): ?PadelMatch
    {
        $model = EloquentMatch::with('confirmations')->find($id->value());

        return $model ? $this->mapper->toDomain($model, $this->extractConfirmedPlayerIds($model)) : null;
    }

    public function findByIdWithLock(MatchId $id): ?PadelMatch
    {
        $model = EloquentMatch::with('confirmations')->lockForUpdate()->find($id->value());

        return $model ? $this->mapper->toDomain($model, $this->extractConfirmedPlayerIds($model)) : null;
    }

    public function save(PadelMatch $match): void
    {
        $data = $this->mapper->toPersistence($match);

        EloquentMatch::updateOrCreate(['id' => $data['id']], $data);

        foreach ($match->confirmedPlayerIds() as $playerId) {
            MatchConfirmation::firstOrCreate(
                ['match_id' => $match->id()->value(), 'player_id' => $playerId->value()],
                ['id' => Uuid::uuid4()->toString(), 'confirmed_at' => now()],
            );
        }
    }

    /** @return list<PadelMatch> */
    public function findByPlayerId(PlayerId $playerId, ?string $filter = null): array
    {
        $id = $playerId->value();

        $query = EloquentMatch::with('confirmations')
            ->where(function ($q) use ($id) {
                $q->where('team_a_player1_id', $id)
                    ->orWhere('team_a_player2_id', $id)
                    ->orWhere('team_b_player1_id', $id)
                    ->orWhere('team_b_player2_id', $id);
            })
            ->orderByDesc('created_at');

        if ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'won') {
            $query->where('status', 'validated')
                ->where(function ($q) use ($id) {
                    $q->where(function ($q2) use ($id) {
                        $q2->whereIn('team_a_player1_id', [$id])
                            ->orWhereIn('team_a_player2_id', [$id]);
                    })->whereRaw('team_a_score > team_b_score')
                        ->orWhere(function ($q2) use ($id) {
                            $q2->whereIn('team_b_player1_id', [$id])
                                ->orWhereIn('team_b_player2_id', [$id]);
                        })->whereRaw('team_b_score > team_a_score');
                });
        } elseif ($filter === 'lost') {
            $query->where('status', 'validated')
                ->where(function ($q) use ($id) {
                    $q->where(function ($q2) use ($id) {
                        $q2->whereIn('team_a_player1_id', [$id])
                            ->orWhereIn('team_a_player2_id', [$id]);
                    })->whereRaw('team_a_score < team_b_score')
                        ->orWhere(function ($q2) use ($id) {
                            $q2->whereIn('team_b_player1_id', [$id])
                                ->orWhereIn('team_b_player2_id', [$id]);
                        })->whereRaw('team_b_score < team_a_score');
                });
        }

        return $query->get()
            ->map(fn (EloquentMatch $m) => $this->mapper->toDomain($m, $this->extractConfirmedPlayerIds($m)))
            ->all();
    }

    public function deleteConfirmations(MatchId $matchId): void
    {
        MatchConfirmation::where('match_id', $matchId->value())->delete();
    }

    private function extractConfirmedPlayerIds(EloquentMatch $model): array
    {
        return $model->confirmations->pluck('player_id')->all();
    }
}
