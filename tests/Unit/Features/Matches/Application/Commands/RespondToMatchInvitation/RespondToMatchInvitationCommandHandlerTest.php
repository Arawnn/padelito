<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Application\Commands\RespondToMatchInvitation;

use App\Features\Matches\Application\Commands\RespondToMatchInvitation\RespondToMatchInvitationCommand;
use App\Features\Matches\Application\Commands\RespondToMatchInvitation\RespondToMatchInvitationCommandHandler;
use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\Events\MatchInvitationAccepted;
use App\Features\Matches\Domain\Events\MatchInvitationDeclined;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyCancelledException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyValidatedException;
use App\Features\Matches\Domain\Exceptions\MatchInvitationNotFoundException;
use App\Features\Matches\Domain\Exceptions\MatchTeamFullException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use App\Features\Matches\Domain\ValueObjects\InvitationStatus;
use App\Features\Matches\Domain\ValueObjects\InvitationType;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use Tests\Shared\Mother\Fake\InMemoryMatchInvitationRepository;
use Tests\Shared\Mother\Fake\InMemoryMatchRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\MatchMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RespondToMatchInvitationCommandHandlerTest extends TestCase
{
    private const CREATOR_ID = '00000000-0000-0000-0000-000000000001';

    private const INVITEE_ID = '00000000-0000-0000-0000-000000000002';

    private const MATCH_ID = '10000000-0000-0000-0000-000000000001';

    private const INVITATION_ID = '20000000-0000-0000-0000-000000000001';

    private const SECOND_INVITATION_ID = '20000000-0000-0000-0000-000000000002';

    private const SECOND_INVITEE_ID = '00000000-0000-0000-0000-000000000003';

    private InMemoryMatchRepository $matchRepository;

    private InMemoryMatchInvitationRepository $invitationRepository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matchRepository = new InMemoryMatchRepository;
        $this->invitationRepository = new InMemoryMatchInvitationRepository;
        $this->eventDispatcher = new SpyEventDispatcher;

        $match = MatchMother::create()->withId(self::MATCH_ID)->withCreator(self::CREATOR_ID)->build();
        $this->matchRepository->save($match);
    }

    public function test_accepting_invitation_assigns_player_to_match(): void
    {
        $this->invitationRepository->save($this->pendingInvitation());

        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: true,
        ));

        $invitation = $this->invitationRepository->findById(MatchInvitationId::fromString(self::INVITATION_ID));
        $this->assertTrue($invitation->status()->isAccepted());
        $this->assertTrue($this->eventDispatcher->dispatched(MatchInvitationAccepted::class));

        $match = $this->matchRepository->findById(MatchId::fromString(self::MATCH_ID));
        $this->assertEquals(self::INVITEE_ID, $match->teamAPlayer2Id()->value());
        $this->assertTrue($this->matchRepository->wasLockedFor(self::MATCH_ID));
    }

    public function test_declining_invitation_does_not_assign_player(): void
    {
        $this->invitationRepository->save($this->pendingInvitation());

        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: false,
        ));

        $invitation = $this->invitationRepository->findById(MatchInvitationId::fromString(self::INVITATION_ID));
        $this->assertTrue($invitation->status()->isDeclined());
        $this->assertTrue($this->eventDispatcher->dispatched(MatchInvitationDeclined::class));

        $match = $this->matchRepository->findById(MatchId::fromString(self::MATCH_ID));
        $this->assertNull($match->teamAPlayer2Id());
    }

    public function test_wrong_responder_cannot_accept(): void
    {
        $this->expectException(UnauthorizedMatchOperationException::class);

        $this->invitationRepository->save($this->pendingInvitation());

        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: '99999999-9999-9999-9999-999999999999',
            accept: true,
        ));
    }

    public function test_it_allows_updating_response_from_accepted_to_declined_and_removes_player_from_match(): void
    {
        $match = MatchMother::create()
            ->withId(self::MATCH_ID)
            ->withCreator(self::CREATOR_ID)
            ->withTeamAPlayer2(self::INVITEE_ID)
            ->build();
        $this->matchRepository->save($match);

        $this->invitationRepository->save($this->invitationWithStatus(
            invitationId: self::INVITATION_ID,
            inviteeId: self::INVITEE_ID,
            status: InvitationStatus::accepted(),
        ));

        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: false,
        ));

        $invitation = $this->invitationRepository->findById(MatchInvitationId::fromString(self::INVITATION_ID));
        $this->assertTrue($invitation->status()->isDeclined());

        $updatedMatch = $this->matchRepository->findById(MatchId::fromString(self::MATCH_ID));
        $this->assertNull($updatedMatch->teamAPlayer2Id());
    }

    public function test_it_allows_updating_response_from_declined_to_accepted_and_assigns_player(): void
    {
        $this->invitationRepository->save($this->invitationWithStatus(
            invitationId: self::INVITATION_ID,
            inviteeId: self::INVITEE_ID,
            status: InvitationStatus::declined(),
        ));

        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: true,
        ));

        $invitation = $this->invitationRepository->findById(MatchInvitationId::fromString(self::INVITATION_ID));
        $this->assertTrue($invitation->status()->isAccepted());

        $updatedMatch = $this->matchRepository->findById(MatchId::fromString(self::MATCH_ID));
        $this->assertEquals(self::INVITEE_ID, $updatedMatch->teamAPlayer2Id()?->value());
    }

    public function test_declined_to_accepted_fails_when_partner_slot_became_full(): void
    {
        $match = MatchMother::create()
            ->withId(self::MATCH_ID)
            ->withCreator(self::CREATOR_ID)
            ->withTeamAPlayer2(self::SECOND_INVITEE_ID)
            ->build();
        $this->matchRepository->save($match);

        $this->invitationRepository->save($this->invitationWithStatus(
            invitationId: self::INVITATION_ID,
            inviteeId: self::INVITEE_ID,
            status: InvitationStatus::declined(),
        ));

        $this->expectException(MatchTeamFullException::class);
        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: true,
        ));
    }

    public function test_second_partner_acceptance_is_blocked_when_slot_is_already_filled(): void
    {
        $this->invitationRepository->save($this->pendingInvitation());
        $this->invitationRepository->save($this->pendingPartnerInvitation(
            invitationId: self::SECOND_INVITATION_ID,
            inviteeId: self::SECOND_INVITEE_ID,
        ));

        $handler = $this->makeHandler();
        $handler(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: true,
        ));

        $this->expectException(MatchTeamFullException::class);
        $handler(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::SECOND_INVITATION_ID,
            responderId: self::SECOND_INVITEE_ID,
            accept: true,
        ));
    }

    public function test_cannot_accept_invitation_when_match_is_validated(): void
    {
        $validatedMatch = MatchMother::create()
            ->withId(self::MATCH_ID)
            ->withCreator(self::CREATOR_ID)
            ->withStatus('validated')
            ->build();
        $this->matchRepository->save($validatedMatch);
        $this->invitationRepository->save($this->pendingInvitation());

        $this->expectException(MatchAlreadyValidatedException::class);
        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: true,
        ));
    }

    public function test_cannot_decline_invitation_when_match_is_validated(): void
    {
        $validatedMatch = MatchMother::create()
            ->withId(self::MATCH_ID)
            ->withCreator(self::CREATOR_ID)
            ->withStatus('validated')
            ->build();
        $this->matchRepository->save($validatedMatch);
        $this->invitationRepository->save($this->pendingInvitation());

        $this->expectException(MatchAlreadyValidatedException::class);
        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: false,
        ));
    }

    public function test_cannot_respond_to_invitation_through_a_different_match_route(): void
    {
        $this->invitationRepository->save($this->pendingInvitation());

        $this->expectException(MatchInvitationNotFoundException::class);
        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: '99999999-9999-9999-9999-999999999999',
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: true,
        ));
    }

    public function test_cannot_respond_to_invitation_when_match_is_cancelled(): void
    {
        $cancelledMatch = MatchMother::create()
            ->withId(self::MATCH_ID)
            ->withCreator(self::CREATOR_ID)
            ->withStatus('cancelled')
            ->build();
        $this->matchRepository->save($cancelledMatch);
        $this->invitationRepository->save($this->pendingInvitation());

        $this->expectException(MatchAlreadyCancelledException::class);
        $this->makeHandler()(new RespondToMatchInvitationCommand(
            matchId: self::MATCH_ID,
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: true,
        ));
    }

    private function pendingInvitation(): MatchInvitation
    {
        return $this->invitationWithStatus(
            invitationId: self::INVITATION_ID,
            inviteeId: self::INVITEE_ID,
            status: InvitationStatus::pending(),
        );
    }

    private function pendingPartnerInvitation(string $invitationId, string $inviteeId): MatchInvitation
    {
        return $this->invitationWithStatus(
            invitationId: $invitationId,
            inviteeId: $inviteeId,
            status: InvitationStatus::pending(),
        );
    }

    private function invitationWithStatus(string $invitationId, string $inviteeId, InvitationStatus $status): MatchInvitation
    {
        return MatchInvitation::reconstitute(
            id: MatchInvitationId::fromString($invitationId),
            matchId: MatchId::fromString(self::MATCH_ID),
            inviteeId: PlayerId::fromString($inviteeId),
            type: InvitationType::partner(),
            status: $status,
            invitedAt: new \DateTimeImmutable,
            respondedAt: $status->isPending() ? null : new \DateTimeImmutable,
        );
    }

    private function makeHandler(): RespondToMatchInvitationCommandHandler
    {
        return new RespondToMatchInvitationCommandHandler(
            matchRepository: $this->matchRepository,
            invitationRepository: $this->invitationRepository,
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
