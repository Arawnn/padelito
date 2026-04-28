<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Events\UpdatePlayerStats;

use App\Features\Matches\Domain\Events\MatchValidated;
use App\Features\Player\Application\Events\UpdatePlayerStats\UpdatePlayerStatsWhenMatchValidated;
use App\Features\Player\Domain\Events\PlayerMatchResultApplied;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Services\EloCalculationService;
use App\Features\Player\Domain\ValueObjects\Id;
use Tests\Shared\Mother\Fake\InMemoryEloHistoryRepository;
use Tests\Shared\Mother\Fake\InMemoryPlayerRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\PlayerMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UpdatePlayerStatsWhenMatchValidatedTest extends TestCase
{
    private const MATCH_ID = '10000000-0000-0000-0000-000000000001';

    private const P1 = '00000000-0000-0000-0000-000000000001';

    private const P2 = '00000000-0000-0000-0000-000000000002';

    private const P3 = '00000000-0000-0000-0000-000000000003';

    private const P4 = '00000000-0000-0000-0000-000000000004';

    private InMemoryPlayerRepository $playerRepository;

    private InMemoryEloHistoryRepository $eloHistoryRepository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playerRepository = new InMemoryPlayerRepository;
        $this->eloHistoryRepository = new InMemoryEloHistoryRepository;
        $this->eventDispatcher = new SpyEventDispatcher;

        foreach ([self::P1, self::P2, self::P3, self::P4] as $index => $id) {
            $this->playerRepository->save(
                PlayerMother::create()->withId($id)->withUsername('player_'.$index)->build()
            );
        }
    }

    public function test_it_subscribes_to_match_validated(): void
    {
        $this->assertSame([MatchValidated::class], UpdatePlayerStatsWhenMatchValidated::subscribedTo());
    }

    public function test_friendly_match_updates_win_loss_stats_without_changing_elo_or_history(): void
    {
        $eloBefore = $this->player(self::P1)->stats()->eloRating()->value();

        $this->makeHandler()($this->matchValidated(ranked: false));

        $winner = $this->player(self::P1);
        $loser = $this->player(self::P3);

        $this->assertSame(1, $winner->stats()->totalWins()->value());
        $this->assertSame(0, $winner->stats()->totalLosses()->value());
        $this->assertSame(0, $loser->stats()->totalWins()->value());
        $this->assertSame(1, $loser->stats()->totalLosses()->value());
        $this->assertSame($eloBefore, $winner->stats()->eloRating()->value());
        $this->assertSame(0, $this->eloHistoryRepository->countForMatch(self::MATCH_ID));
        $this->assertTrue($this->eventDispatcher->dispatched(PlayerMatchResultApplied::class));
    }

    public function test_ranked_match_updates_elo_and_records_history(): void
    {
        $winnerEloBefore = $this->player(self::P1)->stats()->eloRating()->value();
        $loserEloBefore = $this->player(self::P3)->stats()->eloRating()->value();

        $this->makeHandler()($this->matchValidated(ranked: true));

        $winner = $this->player(self::P1);
        $loser = $this->player(self::P3);

        $this->assertGreaterThan($winnerEloBefore, $winner->stats()->eloRating()->value());
        $this->assertLessThan($loserEloBefore, $loser->stats()->eloRating()->value());
        $this->assertSame(4, $this->eloHistoryRepository->countForMatch(self::MATCH_ID));

        $records = $this->eloHistoryRepository->all();
        $this->assertSame('A', $records[0]['team']);
        $this->assertTrue($records[0]['won']);
        $this->assertSame('B', $records[2]['team']);
        $this->assertFalse($records[2]['won']);
    }

    public function test_elo_history_is_idempotent_by_player_and_match(): void
    {
        $event = $this->matchValidated(ranked: true);

        $this->makeHandler()($event);
        $this->makeHandler()($event);

        $this->assertSame(4, $this->eloHistoryRepository->countForMatch(self::MATCH_ID));
    }

    public function test_missing_player_fails_fast(): void
    {
        $this->expectException(PlayerProfileNotFoundException::class);

        $this->makeHandler()(new MatchValidated(
            matchId: self::MATCH_ID,
            teamAPlayerIds: [self::P1, self::P2],
            teamBPlayerIds: [self::P3, '99999999-9999-9999-9999-999999999999'],
            teamAScore: 2,
            teamBScore: 0,
            ranked: true,
        ));
    }

    private function makeHandler(): UpdatePlayerStatsWhenMatchValidated
    {
        return new UpdatePlayerStatsWhenMatchValidated(
            playerRepository: $this->playerRepository,
            eloCalculationService: new EloCalculationService,
            eloHistoryRepository: $this->eloHistoryRepository,
            eventDispatcher: $this->eventDispatcher,
        );
    }

    private function player(string $id): \App\Features\Player\Domain\Entities\Player
    {
        return $this->playerRepository->findById(Id::fromString($id));
    }

    private function matchValidated(bool $ranked): MatchValidated
    {
        return new MatchValidated(
            matchId: self::MATCH_ID,
            teamAPlayerIds: [self::P1, self::P2],
            teamBPlayerIds: [self::P3, self::P4],
            teamAScore: 2,
            teamBScore: 0,
            ranked: $ranked,
        );
    }
}
