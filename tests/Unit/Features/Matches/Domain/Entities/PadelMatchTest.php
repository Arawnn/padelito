<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Domain\Entities;

use App\Features\Matches\Domain\Enums\MatchFormatEnum;
use App\Features\Matches\Domain\Enums\MatchTypeEnum;
use App\Features\Matches\Domain\Events\MatchConfirmationsReset;
use App\Features\Matches\Domain\Events\MatchValidated;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyCancelledException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyValidatedException;
use App\Features\Matches\Domain\Exceptions\MatchNotReadyForConfirmationException;
use App\Features\Matches\Domain\Exceptions\PlayerAlreadyConfirmedException;
use App\Features\Matches\Domain\Exceptions\PlayerNotParticipantException;
use App\Features\Matches\Domain\ValueObjects\CourtName;
use App\Features\Matches\Domain\ValueObjects\MatchFormat;
use App\Features\Matches\Domain\ValueObjects\MatchType;
use App\Features\Matches\Domain\ValueObjects\Notes;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use App\Features\Matches\Domain\ValueObjects\SetsToWin;
use App\Features\Matches\Domain\ValueObjects\Team;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Shared\Mother\MatchMother;

/**
 * @internal
 *
 * @coversNothing
 */
final class PadelMatchTest extends TestCase
{
    private const P1 = '00000000-0000-0000-0000-000000000001';

    private const P2 = '00000000-0000-0000-0000-000000000002';

    private const P3 = '00000000-0000-0000-0000-000000000003';

    private const P4 = '00000000-0000-0000-0000-000000000004';

    private SetsDetail $twoSetsToZero;

    protected function setUp(): void
    {
        $this->twoSetsToZero = SetsDetail::fromArray([
            ['a' => 6, 'b' => 3],
            ['a' => 6, 'b' => 2],
        ]);
    }

    private function fullDoublesMatchReadyForConfirmation(): \App\Features\Matches\Domain\Entities\PadelMatch
    {
        return MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail($this->twoSetsToZero)
            ->build();
    }

    // ─── isReadyForConfirmation ───────────────────────────────────────────────

    public function test_match_is_not_ready_without_sets_detail(): void
    {
        $match = MatchMother::create()->withFullDoublesLineup()->build();

        $this->assertFalse($match->isReadyForConfirmation());
    }

    public function test_match_is_not_ready_without_full_lineup(): void
    {
        $match = MatchMother::create()
            ->withSetsDetail($this->twoSetsToZero)
            ->build();

        $this->assertFalse($match->isReadyForConfirmation());
    }

    public function test_match_is_ready_with_full_lineup_and_sets(): void
    {
        $this->assertTrue($this->fullDoublesMatchReadyForConfirmation()->isReadyForConfirmation());
    }

    // ─── confirm ─────────────────────────────────────────────────────────────

    public function test_confirm_throws_when_player_not_participant(): void
    {
        $this->expectException(PlayerNotParticipantException::class);

        $match = $this->fullDoublesMatchReadyForConfirmation();
        $match->confirm(PlayerId::fromString('99999999-9999-9999-9999-999999999999'));
    }

    public function test_confirm_throws_when_already_confirmed(): void
    {
        $this->expectException(PlayerAlreadyConfirmedException::class);

        $match = $this->fullDoublesMatchReadyForConfirmation();
        $match->confirm(PlayerId::fromString(self::P1));
        $match->confirm(PlayerId::fromString(self::P1));
    }

    public function test_confirm_throws_when_not_ready(): void
    {
        $this->expectException(MatchNotReadyForConfirmationException::class);

        $match = MatchMother::create()->withCreator(self::P1)->build();
        $match->confirm(PlayerId::fromString(self::P1));
    }

    public function test_confirm_throws_when_already_validated(): void
    {
        $this->expectException(MatchAlreadyValidatedException::class);

        $match = MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail($this->twoSetsToZero)
            ->withStatus('validated')
            ->build();

        $match->confirm(PlayerId::fromString(self::P1));
    }

    public function test_confirm_throws_when_cancelled(): void
    {
        $this->expectException(MatchAlreadyCancelledException::class);

        $match = MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail($this->twoSetsToZero)
            ->withStatus('cancelled')
            ->build();

        $match->confirm(PlayerId::fromString(self::P1));
    }

    public function test_validated_match_rejects_all_mutations(): void
    {
        foreach ($this->matchMutations() as $mutation) {
            $match = MatchMother::create()
                ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
                ->withSetsDetail($this->twoSetsToZero)
                ->withStatus('validated')
                ->build();

            $this->assertMutationThrows(MatchAlreadyValidatedException::class, fn () => $mutation($match));
        }
    }

    public function test_cancelled_match_rejects_all_mutations(): void
    {
        foreach ($this->matchMutations() as $mutation) {
            $match = MatchMother::create()
                ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
                ->withSetsDetail($this->twoSetsToZero)
                ->withStatus('cancelled')
                ->build();

            $this->assertMutationThrows(MatchAlreadyCancelledException::class, fn () => $mutation($match));
        }
    }

    public function test_all_confirms_trigger_match_validated_event(): void
    {
        $match = $this->fullDoublesMatchReadyForConfirmation();

        $match->confirm(PlayerId::fromString(self::P1));
        $match->confirm(PlayerId::fromString(self::P2));
        $match->confirm(PlayerId::fromString(self::P3));
        $match->confirm(PlayerId::fromString(self::P4));

        $events = $match->pullDomainEvents();

        $hasValidated = false;
        foreach ($events as $event) {
            if ($event instanceof MatchValidated) {
                $hasValidated = true;
            }
        }

        $this->assertTrue($hasValidated);
        $this->assertTrue($match->status()->isValidated());
    }

    public function test_partial_confirm_does_not_validate(): void
    {
        $match = $this->fullDoublesMatchReadyForConfirmation();

        $match->confirm(PlayerId::fromString(self::P1));
        $match->confirm(PlayerId::fromString(self::P2));

        $this->assertFalse($match->status()->isValidated());
    }

    // ─── resetConfirmations ───────────────────────────────────────────────────

    public function test_updating_sets_detail_resets_confirmations(): void
    {
        $match = MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail($this->twoSetsToZero)
            ->withConfirmedPlayerIds([self::P1, self::P2])
            ->build();

        $newSets = SetsDetail::fromArray([['a' => 3, 'b' => 6], ['a' => 3, 'b' => 6]]);
        $match->updateSetsDetail($newSets);

        $this->assertEmpty($match->confirmedPlayerIds());

        $events = $match->pullDomainEvents();
        $hasReset = false;
        foreach ($events as $event) {
            if ($event instanceof MatchConfirmationsReset) {
                $hasReset = true;
            }
        }

        $this->assertTrue($hasReset);
    }

    public function test_updating_non_substantial_field_does_not_reset_confirmations(): void
    {
        $match = MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail($this->twoSetsToZero)
            ->withConfirmedPlayerIds([self::P1, self::P2])
            ->build();

        $match->updateCourtName(null);

        $this->assertCount(2, $match->confirmedPlayerIds());

        $events = $match->pullDomainEvents();
        foreach ($events as $event) {
            $this->assertNotInstanceOf(MatchConfirmationsReset::class, $event);
        }
    }

    // ─── winningTeam ──────────────────────────────────────────────────────────

    public function test_winning_team_is_a_when_team_a_wins_most_sets(): void
    {
        $match = MatchMother::create()->withSetsDetail($this->twoSetsToZero)->build();

        $winner = $match->winningTeam();

        $this->assertNotNull($winner);
        $this->assertTrue($winner->isA());
    }

    public function test_winning_team_is_b_when_team_b_wins_most_sets(): void
    {
        $sets = SetsDetail::fromArray([['a' => 3, 'b' => 6], ['a' => 2, 'b' => 6]]);
        $match = MatchMother::create()->withSetsDetail($sets)->build();

        $winner = $match->winningTeam();

        $this->assertNotNull($winner);
        $this->assertFalse($winner->isA());
    }

    /**
     * @return list<callable(\App\Features\Matches\Domain\Entities\PadelMatch): void>
     */
    private function matchMutations(): array
    {
        return [
            function ($match): void {
                $match->assignPlayer(PlayerId::fromString('00000000-0000-0000-0000-000000000099'), Team::B());
            },
            function ($match): void {
                $match->removePlayer(PlayerId::fromString(self::P2));
            },
            function ($match): void {
                $match->updateCourtName(CourtName::fromString('Court B'));
            },
            function ($match): void {
                $match->updateMatchDate(new DateTimeImmutable('2026-06-01 10:00:00'));
            },
            function ($match): void {
                $match->updateNotes(Notes::fromString('Late edit'));
            },
            function ($match): void {
                $match->updateSetsDetail(SetsDetail::fromArray([['a' => 6, 'b' => 4], ['a' => 6, 'b' => 4]]));
            },
            function ($match): void {
                $match->updateSetsToWin(SetsToWin::fromInt(3));
            },
            function ($match): void {
                $match->updateFormat(MatchFormat::fromEnum(MatchFormatEnum::DOUBLES));
            },
            function ($match): void {
                $match->updateType(MatchType::fromEnum(MatchTypeEnum::FRIENDLY));
            },
            function ($match): void {
                $match->cancel();
            },
        ];
    }

    /** @param callable(): void $mutation */
    private function assertMutationThrows(string $exceptionClass, callable $mutation): void
    {
        try {
            $mutation();
            self::fail(sprintf('Expected mutation to throw %s.', $exceptionClass));
        } catch (\Throwable $e) {
            self::assertInstanceOf($exceptionClass, $e);
        }
    }
}
