<?php

declare(strict_types=1);

namespace Tests\Integration\Features\Matches\Application;

use App\Features\Auth\Infrastructure\Persistence\Eloquent\Models\User;
use App\Features\Matches\Application\Commands\ConfirmMatch\ConfirmMatchCommand;
use App\Features\Matches\Domain\Events\MatchValidated;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\Player as PlayerModel;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Domain\Events\DomainEventSubscriberInterface;
use Illuminate\Support\Facades\DB;
use Tests\IntegrationTestCase;
use Tests\Shared\Mother\MatchMother;

/**
 * @internal
 *
 * @coversNothing
 */
final class ConfirmMatchTransactionTest extends IntegrationTestCase
{
    private const P1 = '00000000-0000-0000-0000-000000000001';

    private const P2 = '00000000-0000-0000-0000-000000000002';

    private const P3 = '00000000-0000-0000-0000-000000000003';

    private const P4 = '00000000-0000-0000-0000-000000000004';

    public function test_match_validation_rolls_back_when_synchronous_match_validated_listener_fails(): void
    {
        $this->createProfiles();

        $match = MatchMother::create()
            ->withFullDoublesLineup(self::P1, self::P2, self::P3, self::P4)
            ->withSetsDetail(SetsDetail::fromArray([['a' => 6, 'b' => 3], ['a' => 6, 'b' => 2]]))
            ->asRanked()
            ->build();

        app(MatchRepositoryInterface::class)->save($match);

        $this->app->tag([ThrowingMatchValidatedSubscriber::class], 'domain_event_subscribers');

        $bus = app(CommandBusInterface::class);
        foreach ([self::P1, self::P2, self::P3] as $playerId) {
            $bus->dispatch(new ConfirmMatchCommand($match->id()->value(), $playerId));
        }

        try {
            $bus->dispatch(new ConfirmMatchCommand($match->id()->value(), self::P4));
            $this->fail('Expected synchronous listener failure.');
        } catch (SyntheticMatchValidatedListenerException $exception) {
            $this->assertSame('Synthetic MatchValidated listener failure.', $exception->getMessage());
        }

        $this->assertDatabaseHas('matches', [
            'id' => $match->id()->value(),
            'status' => 'pending',
        ]);
        $this->assertSame(0, DB::table('elo_history')->where('match_id', $match->id()->value())->count());
        $this->assertDatabaseHas('profiles', ['id' => self::P1, 'total_wins' => 0, 'elo_rating' => 1500]);
        $this->assertDatabaseMissing('match_confirmations', [
            'match_id' => $match->id()->value(),
            'player_id' => self::P4,
        ]);
    }

    private function createProfiles(): void
    {
        foreach ([self::P1, self::P2, self::P3, self::P4] as $index => $id) {
            User::factory()->create(['id' => $id]);

            PlayerModel::create([
                'id' => $id,
                'username' => 'tx_player_'.$index,
                'level' => 'beginner',
                'elo_rating' => 1500,
                'total_wins' => 0,
                'total_losses' => 0,
                'current_streak' => 0,
                'best_streak' => 0,
                'padel_coins' => 0,
                'is_public' => false,
            ]);
        }
    }
}

final readonly class ThrowingMatchValidatedSubscriber implements DomainEventSubscriberInterface
{
    public static function subscribedTo(): array
    {
        return [MatchValidated::class];
    }

    public function __invoke(MatchValidated $event): void
    {
        throw new SyntheticMatchValidatedListenerException('Synthetic MatchValidated listener failure.');
    }
}

final class SyntheticMatchValidatedListenerException extends \RuntimeException {}
