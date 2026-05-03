<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Application\Commands\UpdateMatch;

use App\Features\Matches\Application\Commands\UpdateMatch\UpdateMatchCommand;
use App\Features\Matches\Application\Commands\UpdateMatch\UpdateMatchCommandHandler;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyCancelledException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use App\Features\Matches\Domain\ValueObjects\CourtName;
use App\Features\Matches\Domain\ValueObjects\Notes;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use DateTimeImmutable;
use Tests\Shared\Mother\Fake\InMemoryMatchRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\MatchMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UpdateMatchCommandHandlerTest extends TestCase
{
    private const CREATOR_ID = '00000000-0000-0000-0000-000000000001';

    private const P2 = '00000000-0000-0000-0000-000000000002';

    private const P3 = '00000000-0000-0000-0000-000000000003';

    private const P4 = '00000000-0000-0000-0000-000000000004';

    private InMemoryMatchRepository $matchRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matchRepository = new InMemoryMatchRepository;
    }

    public function test_updating_sets_detail_resets_confirmations(): void
    {
        $match = MatchMother::create()
            ->withFullDoublesLineup(self::CREATOR_ID, self::P2, self::P3, self::P4)
            ->withConfirmedPlayerIds([self::CREATOR_ID, self::P2])
            ->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new UpdateMatchCommand(
            matchId: $match->id()->value(),
            requesterId: self::CREATOR_ID,
            setsDetail: [['a' => 6, 'b' => 3], ['a' => 6, 'b' => 2]],
        ));

        $updated = $this->matchRepository->findById($match->id());
        $this->assertEmpty($updated->confirmedPlayerIds());
        $this->assertTrue($this->matchRepository->confirmationsWereDeletedFor($match->id()->value()));
    }

    public function test_updating_court_name_does_not_reset_confirmations(): void
    {
        $match = MatchMother::create()
            ->withFullDoublesLineup(self::CREATOR_ID, self::P2, self::P3, self::P4)
            ->withConfirmedPlayerIds([self::CREATOR_ID])
            ->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new UpdateMatchCommand(
            matchId: $match->id()->value(),
            requesterId: self::CREATOR_ID,
            courtName: 'New Court',
        ));

        $this->assertFalse($this->matchRepository->confirmationsWereDeletedFor($match->id()->value()));
    }

    public function test_explicit_null_clears_nullable_fields(): void
    {
        $match = MatchMother::create()
            ->withFullDoublesLineup(self::CREATOR_ID, self::P2, self::P3, self::P4)
            ->withSetsDetail(SetsDetail::fromArray([['a' => 6, 'b' => 3], ['a' => 6, 'b' => 2]]))
            ->withConfirmedPlayerIds([self::CREATOR_ID, self::P2])
            ->build();
        $match->updateCourtName(CourtName::fromString('Court A'));
        $match->updateMatchDate(new DateTimeImmutable('2026-06-01 10:00:00'));
        $match->updateNotes(Notes::fromString('Bring balls'));
        $match->pullDomainEvents();
        $this->matchRepository->save($match);

        $this->makeHandler()(new UpdateMatchCommand(
            matchId: $match->id()->value(),
            requesterId: self::CREATOR_ID,
            courtName: null,
            matchDate: null,
            notes: null,
            setsDetail: null,
            fields: ['courtName', 'matchDate', 'notes', 'setsDetail'],
        ));

        $updated = $this->matchRepository->findById($match->id());
        $this->assertNull($updated->courtName());
        $this->assertNull($updated->matchDate());
        $this->assertNull($updated->notes());
        $this->assertNull($updated->setsDetail());
        $this->assertEmpty($updated->confirmedPlayerIds());
        $this->assertTrue($this->matchRepository->confirmationsWereDeletedFor($match->id()->value()));
    }

    public function test_absent_nullable_fields_are_left_unchanged(): void
    {
        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->build();
        $match->updateCourtName(CourtName::fromString('Court A'));
        $match->updateMatchDate(new DateTimeImmutable('2026-06-01 10:00:00'));
        $match->updateNotes(Notes::fromString('Bring balls'));
        $this->matchRepository->save($match);

        $this->makeHandler()(new UpdateMatchCommand(
            matchId: $match->id()->value(),
            requesterId: self::CREATOR_ID,
            fields: [],
        ));

        $updated = $this->matchRepository->findById($match->id());
        $this->assertSame('Court A', $updated->courtName()?->value());
        $this->assertSame('2026-06-01 10:00:00', $updated->matchDate()?->format('Y-m-d H:i:s'));
        $this->assertSame('Bring balls', $updated->notes()?->value());
    }

    public function test_non_creator_cannot_update(): void
    {
        $this->expectException(UnauthorizedMatchOperationException::class);

        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new UpdateMatchCommand(
            matchId: $match->id()->value(),
            requesterId: '99999999-9999-9999-9999-999999999999',
            notes: 'hack',
        ));
    }

    public function test_cannot_update_cancelled_match(): void
    {
        $this->expectException(MatchAlreadyCancelledException::class);

        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->withStatus('cancelled')->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new UpdateMatchCommand(
            matchId: $match->id()->value(),
            requesterId: self::CREATOR_ID,
            notes: 'late edit',
        ));
    }

    private function makeHandler(): UpdateMatchCommandHandler
    {
        return new UpdateMatchCommandHandler(
            matchRepository: $this->matchRepository,
            eventDispatcher: new SpyEventDispatcher,
        );
    }
}
