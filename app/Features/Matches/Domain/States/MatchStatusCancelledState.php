<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\States;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyCancelledException;
use App\Features\Matches\Domain\ValueObjects\CourtName;
use App\Features\Matches\Domain\ValueObjects\MatchFormat;
use App\Features\Matches\Domain\ValueObjects\MatchStatus;
use App\Features\Matches\Domain\ValueObjects\MatchType;
use App\Features\Matches\Domain\ValueObjects\Notes;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use App\Features\Matches\Domain\ValueObjects\SetsToWin;
use App\Features\Matches\Domain\ValueObjects\Team;
use DateTimeImmutable;

final readonly class MatchStatusCancelledState implements MatchStateInterface
{
    public function status(): MatchStatus
    {
        return MatchStatus::cancelled();
    }

    public function canAcceptInvitation(PadelMatch $match, PlayerId $playerId, Team $team): bool
    {
        return false;
    }

    public function ensureCanInvitePlayer(PadelMatch $match, PlayerId $playerId, Team $team): void
    {
        $this->throwAlreadyCancelled();
    }

    public function ensureCanRespondToInvitation(): void
    {
        $this->throwAlreadyCancelled();
    }

    public function ensureCanUpdate(): void
    {
        $this->throwAlreadyCancelled();
    }

    public function assignPlayer(PadelMatch $match, PlayerId $playerId, Team $team): void
    {
        $this->throwAlreadyCancelled();
    }

    public function removePlayer(PadelMatch $match, PlayerId $playerId): void
    {
        $this->throwAlreadyCancelled();
    }

    public function confirm(PadelMatch $match, PlayerId $playerId): void
    {
        $this->throwAlreadyCancelled();
    }

    public function updateCourtName(PadelMatch $match, ?CourtName $courtName): void
    {
        $this->throwAlreadyCancelled();
    }

    public function updateMatchDate(PadelMatch $match, ?DateTimeImmutable $matchDate): void
    {
        $this->throwAlreadyCancelled();
    }

    public function updateNotes(PadelMatch $match, ?Notes $notes): void
    {
        $this->throwAlreadyCancelled();
    }

    public function updateSetsDetail(PadelMatch $match, ?SetsDetail $setsDetail): void
    {
        $this->throwAlreadyCancelled();
    }

    public function updateSetsToWin(PadelMatch $match, SetsToWin $setsToWin): void
    {
        $this->throwAlreadyCancelled();
    }

    public function updateFormat(PadelMatch $match, MatchFormat $format): void
    {
        $this->throwAlreadyCancelled();
    }

    public function updateType(PadelMatch $match, MatchType $type): void
    {
        $this->throwAlreadyCancelled();
    }

    public function cancel(PadelMatch $match): void
    {
        $this->throwAlreadyCancelled();
    }

    private function throwAlreadyCancelled(): never
    {
        throw MatchAlreadyCancelledException::create();
    }
}
