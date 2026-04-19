<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Persistence\Eloquent\Repositories;

use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\Repositories\MatchInvitationRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Mappers\MatchMapper;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Models\MatchInvitation as EloquentMatchInvitation;

final class EloquentMatchInvitationRepository implements MatchInvitationRepositoryInterface
{
    public function __construct(
        private readonly MatchMapper $mapper,
    ) {}

    public function findById(MatchInvitationId $id): ?MatchInvitation
    {
        $model = EloquentMatchInvitation::find($id->value());

        return $model ? $this->mapper->invitationToDomain($model) : null;
    }

    public function findByMatchAndSlot(MatchId $matchId, string $team, int $position): ?MatchInvitation
    {
        $model = EloquentMatchInvitation::where('match_id', $matchId->value())
            ->where('team', $team)
            ->where('position', $position)
            ->first();

        return $model ? $this->mapper->invitationToDomain($model) : null;
    }

    public function findByMatchAndInvitee(MatchId $matchId, PlayerId $inviteeId): ?MatchInvitation
    {
        $model = EloquentMatchInvitation::where('match_id', $matchId->value())
            ->where('invitee_id', $inviteeId->value())
            ->first();

        return $model ? $this->mapper->invitationToDomain($model) : null;
    }

    public function save(MatchInvitation $invitation): void
    {
        $data = $this->mapper->invitationToPersistence($invitation);
        EloquentMatchInvitation::updateOrCreate(['id' => $data['id']], $data);
    }

    /** @return list<MatchInvitation> */
    public function findPendingByInvitee(PlayerId $inviteeId): array
    {
        return EloquentMatchInvitation::where('invitee_id', $inviteeId->value())
            ->where('status', 'pending')
            ->orderByDesc('invited_at')
            ->get()
            ->map(fn (EloquentMatchInvitation $m) => $this->mapper->invitationToDomain($m))
            ->all();
    }
}
