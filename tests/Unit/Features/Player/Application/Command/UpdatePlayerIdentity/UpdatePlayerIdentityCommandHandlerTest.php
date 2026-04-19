<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Command\UpdatePlayerIdentity;

use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommand;
use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommandHandler;
use App\Features\Player\Domain\Events\PlayerIdentityUpdated;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\ValueObjects\AvatarUrl;
use App\Features\Player\Domain\ValueObjects\DisplayName;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
use App\Shared\Application\Optional;
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
final class UpdatePlayerIdentityCommandHandlerTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPlayerRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_updates_display_name(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::of('New Name'),
            bio: Optional::absent(),
        ));

        $this->assertEquals('New Name', $result->identity()->displayName()->value());
    }

    public function test_it_keeps_existing_display_name_when_absent(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::absent(),
            bio: Optional::absent(),
        ));

        $this->assertEquals('Jean Dupont', $result->identity()->displayName()->value());
    }

    public function test_it_clears_display_name_when_explicitly_null(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::of(null),
            bio: Optional::absent(),
        ));

        $this->assertNull($result->identity()->displayName());
    }

    public function test_it_preserves_existing_avatar_url(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $player->updateIdentity(PlayerIdentity::of(
            displayName: DisplayName::fromString('Jean Dupont'),
            bio: null,
            avatar: AvatarUrl::fromString('http://localhost/storage/avatars/existing.jpg'),
        ));
        $player->pullDomainEvents();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::of('Updated Name'),
            bio: Optional::absent(),
        ));

        $this->assertEquals(
            'http://localhost/storage/avatars/existing.jpg',
            $result->identity()->avatarUrl()->value(),
        );
    }

    public function test_it_dispatches_player_identity_updated_event(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::of('New Name'),
            bio: Optional::absent(),
        ));

        $this->assertTrue($this->eventDispatcher->dispatched(PlayerIdentityUpdated::class));
    }

    public function test_it_fails_when_player_not_found(): void
    {
        $this->expectException(PlayerProfileNotFoundException::class);

        $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000099',
            displayName: Optional::absent(),
            bio: Optional::absent(),
        ));
    }

    private function makeHandler(): UpdatePlayerIdentityCommandHandler
    {
        return new UpdatePlayerIdentityCommandHandler(
            playerRepository: $this->repository,
            eventDispatcher: $this->eventDispatcher,
            transactionManager: new ImmediateTransactionManager,
        );
    }
}
