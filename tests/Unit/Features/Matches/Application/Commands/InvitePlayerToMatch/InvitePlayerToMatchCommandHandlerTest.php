<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Application\Commands\InvitePlayerToMatch;

use App\Features\Matches\Application\Commands\InvitePlayerToMatch\InvitePlayerToMatchCommand;
use App\Features\Matches\Application\Commands\InvitePlayerToMatch\InvitePlayerToMatchCommandHandler;
use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\Exceptions\DuplicatePlayerInMatchException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyCancelledException;
use App\Features\Matches\Domain\Exceptions\PlayerNotRegisteredInAppException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use Tests\Shared\Mother\Fake\ImmediateTransactionManager;
use Tests\Shared\Mother\Fake\InMemoryMatchInvitationRepository;
use Tests\Shared\Mother\Fake\InMemoryMatchRepository;
use Tests\Shared\Mother\Fake\InMemoryPlayerRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\MatchMother;
use Tests\Shared\Mother\PlayerMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class InvitePlayerToMatchCommandHandlerTest extends TestCase
{
    private const CREATOR_ID = '00000000-0000-0000-0000-000000000001';

    private const INVITEE_ID = '00000000-0000-0000-0000-000000000002';

    private InMemoryMatchRepository $matchRepository;

    private InMemoryMatchInvitationRepository $invitationRepository;

    private InMemoryPlayerRepository $playerRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matchRepository = new InMemoryMatchRepository;
        $this->invitationRepository = new InMemoryMatchInvitationRepository;
        $this->playerRepository = new InMemoryPlayerRepository;

        $this->playerRepository->save(PlayerMother::create()->withId(self::CREATOR_ID)->build());
        $this->playerRepository->save(PlayerMother::create()->withId(self::INVITEE_ID)->withUsername('invitee')->build());
    }

    public function test_it_creates_an_invitation(): void
    {
        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->build();
        $this->matchRepository->save($match);

        $invitation = $this->makeHandler()($this->validCommand($match->id()->value()));

        $this->assertInstanceOf(MatchInvitation::class, $invitation);
        $this->assertEquals(self::INVITEE_ID, $invitation->inviteeId()->value());
        $this->assertTrue($invitation->status()->isPending());
        $this->assertEquals('opponent', $invitation->type()->value()->value);
    }

    public function test_non_creator_cannot_invite(): void
    {
        $this->expectException(UnauthorizedMatchOperationException::class);

        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new InvitePlayerToMatchCommand(
            matchId: $match->id()->value(),
            inviterId: '99999999-9999-9999-9999-999999999999',
            inviteeId: self::INVITEE_ID,
            type: 'opponent',
        ));
    }

    public function test_cannot_invite_to_cancelled_match(): void
    {
        $this->expectException(MatchAlreadyCancelledException::class);

        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->withStatus('cancelled')->build();
        $this->matchRepository->save($match);

        $this->makeHandler()($this->validCommand($match->id()->value()));
    }

    public function test_cannot_invite_unregistered_player(): void
    {
        $this->expectException(PlayerNotRegisteredInAppException::class);

        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new InvitePlayerToMatchCommand(
            matchId: $match->id()->value(),
            inviterId: self::CREATOR_ID,
            inviteeId: 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            type: 'opponent',
        ));
    }

    public function test_cannot_invite_creator_again(): void
    {
        $this->expectException(DuplicatePlayerInMatchException::class);

        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new InvitePlayerToMatchCommand(
            matchId: $match->id()->value(),
            inviterId: self::CREATOR_ID,
            inviteeId: self::CREATOR_ID,
            type: 'partner',
        ));
    }

    private function validCommand(string $matchId): InvitePlayerToMatchCommand
    {
        return new InvitePlayerToMatchCommand(
            matchId: $matchId,
            inviterId: self::CREATOR_ID,
            inviteeId: self::INVITEE_ID,
            type: 'opponent',
        );
    }

    private function makeHandler(): InvitePlayerToMatchCommandHandler
    {
        return new InvitePlayerToMatchCommandHandler(
            matchRepository: $this->matchRepository,
            invitationRepository: $this->invitationRepository,
            playerRepository: $this->playerRepository,
            transactionManager: new ImmediateTransactionManager,
            eventDispatcher: new SpyEventDispatcher,
        );
    }
}
