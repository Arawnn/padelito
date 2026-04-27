<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Command\ChangeProfileVisibility;

use App\Features\Player\Application\Commands\ChangeProfileVisibility\ChangeProfileVisibilityCommand;
use App\Features\Player\Application\Commands\ChangeProfileVisibility\ChangeProfileVisibilityCommandHandler;
use App\Features\Player\Domain\Events\PlayerVisibilityChanged;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use Tests\Shared\Mother\Fake\InMemoryPlayerRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\PlayerMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ChangeProfileVisibilityCommandHandlerTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPlayerRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_makes_a_profile_public(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new ChangeProfileVisibilityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            isPublic: true,
        ));

        $this->assertTrue($result->visibility()->isPublic());
    }

    public function test_it_makes_a_profile_private(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->asPublic()->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new ChangeProfileVisibilityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            isPublic: false,
        ));

        $this->assertTrue($result->visibility()->isPrivate());
    }

    public function test_it_dispatches_player_visibility_changed_event(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $this->makeHandler()(new ChangeProfileVisibilityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            isPublic: true,
        ));

        $this->assertTrue($this->eventDispatcher->dispatched(PlayerVisibilityChanged::class));
    }

    public function test_it_fails_when_player_not_found(): void
    {
        $this->expectException(PlayerProfileNotFoundException::class);

        $this->makeHandler()(new ChangeProfileVisibilityCommand(
            userId: '00000000-0000-0000-0000-000000000099',
            isPublic: true,
        ));
    }

    private function makeHandler(): ChangeProfileVisibilityCommandHandler
    {
        return new ChangeProfileVisibilityCommandHandler(
            playerRepository: $this->repository,
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
