<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Application\Commands\RespondToMatchInvitation;

use App\Features\Matches\Application\Commands\RespondToMatchInvitation\RespondToMatchInvitationCommand;
use App\Features\Matches\Application\Commands\RespondToMatchInvitation\RespondToMatchInvitationCommandHandler;
use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\Events\MatchInvitationAccepted;
use App\Features\Matches\Domain\Events\MatchInvitationDeclined;
use App\Features\Matches\Domain\Exceptions\MatchInvitationAlreadyRespondedException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use App\Features\Matches\Domain\ValueObjects\InvitationType;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\Team;
use Tests\Shared\Mother\Fake\ImmediateTransactionManager;
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
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: true,
        ));

        $invitation = $this->invitationRepository->findById(MatchInvitationId::fromString(self::INVITATION_ID));
        $this->assertTrue($invitation->status()->isAccepted());
        $this->assertTrue($this->eventDispatcher->dispatched(MatchInvitationAccepted::class));

        $match = $this->matchRepository->findById(MatchId::fromString(self::MATCH_ID));
        $this->assertEquals(self::INVITEE_ID, $match->teamBPlayer1Id()->value());
    }

    public function test_declining_invitation_does_not_assign_player(): void
    {
        $this->invitationRepository->save($this->pendingInvitation());

        $this->makeHandler()(new RespondToMatchInvitationCommand(
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: false,
        ));

        $invitation = $this->invitationRepository->findById(MatchInvitationId::fromString(self::INVITATION_ID));
        $this->assertTrue($invitation->status()->isDeclined());
        $this->assertTrue($this->eventDispatcher->dispatched(MatchInvitationDeclined::class));

        $match = $this->matchRepository->findById(MatchId::fromString(self::MATCH_ID));
        $this->assertNull($match->teamBPlayer1Id());
    }

    public function test_wrong_responder_cannot_accept(): void
    {
        $this->expectException(UnauthorizedMatchOperationException::class);

        $this->invitationRepository->save($this->pendingInvitation());

        $this->makeHandler()(new RespondToMatchInvitationCommand(
            invitationId: self::INVITATION_ID,
            responderId: '99999999-9999-9999-9999-999999999999',
            accept: true,
        ));
    }

    public function test_cannot_respond_twice(): void
    {
        $this->expectException(MatchInvitationAlreadyRespondedException::class);

        $this->invitationRepository->save($this->pendingInvitation());

        $handler = $this->makeHandler();
        $handler(new RespondToMatchInvitationCommand(
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: true,
        ));
        $handler(new RespondToMatchInvitationCommand(
            invitationId: self::INVITATION_ID,
            responderId: self::INVITEE_ID,
            accept: false,
        ));
    }

    private function pendingInvitation(): MatchInvitation
    {
        return MatchInvitation::reconstitute(
            id: MatchInvitationId::fromString(self::INVITATION_ID),
            matchId: MatchId::fromString(self::MATCH_ID),
            inviteeId: PlayerId::fromString(self::INVITEE_ID),
            team: Team::B(),
            type: InvitationType::opponent(),
            status: \App\Features\Matches\Domain\ValueObjects\InvitationStatus::pending(),
            invitedAt: new \DateTimeImmutable,
            respondedAt: null,
        );
    }

    private function makeHandler(): RespondToMatchInvitationCommandHandler
    {
        return new RespondToMatchInvitationCommandHandler(
            matchRepository: $this->matchRepository,
            invitationRepository: $this->invitationRepository,
            transactionManager: new ImmediateTransactionManager,
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
