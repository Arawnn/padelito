<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Command\UploadPlayerAvatar;

use App\Features\Player\Application\Commands\UploadPlayerAvatar\UploadPlayerAvatarCommand;
use App\Features\Player\Application\Commands\UploadPlayerAvatar\UploadPlayerAvatarCommandHandler;
use App\Features\Player\Application\Dto\AvatarInput;
use App\Features\Player\Domain\Events\PlayerIdentityUpdated;
use App\Features\Player\Domain\Exceptions\InvalidAvatarUrlException;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\ValueObjects\AvatarUrl;
use App\Features\Player\Domain\ValueObjects\Bio;
use App\Features\Player\Domain\ValueObjects\DisplayName;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
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
final class UploadPlayerAvatarCommandHandlerTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPlayerRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_provisions_avatar_and_sets_it_on_player(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $provisioner = FakeAvatarProvisioner::thatSucceeds('http://localhost/storage/avatars/new.jpg');

        $result = $this->makeHandler($provisioner)(new UploadPlayerAvatarCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'Jean Dupont',
            avatar: new AvatarInput(null, null, 'https://example.com/avatar.jpg'),
        ));

        $this->assertEquals(
            'http://localhost/storage/avatars/new.jpg',
            $result->identity()->avatarUrl()->value(),
        );
    }

    public function test_it_preserves_existing_display_name_and_bio(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $player->updateIdentity(PlayerIdentity::of(
            displayName: DisplayName::fromString('Jean Dupont'),
            bio: Bio::fromString('Ma bio'),
            avatar: null,
        ));
        $player->pullDomainEvents();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UploadPlayerAvatarCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'Jean Dupont',
            avatar: null,
        ));

        $this->assertEquals('Jean Dupont', $result->identity()->displayName()->value());
        $this->assertEquals('Ma bio', $result->identity()->bio()->value());
    }

    public function test_it_deletes_old_avatar_when_one_existed(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $player->updateIdentity(PlayerIdentity::of(
            displayName: DisplayName::fromString('Jean Dupont'),
            bio: null,
            avatar: AvatarUrl::fromString('http://localhost/storage/avatars/old.jpg'),
        ));
        $player->pullDomainEvents();
        $this->repository->save($player);

        $provisioner = FakeAvatarProvisioner::thatSucceeds('http://localhost/storage/avatars/new.jpg');

        $this->makeHandler($provisioner)(new UploadPlayerAvatarCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'Jean Dupont',
            avatar: null,
        ));

        $this->assertEquals('http://localhost/storage/avatars/old.jpg', $provisioner->lastDeletedUrl);
    }

    public function test_it_does_not_delete_old_avatar_when_none_existed(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $provisioner = FakeAvatarProvisioner::thatSucceeds();

        $this->makeHandler($provisioner)(new UploadPlayerAvatarCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'Jean Dupont',
            avatar: null,
        ));

        $this->assertNull($provisioner->lastDeletedUrl);
    }

    public function test_it_dispatches_player_identity_updated_event(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $this->makeHandler()(new UploadPlayerAvatarCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'Jean Dupont',
            avatar: null,
        ));

        $this->assertTrue($this->eventDispatcher->dispatched(PlayerIdentityUpdated::class));
    }

    public function test_it_throws_when_player_not_found(): void
    {
        $this->expectException(PlayerProfileNotFoundException::class);

        $this->makeHandler()(new UploadPlayerAvatarCommand(
            userId: '00000000-0000-0000-0000-000000000099',
            displayName: 'Jean Dupont',
            avatar: null,
        ));
    }

    public function test_it_propagates_provisioner_failure(): void
    {
        $this->expectException(InvalidAvatarUrlException::class);

        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $this->makeHandler(FakeAvatarProvisioner::thatFails())(new UploadPlayerAvatarCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            displayName: 'Jean Dupont',
            avatar: new AvatarInput(null, null, 'https://example.com/avatar.jpg'),
        ));
    }

    private function makeHandler(?FakeAvatarProvisioner $provisioner = null): UploadPlayerAvatarCommandHandler
    {
        return new UploadPlayerAvatarCommandHandler(
            playerRepository: $this->repository,
            avatarProvisioner: $provisioner ?? FakeAvatarProvisioner::thatSucceeds(),
            eventDispatcher: $this->eventDispatcher,
            transactionManager: new ImmediateTransactionManager,
        );
    }
}
