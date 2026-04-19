<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Infrastructure\Listeners;

use App\Features\Auth\Domain\Events\UserCreated;
use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommand;
use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\Services\UsernameGeneratorService;
use App\Features\Player\Infrastructure\Listeners\CreateDefaultPlayerProfileOnUserCreated;
use Tests\Shared\Mother\Fake\InMemoryPlayerRepository;
use Tests\Shared\Mother\Fake\SpyCommandBus;
use Tests\Shared\Mother\PlayerMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CreateDefaultPlayerProfileOnUserCreatedTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    private SpyCommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPlayerRepository;
        $this->commandBus = new SpyCommandBus;
    }

    public function test_it_dispatches_create_player_profile_command(): void
    {
        $this->makeListener()(new UserCreated(
            userId: '00000000-0000-0000-0000-000000000001',
            name: 'Jean Dupont',
            email: 'jean@example.com',
        ));

        $this->assertTrue($this->commandBus->wasDispatched(CreatePlayerProfileCommand::class));
    }

    public function test_it_slugifies_name_into_username(): void
    {
        $this->makeListener()(new UserCreated(
            userId: '00000000-0000-0000-0000-000000000001',
            name: 'Jean Dupont',
            email: 'jean@example.com',
        ));

        /** @var CreatePlayerProfileCommand $command */
        $command = $this->commandBus->dispatchedOfType(CreatePlayerProfileCommand::class);
        $this->assertEquals('jean_dupont', $command->username);
    }

    public function test_it_uses_beginner_as_default_level(): void
    {
        $this->makeListener()(new UserCreated(
            userId: '00000000-0000-0000-0000-000000000001',
            name: 'Jean Dupont',
            email: 'jean@example.com',
        ));

        /** @var CreatePlayerProfileCommand $command */
        $command = $this->commandBus->dispatchedOfType(CreatePlayerProfileCommand::class);
        $this->assertEquals(PlayerLevelEnum::BEGINNER->value, $command->level);
    }

    public function test_it_sets_user_name_as_display_name(): void
    {
        $this->makeListener()(new UserCreated(
            userId: '00000000-0000-0000-0000-000000000001',
            name: 'Jean Dupont',
            email: 'jean@example.com',
        ));

        /** @var CreatePlayerProfileCommand $command */
        $command = $this->commandBus->dispatchedOfType(CreatePlayerProfileCommand::class);
        $this->assertEquals('Jean Dupont', $command->displayName);
    }

    public function test_it_appends_suffix_on_username_collision(): void
    {
        $this->repository->save(
            PlayerMother::create()->withId('00000000-0000-0000-0000-000000000099')->withUsername('jean_dupont')->build()
        );

        $this->makeListener()(new UserCreated(
            userId: '00000000-0000-0000-0000-000000000001',
            name: 'Jean Dupont',
            email: 'jean@example.com',
        ));

        /** @var CreatePlayerProfileCommand $command */
        $command = $this->commandBus->dispatchedOfType(CreatePlayerProfileCommand::class);
        $this->assertStringStartsWith('jean_dupont_', $command->username);
        $this->assertNotEquals('jean_dupont', $command->username);
    }

    private function makeListener(): CreateDefaultPlayerProfileOnUserCreated
    {
        return new CreateDefaultPlayerProfileOnUserCreated(
            commandBus: $this->commandBus,
            usernameGenerator: new UsernameGeneratorService($this->repository),
        );
    }
}
