<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Application\Events;

use App\Features\Matches\Application\Events\CancelActiveInvitationsWhenMatchCancelled;
use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\Events\MatchCancelled;
use App\Features\Matches\Domain\Events\MatchInvitationCancelled;
use App\Features\Matches\Domain\ValueObjects\InvitationType;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use Tests\Shared\Mother\Fake\InMemoryMatchInvitationRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CancelActiveInvitationsWhenMatchCancelledTest extends TestCase
{
    private const MATCH_ID = '00000000-0000-0000-0000-000000000100';

    private const OTHER_MATCH_ID = '00000000-0000-0000-0000-000000000200';

    private const INVITER_ID = '00000000-0000-0000-0000-000000000001';

    private const PENDING_INVITEE_ID = '00000000-0000-0000-0000-000000000002';

    private const ACCEPTED_INVITEE_ID = '00000000-0000-0000-0000-000000000003';

    private const DECLINED_INVITEE_ID = '00000000-0000-0000-0000-000000000004';

    private const OTHER_INVITEE_ID = '00000000-0000-0000-0000-000000000005';

    public function test_it_cancels_active_invitations_for_cancelled_match(): void
    {
        $repository = new InMemoryMatchInvitationRepository;
        $eventDispatcher = new SpyEventDispatcher;

        $pendingInvitation = $this->pendingInvitation(self::MATCH_ID, self::PENDING_INVITEE_ID);
        $acceptedInvitation = $this->pendingInvitation(self::MATCH_ID, self::ACCEPTED_INVITEE_ID);
        $acceptedInvitation->accept();
        $acceptedInvitation->pullDomainEvents();
        $declinedInvitation = $this->pendingInvitation(self::MATCH_ID, self::DECLINED_INVITEE_ID);
        $declinedInvitation->decline();
        $declinedInvitation->pullDomainEvents();
        $otherMatchInvitation = $this->pendingInvitation(self::OTHER_MATCH_ID, self::OTHER_INVITEE_ID);

        $repository->save($pendingInvitation);
        $repository->save($acceptedInvitation);
        $repository->save($declinedInvitation);
        $repository->save($otherMatchInvitation);

        (new CancelActiveInvitationsWhenMatchCancelled($repository, $eventDispatcher))(
            new MatchCancelled(self::MATCH_ID)
        );

        $this->assertTrue($repository->findById($pendingInvitation->id())->status()->isCancelled());
        $this->assertTrue($repository->findById($acceptedInvitation->id())->status()->isCancelled());
        $this->assertTrue($repository->findById($declinedInvitation->id())->status()->isDeclined());
        $this->assertTrue($repository->findById($otherMatchInvitation->id())->status()->isPending());
        $this->assertSame(2, $eventDispatcher->count(MatchInvitationCancelled::class));
    }

    private function pendingInvitation(string $matchId, string $inviteeId): MatchInvitation
    {
        $invitation = MatchInvitation::invite(
            id: MatchInvitationId::generate(),
            matchId: MatchId::fromString($matchId),
            inviteeId: PlayerId::fromString($inviteeId),
            invitedByPlayerId: PlayerId::fromString(self::INVITER_ID),
            type: InvitationType::opponent(),
        );
        $invitation->pullDomainEvents();

        return $invitation;
    }
}
