<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;

final class InMemoryMatchRepository implements MatchRepositoryInterface
{
    /** @var array<string, PadelMatch> */
    private array $store = [];

    /** @var array<string, list<string>> */
    private array $deletedConfirmations = [];

    /** @var list<string> */
    private array $lockedMatchIds = [];

    public function findById(MatchId $id): ?PadelMatch
    {
        return $this->store[$id->value()] ?? null;
    }

    public function findByIdWithLock(MatchId $id): ?PadelMatch
    {
        $this->lockedMatchIds[] = $id->value();

        return $this->findById($id);
    }

    public function save(PadelMatch $match): void
    {
        $this->store[$match->id()->value()] = $match;
    }

    /** @return list<PadelMatch> */
    public function findByPlayerId(PlayerId $playerId, ?string $filter = null): array
    {
        $id = $playerId->value();

        $matches = array_values(array_filter($this->store, function (PadelMatch $m) use ($id) {
            return $m->teamAPlayer1Id()->value() === $id
                || $m->teamAPlayer2Id()?->value() === $id
                || $m->teamBPlayer1Id()?->value() === $id
                || $m->teamBPlayer2Id()?->value() === $id;
        }));

        if ($filter === 'pending') {
            $matches = array_values(array_filter($matches, fn (PadelMatch $m) => $m->status()->isPending()));
        } elseif ($filter === 'won') {
            $matches = array_values(array_filter($matches, function (PadelMatch $m) use ($id) {
                if (! $m->status()->isValidated()) {
                    return false;
                }
                $winner = $m->winningTeam();
                if ($winner === null) {
                    return false;
                }

                $onTeamA = $m->teamAPlayer1Id()->value() === $id || $m->teamAPlayer2Id()?->value() === $id;

                return ($winner->isA() && $onTeamA) || (! $winner->isA() && ! $onTeamA);
            }));
        } elseif ($filter === 'lost') {
            $matches = array_values(array_filter($matches, function (PadelMatch $m) use ($id) {
                if (! $m->status()->isValidated()) {
                    return false;
                }
                $winner = $m->winningTeam();
                if ($winner === null) {
                    return false;
                }

                $onTeamA = $m->teamAPlayer1Id()->value() === $id || $m->teamAPlayer2Id()?->value() === $id;

                return ($winner->isA() && ! $onTeamA) || (! $winner->isA() && $onTeamA);
            }));
        }

        return $matches;
    }

    public function deleteConfirmations(MatchId $matchId): void
    {
        $this->deletedConfirmations[] = $matchId->value();
    }

    public function confirmationsWereDeletedFor(string $matchId): bool
    {
        return in_array($matchId, $this->deletedConfirmations, true);
    }

    public function wasLockedFor(string $matchId): bool
    {
        return in_array($matchId, $this->lockedMatchIds, true);
    }
}
