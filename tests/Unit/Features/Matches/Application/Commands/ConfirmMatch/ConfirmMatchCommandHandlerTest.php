<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Application\Commands\ConfirmMatch;

use App\Features\Matches\Application\Commands\ConfirmMatch\ConfirmMatchCommand;
use App\Features\Matches\Application\Commands\ConfirmMatch\ConfirmMatchCommandHandler;
use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\Events\MatchValidated;
use App\Features\Matches\Domain\Exceptions\MatchNotReadyForConfirmationException;
use App\Features\Matches\Domain\Exceptions\PlayerAlreadyConfirmedException;
use App\Features\Matches\Domain\Exceptions\PlayerNotParticipantException;
use App\Features\Matches\Domain\ValueObjects\InvitationStatus;
use App\Features\Matches\Domain\ValueObjects\InvitationType;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
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
final class ConfirmMatchCommandHandlerTest extends TestCase
{
    private const P1 = '00000000-0000-0000-0000-000000000001';

    private const P2 = '00000000-0000-0000-0000-000000000002';

    private const P3 = '00000000-0000-0000-0000-000000000003';

    private const P4 = '00000000-0000-0000-0000-000000000004';

    private const PENDING_INVITEE = '00000000-0000-0000-0000-000000000005';

    private InMemoryMatchRepository $matchRepository;

    private SpyEventDispatcher $eventDispatcher;

    private SetsDetail $twoZero;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matchRepository = new InMemoryMatchRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
        $this->twoZero = SetsDetail::fromArray([['a' => 6, 'b' => 3], ['a' => 6, 'b' => 2]]);
    }

    public function test_partial_confirmation_does_not_validate(): void
    {
        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), self::P1));
        $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), self::P2));

        $updated = $this->matchRepository->findById($match->id());
        $this->assertFalse($updated->status()->isValidated());
        $this->assertFalse($this->eventDispatcher->dispatched(MatchValidated::class));
    }

    public function test_all_confirmations_validate_and_dispatch_match_validated_payload(): void
    {
        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        foreach ([self::P1, self::P2, self::P3, self::P4] as $playerId) {
            $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), $playerId));
        }

        $updated = $this->matchRepository->findById($match->id());
        $event = $this->eventDispatcher->first(MatchValidated::class);

        $this->assertTrue($updated->status()->isValidated());
        $this->assertInstanceOf(MatchValidated::class, $event);
        $this->assertSame($match->id()->value(), $event->matchId);
        $this->assertSame([self::P1, self::P2], $event->teamAPlayerIds);
        $this->assertSame([self::P3, self::P4], $event->teamBPlayerIds);
        $this->assertSame(2, $event->teamAScore);
        $this->assertSame(0, $event->teamBScore);
        $this->assertFalse($event->ranked);
    }

    public function test_ranked_match_validated_payload_marks_match_as_ranked(): void
    {
        $match = MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail($this->twoZero)
            ->asRanked()
            ->build();
        $this->matchRepository->save($match);

        foreach ([self::P1, self::P2, self::P3, self::P4] as $playerId) {
            $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), $playerId));
        }

        $event = $this->eventDispatcher->first(MatchValidated::class);

        $this->assertInstanceOf(MatchValidated::class, $event);
        $this->assertTrue($event->ranked);
    }

    public function test_non_participant_cannot_confirm(): void
    {
        $this->expectException(PlayerNotParticipantException::class);

        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), '99999999-9999-9999-9999-999999999999'));
    }

    public function test_pending_invitee_cannot_confirm_without_being_assigned_to_a_team(): void
    {
        $this->expectException(PlayerNotParticipantException::class);

        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        $invitationRepository = new InMemoryMatchInvitationRepository;
        $invitationRepository->save(MatchInvitation::reconstitute(
            id: MatchInvitationId::fromString('20000000-0000-0000-0000-000000000001'),
            matchId: MatchId::fromString($match->id()->value()),
            inviteeId: PlayerId::fromString(self::PENDING_INVITEE),
            type: InvitationType::partner(),
            status: InvitationStatus::pending(),
            invitedAt: new \DateTimeImmutable,
            respondedAt: null,
        ));

        $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), self::PENDING_INVITEE));
    }

    public function test_cannot_confirm_incomplete_match(): void
    {
        $this->expectException(MatchNotReadyForConfirmationException::class);

        $match = MatchMother::create()->withCreator(self::P1)->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), self::P1));
    }

    public function test_double_confirm_throws(): void
    {
        $this->expectException(PlayerAlreadyConfirmedException::class);

        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), self::P1));
        $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), self::P1));
    }

    private function readyDoublesMatch(): \App\Features\Matches\Domain\Entities\PadelMatch
    {
        return MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail($this->twoZero)
            ->build();
    }

    private function makeHandler(): ConfirmMatchCommandHandler
    {
        return new ConfirmMatchCommandHandler(
            matchRepository: $this->matchRepository,
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
