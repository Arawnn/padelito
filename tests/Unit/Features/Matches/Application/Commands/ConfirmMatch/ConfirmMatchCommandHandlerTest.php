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
use App\Features\Matches\Domain\Services\EloCalculationService;
use App\Features\Matches\Domain\ValueObjects\InvitationStatus;
use App\Features\Matches\Domain\ValueObjects\InvitationType;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use Tests\Shared\Mother\Fake\InMemoryEloHistoryRepository;
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
final class ConfirmMatchCommandHandlerTest extends TestCase
{
    private const P1 = '00000000-0000-0000-0000-000000000001';

    private const P2 = '00000000-0000-0000-0000-000000000002';

    private const P3 = '00000000-0000-0000-0000-000000000003';

    private const P4 = '00000000-0000-0000-0000-000000000004';

    private const PENDING_INVITEE = '00000000-0000-0000-0000-000000000005';

    private InMemoryMatchRepository $matchRepository;

    private InMemoryPlayerRepository $playerRepository;

    private InMemoryEloHistoryRepository $eloHistoryRepository;

    private SpyEventDispatcher $eventDispatcher;

    private SetsDetail $twoZero;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matchRepository = new InMemoryMatchRepository;
        $this->playerRepository = new InMemoryPlayerRepository;
        $this->eloHistoryRepository = new InMemoryEloHistoryRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
        $this->twoZero = SetsDetail::fromArray([['a' => 6, 'b' => 3], ['a' => 6, 'b' => 2]]);

        foreach ([self::P1, self::P2, self::P3, self::P4, self::PENDING_INVITEE] as $i => $id) {
            $this->playerRepository->save(
                PlayerMother::create()->withId($id)->withUsername('player_'.$i)->build()
            );
        }
    }

    private function readyDoublesMatch(): \App\Features\Matches\Domain\Entities\PadelMatch
    {
        return MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail($this->twoZero)
            ->build();
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

    public function test_all_confirmations_validate_friendly_match(): void
    {
        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        foreach ([self::P1, self::P2, self::P3, self::P4] as $playerId) {
            $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), $playerId));
        }

        $updated = $this->matchRepository->findById($match->id());
        $this->assertTrue($updated->status()->isValidated());
        $this->assertTrue($this->eventDispatcher->dispatched(MatchValidated::class));
    }

    public function test_friendly_match_does_not_change_elo(): void
    {
        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        $eloBefore = $this->playerRepository->findById(\App\Features\Player\Domain\ValueObjects\Id::fromString(self::P1))->stats()->eloRating()->value();

        foreach ([self::P1, self::P2, self::P3, self::P4] as $playerId) {
            $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), $playerId));
        }

        $eloAfter = $this->playerRepository->findById(\App\Features\Player\Domain\ValueObjects\Id::fromString(self::P1))->stats()->eloRating()->value();

        $this->assertEquals($eloBefore, $eloAfter);
    }

    public function test_ranked_match_changes_elo(): void
    {
        $match = MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail($this->twoZero)
            ->asRanked()
            ->build();
        $this->matchRepository->save($match);

        $eloBeforeWinner = $this->playerRepository->findById(\App\Features\Player\Domain\ValueObjects\Id::fromString(self::P1))->stats()->eloRating()->value();
        $eloBeforeLoser = $this->playerRepository->findById(\App\Features\Player\Domain\ValueObjects\Id::fromString(self::P3))->stats()->eloRating()->value();

        foreach ([self::P1, self::P2, self::P3, self::P4] as $playerId) {
            $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), $playerId));
        }

        $eloAfterWinner = $this->playerRepository->findById(\App\Features\Player\Domain\ValueObjects\Id::fromString(self::P1))->stats()->eloRating()->value();
        $eloAfterLoser = $this->playerRepository->findById(\App\Features\Player\Domain\ValueObjects\Id::fromString(self::P3))->stats()->eloRating()->value();

        $this->assertGreaterThan($eloBeforeWinner, $eloAfterWinner);
        $this->assertLessThan($eloBeforeLoser, $eloAfterLoser);
    }

    public function test_friendly_match_updates_win_loss_stats(): void
    {
        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        foreach ([self::P1, self::P2, self::P3, self::P4] as $playerId) {
            $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), $playerId));
        }

        $winner = $this->playerRepository->findById(\App\Features\Player\Domain\ValueObjects\Id::fromString(self::P1));
        $loser = $this->playerRepository->findById(\App\Features\Player\Domain\ValueObjects\Id::fromString(self::P3));

        $this->assertEquals(1, $winner->stats()->totalWins()->value());
        $this->assertEquals(0, $winner->stats()->totalLosses()->value());
        $this->assertEquals(0, $loser->stats()->totalWins()->value());
        $this->assertEquals(1, $loser->stats()->totalLosses()->value());
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

    public function test_ranked_match_records_elo_history(): void
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

        $this->assertEquals(4, $this->eloHistoryRepository->countForMatch($match->id()->value()));
    }

    public function test_friendly_match_records_no_elo_history(): void
    {
        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        foreach ([self::P1, self::P2, self::P3, self::P4] as $playerId) {
            $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), $playerId));
        }

        $this->assertEquals(0, $this->eloHistoryRepository->countForMatch($match->id()->value()));
    }

    public function test_double_confirm_throws(): void
    {
        $this->expectException(PlayerAlreadyConfirmedException::class);

        $match = $this->readyDoublesMatch();
        $this->matchRepository->save($match);

        $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), self::P1));
        $this->makeHandler()(new ConfirmMatchCommand($match->id()->value(), self::P1));
    }

    private function makeHandler(): ConfirmMatchCommandHandler
    {
        return new ConfirmMatchCommandHandler(
            matchRepository: $this->matchRepository,
            playerRepository: $this->playerRepository,
            eloCalculationService: new EloCalculationService,
            eloHistoryRepository: $this->eloHistoryRepository,
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
