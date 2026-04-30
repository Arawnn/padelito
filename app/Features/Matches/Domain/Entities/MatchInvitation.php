<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Entities;

use App\Features\Matches\Domain\Events\MatchInvitationAccepted;
use App\Features\Matches\Domain\Events\MatchInvitationDeclined;
use App\Features\Matches\Domain\Events\PlayerInvitedToMatch;
use App\Features\Matches\Domain\ValueObjects\InvitationStatus;
use App\Features\Matches\Domain\ValueObjects\InvitationType;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Shared\Domain\Entities\AggregateRoot;
use DateTimeImmutable;

final class MatchInvitation extends AggregateRoot
{
    private function __construct(
        private readonly MatchInvitationId $id,
        private readonly MatchId $matchId,
        private readonly PlayerId $inviteeId,
        private readonly InvitationType $type,
        private InvitationStatus $status,
        private readonly DateTimeImmutable $invitedAt,
        private ?DateTimeImmutable $respondedAt,
    ) {}

    public static function invite(
        MatchInvitationId $id,
        MatchId $matchId,
        PlayerId $inviteeId,
        PlayerId $invitedByPlayerId,
        InvitationType $type,
    ): self {
        $invitation = new self(
            id: $id,
            matchId: $matchId,
            inviteeId: $inviteeId,
            type: $type,
            status: InvitationStatus::pending(),
            invitedAt: new DateTimeImmutable,
            respondedAt: null,
        );

        $invitation->recordDomainEvent(new PlayerInvitedToMatch(
            matchId: $matchId->value(),
            invitedPlayerId: $inviteeId->value(),
            invitedByPlayerId: $invitedByPlayerId->value(),
            invitationId: $id->value(),
        ));

        return $invitation;
    }

    public static function reconstitute(
        MatchInvitationId $id,
        MatchId $matchId,
        PlayerId $inviteeId,
        InvitationType $type,
        InvitationStatus $status,
        DateTimeImmutable $invitedAt,
        ?DateTimeImmutable $respondedAt,
    ): self {
        return new self(
            id: $id,
            matchId: $matchId,
            inviteeId: $inviteeId,
            type: $type,
            status: $status,
            invitedAt: $invitedAt,
            respondedAt: $respondedAt,
        );
    }

    public function accept(): void
    {
        if ($this->status->isAccepted()) {
            return;
        }

        $this->status = InvitationStatus::accepted();
        $this->respondedAt = new DateTimeImmutable;
        $this->recordDomainEvent(new MatchInvitationAccepted(
            matchId: $this->matchId->value(),
            playerId: $this->inviteeId->value(),
            invitationId: $this->id->value(),
        ));
    }

    public function decline(): void
    {
        if ($this->status->isDeclined()) {
            return;
        }

        $this->status = InvitationStatus::declined();
        $this->respondedAt = new DateTimeImmutable;
        $this->recordDomainEvent(new MatchInvitationDeclined(
            matchId: $this->matchId->value(),
            playerId: $this->inviteeId->value(),
            invitationId: $this->id->value(),
        ));
    }

    public function id(): MatchInvitationId
    {
        return $this->id;
    }

    public function matchId(): MatchId
    {
        return $this->matchId;
    }

    public function inviteeId(): PlayerId
    {
        return $this->inviteeId;
    }

    public function type(): InvitationType
    {
        return $this->type;
    }

    public function status(): InvitationStatus
    {
        return $this->status;
    }

    public function invitedAt(): DateTimeImmutable
    {
        return $this->invitedAt;
    }

    public function respondedAt(): ?DateTimeImmutable
    {
        return $this->respondedAt;
    }
}
