<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Command\CreatePlayerProfile;

use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommand;
use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommandHandler;
use App\Features\Player\Domain\Events\PlayerProfileCreated;
use App\Features\Player\Domain\Exceptions\PlayerProfileAlreadyExistException;
use Tests\Shared\Mother\Fake\FakeAvatarProvisioner;
use Tests\Shared\Mother\Fake\ImmediateTransactionManager;
use Tests\Shared\Mother\Fake\InMemoryPlayerRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\PlayerMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CreatePlayerProfileCommandHandlerTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    private ImmediateTransactionManager $tx;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPlayerRepository;
        $this->tx = new ImmediateTransactionManager;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_creates_a_player_profile(): void
    {
        $handler = $this->makeHandler();
        $command = $this->validCommand();

        $result = $handler($command);

        $this->assertTrue($result->isOk());

        $player = $this->repository->findById($result->value()->id());
        $this->assertNotNull($player);
        $this->assertEquals('jean_dupont', $player->username()->value());
        $this->assertEquals('beginner', $player->level()->value()->value);
        $this->assertEquals(1500, $player->stats()->eloRating()->value());
    }

    public function test_it_dispatches_player_profile_created_event(): void
    {
        $handler = $this->makeHandler();

        $handler($this->validCommand());

        $this->assertTrue($this->eventDispatcher->dispatched(PlayerProfileCreated::class));
    }

    public function test_it_fails_when_profile_already_exists_for_user(): void
    {
        $existing = PlayerMother::create()
            ->withId('00000000-0000-0000-0000-000000000001')
            ->withUsername('existing_player')
            ->build();

        $this->repository->save($existing);

        $result = $this->makeHandler()($this->validCommand(userId: '00000000-0000-0000-0000-000000000001'));

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(PlayerProfileAlreadyExistException::class, $result->error());
    }

    public function test_it_fails_when_username_is_already_taken(): void
    {
        $existing = PlayerMother::create()
            ->withId('00000000-0000-0000-0000-000000000099')
            ->withUsername('jean_dupont')
            ->build();

        $this->repository->save($existing);

        $result = $this->makeHandler()($this->validCommand());

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(PlayerProfileAlreadyExistException::class, $result->error());
    }

    public function test_it_initializes_elo_at_1500(): void
    {
        $result = $this->makeHandler()($this->validCommand());

        $this->assertEquals(1500, $result->value()->stats()->eloRating()->value());
    }

    public function test_it_initializes_padel_coins_at_zero(): void
    {
        $result = $this->makeHandler()($this->validCommand());

        $this->assertEquals(0, $result->value()->padelCoins()->value());
    }

    private function validCommand(string $userId = '00000000-0000-0000-0000-000000000001'): CreatePlayerProfileCommand
    {
        return new CreatePlayerProfileCommand(
            userId: $userId,
            username: 'jean_dupont',
            level: 'beginner',
            displayName: 'Jean Dupont',
            avatar: null,
            bio: null,
            location: null,
            dominantHand: 'right',
            preferredPosition: 'back',
        );
    }

    private function makeHandler(): CreatePlayerProfileCommandHandler
    {
        return new CreatePlayerProfileCommandHandler(
            playerRepository: $this->repository,
            transactionManager: $this->tx,
            eventDispatcher: $this->eventDispatcher,
            avatarProvisioner: FakeAvatarProvisioner::thatSucceeds(),
        );
    }
}
