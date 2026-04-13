<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Command\UpdatePlayerIdentity;

use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommand;
use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommandHandler;
use App\Features\Player\Application\Dto\AvatarInput;
use App\Features\Player\Domain\Events\PlayerIdentityUpdated;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Shared\Application\Optional;
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
            avatar: null,
        ));

        $this->assertTrue($result->isOk());
        $this->assertEquals('New Name', $result->value()->identity()->displayName()->value());
    }

    public function test_it_keeps_existing_display_name_when_absent(): void
    {
        // PlayerMother builds with displayName = 'Jean Dupont'
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::absent(),
            bio: Optional::absent(),
            avatar: null,
        ));

        $this->assertTrue($result->isOk());
        $this->assertEquals('Jean Dupont', $result->value()->identity()->displayName()->value());
    }

    public function test_it_clears_display_name_when_explicitly_null(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::of(null),
            bio: Optional::absent(),
            avatar: null,
        ));

        $this->assertTrue($result->isOk());
        $this->assertNull($result->value()->identity()->displayName());
    }

    public function test_it_provisions_avatar_when_provided(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $provisioner = FakeAvatarProvisioner::thatSucceeds('http://localhost/storage/avatars/new.jpg');

        $result = $this->makeHandler($provisioner)(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::absent(),
            bio: Optional::absent(),
            avatar: new AvatarInput(
                uploadedFilePath: null,
                uploadedFileExtension: null,
                remoteUrl: 'https://example.com/avatar.jpg',
            ),
        ));

        $this->assertTrue($result->isOk());
        $this->assertEquals(
            'http://localhost/storage/avatars/new.jpg',
            $result->value()->identity()->avatarUrl()->value(),
        );
    }

    public function test_it_deletes_old_avatar_when_replacing(): void
    {
        // Build a player with an existing avatar URL
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        // Manually set an avatar by updating identity directly
        $player->updateIdentity(\App\Features\Player\Domain\ValueObjects\PlayerIdentity::of(
            displayName: \App\Features\Player\Domain\ValueObjects\DisplayName::fromString('Jean Dupont'),
            bio: null,
            avatar: \App\Features\Player\Domain\ValueObjects\AvatarUrl::fromString('http://localhost/storage/avatars/old.jpg'),
        ));
        $player->pullDomainEvents(); // clear the event recorded by updateIdentity
        $this->repository->save($player);

        $provisioner = FakeAvatarProvisioner::thatSucceeds('http://localhost/storage/avatars/new.jpg');

        $this->makeHandler($provisioner)(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::absent(),
            bio: Optional::absent(),
            avatar: new AvatarInput(
                uploadedFilePath: null,
                uploadedFileExtension: null,
                remoteUrl: 'https://example.com/new-avatar.jpg',
            ),
        ));

        $this->assertEquals('http://localhost/storage/avatars/old.jpg', $provisioner->lastDeletedUrl);
    }

    public function test_it_fails_when_provisioner_fails(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler(FakeAvatarProvisioner::thatFails())(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::absent(),
            bio: Optional::absent(),
            avatar: new AvatarInput(
                uploadedFilePath: null,
                uploadedFileExtension: null,
                remoteUrl: 'https://example.com/avatar.jpg',
            ),
        ));

        $this->assertTrue($result->isFail());
    }

    public function test_it_dispatches_player_identity_updated_event(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: Optional::of('New Name'),
            bio: Optional::absent(),
            avatar: null,
        ));

        $this->assertTrue($this->eventDispatcher->dispatched(PlayerIdentityUpdated::class));
    }

    public function test_it_fails_when_player_not_found(): void
    {
        $result = $this->makeHandler()(new UpdatePlayerIdentityCommand(
            userId: '00000000-0000-0000-0000-000000000099',
            displayName: Optional::absent(),
            bio: Optional::absent(),
            avatar: null,
        ));

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(PlayerProfileNotFoundException::class, $result->error());
    }

    private function makeHandler(?FakeAvatarProvisioner $provisioner = null): UpdatePlayerIdentityCommandHandler
    {
        return new UpdatePlayerIdentityCommandHandler(
            playerRepository: $this->repository,
            avatarProvisioner: $provisioner ?? FakeAvatarProvisioner::thatSucceeds(),
            eventDispatcher: $this->eventDispatcher,
            transactionManager: new ImmediateTransactionManager,
        );
    }
}
