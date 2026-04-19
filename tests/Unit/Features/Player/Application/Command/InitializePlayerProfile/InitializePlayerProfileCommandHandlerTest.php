<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Command\InitializePlayerProfile;

use App\Features\Player\Application\Commands\InitializePlayerProfile\InitializePlayerProfileCommand;
use App\Features\Player\Application\Commands\InitializePlayerProfile\InitializePlayerProfileCommandHandler;
use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\Events\PlayerProfileCreated;
use App\Features\Player\Domain\Exceptions\PlayerProfileAlreadyExistException;
use App\Features\Player\Domain\Services\UsernameGeneratorService;
use App\Features\Player\Domain\ValueObjects\Id;
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
final class InitializePlayerProfileCommandHandlerTest extends TestCase
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

    public function test_it_creates_player_with_beginner_level(): void
    {
        $this->makeHandler()(new InitializePlayerProfileCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'John Doe',
        ));

        $player = $this->repository->findById(Id::fromString('00000000-0000-0000-0000-000000000001'));

        $this->assertNotNull($player);
        $this->assertEquals(PlayerLevelEnum::BEGINNER->value, $player->level()->value()->value);
    }

    public function test_it_slugifies_display_name_into_username(): void
    {
        $this->makeHandler()(new InitializePlayerProfileCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'Jean Dupont',
        ));

        $player = $this->repository->findById(Id::fromString('00000000-0000-0000-0000-000000000001'));

        $this->assertEquals('jean_dupont', $player->username()->value());
    }

    public function test_it_preserves_display_name_in_identity(): void
    {
        $this->makeHandler()(new InitializePlayerProfileCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'John Doe',
        ));

        $player = $this->repository->findById(Id::fromString('00000000-0000-0000-0000-000000000001'));

        $this->assertEquals('John Doe', $player->identity()?->displayName()?->value());
    }

    public function test_it_dispatches_player_profile_created_event(): void
    {
        $this->makeHandler()(new InitializePlayerProfileCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'John Doe',
        ));

        $this->assertTrue($this->eventDispatcher->dispatched(PlayerProfileCreated::class));
    }

    public function test_it_throws_if_profile_already_exists_for_user_id(): void
    {
        $this->expectException(PlayerProfileAlreadyExistException::class);

        $this->repository->save(
            PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build()
        );

        $this->makeHandler()(new InitializePlayerProfileCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'John Doe',
        ));
    }

    public function test_it_appends_suffix_on_username_collision(): void
    {
        $this->repository->save(
            PlayerMother::create()->withId('00000000-0000-0000-0000-000000000099')->withUsername('jean_dupont')->build()
        );

        $this->makeHandler()(new InitializePlayerProfileCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'Jean Dupont',
        ));

        $player = $this->repository->findById(Id::fromString('00000000-0000-0000-0000-000000000001'));

        $this->assertStringStartsWith('jean_dupont_', $player->username()->value());
        $this->assertNotEquals('jean_dupont', $player->username()->value());
    }

    private function makeHandler(): InitializePlayerProfileCommandHandler
    {
        return new InitializePlayerProfileCommandHandler(
            playerRepository: $this->repository,
            usernameGenerator: new UsernameGeneratorService($this->repository),
            transactionManager: $this->tx,
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
