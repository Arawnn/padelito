<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\States;

use App\Features\Matches\Domain\Entities\PadelMatch;
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

interface MatchStateInterface
{
    public function status(): MatchStatus;

    public function canAcceptInvitation(PadelMatch $match, PlayerId $playerId, Team $team): bool;

    public function ensureCanInvitePlayer(PadelMatch $match, PlayerId $playerId, Team $team): void;

    public function ensureCanRespondToInvitation(): void;

    public function ensureCanUpdate(): void;

    public function assignPlayer(PadelMatch $match, PlayerId $playerId, Team $team): void;

    public function removePlayer(PadelMatch $match, PlayerId $playerId): void;

    public function confirm(PadelMatch $match, PlayerId $playerId): void;

    public function updateCourtName(PadelMatch $match, ?CourtName $courtName): void;

    public function updateMatchDate(PadelMatch $match, ?DateTimeImmutable $matchDate): void;

    public function updateNotes(PadelMatch $match, ?Notes $notes): void;

    public function updateSetsDetail(PadelMatch $match, ?SetsDetail $setsDetail): void;

    public function updateSetsToWin(PadelMatch $match, SetsToWin $setsToWin): void;

    public function updateFormat(PadelMatch $match, MatchFormat $format): void;

    public function updateType(PadelMatch $match, MatchType $type): void;

    public function cancel(PadelMatch $match): void;
}
