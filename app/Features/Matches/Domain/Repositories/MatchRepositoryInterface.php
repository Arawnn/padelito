<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Repositories;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;

interface MatchRepositoryInterface
{
    public function findById(MatchId $id): ?PadelMatch;

    public function findByIdWithLock(MatchId $id): ?PadelMatch;

    public function save(PadelMatch $match): void;

    /** @return list<PadelMatch> */
    public function findByPlayerId(PlayerId $playerId, ?string $filter = null): array;

    public function deleteConfirmations(MatchId $matchId): void;
}
