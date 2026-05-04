<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\States;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyValidatedException;
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

final readonly class MatchStatusValidatedState implements MatchStateInterface
{
    public function status(): MatchStatus
    {
        return MatchStatus::validated();
    }

    public function canAcceptInvitation(PadelMatch $match, PlayerId $playerId, Team $team): bool
    {
        return false;
    }

    public function ensureCanInvitePlayer(PadelMatch $match, PlayerId $playerId, Team $team): void
    {
        $this->throwAlreadyValidated();
    }

    public function ensureCanRespondToInvitation(): void
    {
        $this->throwAlreadyValidated();
    }

    public function ensureCanUpdate(): void
    {
        $this->throwAlreadyValidated();
    }

    public function assignPlayer(PadelMatch $match, PlayerId $playerId, Team $team): void
    {
        $this->throwAlreadyValidated();
    }

    public function removePlayer(PadelMatch $match, PlayerId $playerId): void
    {
        $this->throwAlreadyValidated();
    }

    public function confirm(PadelMatch $match, PlayerId $playerId): void
    {
        $this->throwAlreadyValidated();
    }

    public function updateCourtName(PadelMatch $match, ?CourtName $courtName): void
    {
        $this->throwAlreadyValidated();
    }

    public function updateMatchDate(PadelMatch $match, ?DateTimeImmutable $matchDate): void
    {
        $this->throwAlreadyValidated();
    }

    public function updateNotes(PadelMatch $match, ?Notes $notes): void
    {
        $this->throwAlreadyValidated();
    }

    public function updateSetsDetail(PadelMatch $match, ?SetsDetail $setsDetail): void
    {
        $this->throwAlreadyValidated();
    }

    public function updateSetsToWin(PadelMatch $match, SetsToWin $setsToWin): void
    {
        $this->throwAlreadyValidated();
    }

    public function updateFormat(PadelMatch $match, MatchFormat $format): void
    {
        $this->throwAlreadyValidated();
    }

    public function updateType(PadelMatch $match, MatchType $type): void
    {
        $this->throwAlreadyValidated();
    }

    public function cancel(PadelMatch $match): void
    {
        $this->throwAlreadyValidated();
    }

    private function throwAlreadyValidated(): never
    {
        throw MatchAlreadyValidatedException::create();
    }
}
